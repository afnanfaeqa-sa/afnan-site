<?php
/*
 * Email + password login for the dashboard (/admin).
 *
 * The dashboard (Sveltia CMS) opens this page in a popup, the way it would open
 * GitHub's own login. When the email and password match, the page hands the
 * dashboard the GitHub access token kept on the server, so team members never
 * need a GitHub account or the token itself.
 *
 * The token and the team's logins live OUTSIDE public_html, in:
 *     /home/<cpanel-user>/afnan-admin-secret.php
 * which looks like this:
 *     <?php return [
 *       'token' => 'github_pat_...',
 *       'users' => [
 *         'name@example.com' => 'a-strong-password',
 *       ],
 *     ];
 */

declare(strict_types=1);

const ALLOWED_ORIGINS = ['https://afnanfaeqasa.sa', 'https://www.afnanfaeqasa.sa'];
const MAX_FAILS = 5;          // per IP
const LOCK_SECONDS = 900;     // 15 minutes after MAX_FAILS

header('Content-Type: text/html; charset=utf-8');
header('X-Frame-Options: DENY');
header('Referrer-Policy: no-referrer');
header('Cache-Control: no-store');

$secretFile = dirname(__DIR__, 2) . '/afnan-admin-secret.php';
$secret = is_file($secretFile) ? require $secretFile : null;
$ready = is_array($secret) && !empty($secret['token']) && !empty($secret['users']) && is_array($secret['users']);

/* ---- simple brute-force guard, one small file per IP in the system temp dir ---- */
$ip = $_SERVER['REMOTE_ADDR'] ?? 'x';
$lockFile = sys_get_temp_dir() . '/afnan-admin-' . hash('sha256', $ip);
$state = is_file($lockFile) ? (json_decode((string) file_get_contents($lockFile), true) ?: []) : [];
$fails = (int) ($state['fails'] ?? 0);
$since = (int) ($state['since'] ?? 0);
if ($fails >= MAX_FAILS && time() - $since > LOCK_SECONDS) { $fails = 0; }
$locked = $fails >= MAX_FAILS;

function checkPassword(string $given, string $stored): bool {
    if (str_starts_with($stored, '$2y$') || str_starts_with($stored, '$argon2')) {
        return password_verify($given, $stored);
    }
    return hash_equals($stored, $given);
}

$error = '';
$token = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $ready && !$locked) {
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $pass  = (string) ($_POST['password'] ?? '');
    $users = array_change_key_case($secret['users'], CASE_LOWER);
    if ($email !== '' && isset($users[$email]) && checkPassword($pass, (string) $users[$email])) {
        @unlink($lockFile);
        $token = (string) $secret['token'];
    } else {
        $fails++;
        @file_put_contents($lockFile, json_encode(['fails' => $fails, 'since' => time()]));
        usleep(800000);
        $locked = $fails >= MAX_FAILS;
        $error = $locked ? 'محاولات كثيرة. حاول مرة ثانية بعد ربع ساعة.' : 'الإيميل أو كلمة المرور غير صحيحة.';
    }
} elseif ($locked) {
    $error = 'محاولات كثيرة. حاول مرة ثانية بعد ربع ساعة.';
}

$e = static fn(string $s): string => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
?><!doctype html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>دخول لوحة التحكم – أفنان الفائقة</title>
<style>
  :root{--navy:#14266e;--green:#138a43;--gold:#c7a04a;--ink:#1d2433;--muted:#5d6678;--line:#e3e7ef}
  *{box-sizing:border-box}
  body{margin:0;min-height:100vh;display:grid;place-items:center;padding:24px 16px;background:#f3f6fb;font:16px/1.6 Tahoma,"Segoe UI",Arial,sans-serif;color:var(--ink)}
  .card{width:100%;max-width:380px;background:#fff;border:1px solid var(--line);border-radius:20px;padding:28px 24px;box-shadow:0 24px 48px -28px rgba(20,38,110,.45)}
  .brand{display:flex;align-items:center;gap:12px;margin-bottom:20px}
  .brand img{width:46px;height:auto}
  .brand b{display:block;color:var(--navy);font-size:18px}
  .brand small{color:var(--muted);font-size:13px}
  label{display:block;font-size:14px;font-weight:600;margin:14px 0 6px;color:var(--navy)}
  input{width:100%;padding:12px 14px;border:1px solid var(--line);border-radius:12px;font:inherit;background:#fbfcfe}
  input:focus{outline:2px solid rgba(19,138,67,.35);border-color:var(--green)}
  input[type=email]{direction:ltr;text-align:left}
  button{width:100%;margin-top:20px;padding:13px;border:0;border-radius:12px;background:var(--green);color:#fff;font:600 16px/1 inherit;font-family:inherit;cursor:pointer}
  button:active{scale:.98}
  .err{margin-top:14px;padding:10px 12px;border-radius:10px;background:#fdecec;color:#a12020;font-size:14px}
  .note{margin-top:14px;color:var(--muted);font-size:13px;text-align:center}
</style>
</head>
<body>
<?php if ($token !== null): ?>
  <div class="card"><p class="note">تم الدخول، جاري فتح لوحة التحكم…</p></div>
  <script>
  (function () {
    var allowed = <?= json_encode(ALLOWED_ORIGINS) ?>;
    var payload = 'authorization:github:success:' + JSON.stringify({ token: <?= json_encode($token) ?>, provider: 'github' });
    function onMessage(e) {
      if (allowed.indexOf(e.origin) === -1) return;
      window.removeEventListener('message', onMessage);
      window.opener.postMessage(payload, e.origin);
      setTimeout(function () { window.close(); }, 400);
    }
    if (!window.opener) { document.querySelector('.note').textContent = 'افتح لوحة التحكم من afnanfaeqasa.sa/admin واضغط تسجيل الدخول.'; return; }
    window.addEventListener('message', onMessage);
    allowed.forEach(function (o) { try { window.opener.postMessage('authorizing:github', o); } catch (_) {} });
  })();
  </script>
<?php else: ?>
  <form class="card" method="post" autocomplete="on">
    <div class="brand"><img src="../img/logo.png" alt=""><div><b>لوحة تحكم أفنان الفائقة</b><small>سجّل دخولك لتعديل محتوى الموقع</small></div></div>
    <?php if (!$ready): ?>
      <div class="err">لوحة الدخول لم تُجهَّز بعد: ملف الإعدادات غير موجود على السيرفر.</div>
    <?php else: ?>
      <label for="email">البريد الإلكتروني</label>
      <input id="email" name="email" type="email" required autocomplete="username" value="<?= $e((string) ($_POST['email'] ?? '')) ?>" <?= $locked ? 'disabled' : '' ?>>
      <label for="password">كلمة المرور</label>
      <input id="password" name="password" type="password" required autocomplete="current-password" <?= $locked ? 'disabled' : '' ?>>
      <button type="submit" <?= $locked ? 'disabled' : '' ?>>دخول</button>
      <?php if ($error !== ''): ?><div class="err"><?= $e($error) ?></div><?php endif; ?>
    <?php endif; ?>
  </form>
<?php endif; ?>
</body>
</html>
