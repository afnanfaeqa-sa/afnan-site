<?php
/*
 * The rep's own dashboard: https://<subdomain>.afnanfaeqasa.sa/dashboard
 * Included from reps/index.php once the rep is known ($rep, $data, $content, $host, $local, $waBase).
 *
 *   /dashboard            stats (visits, WhatsApp / call / quiz, sources, services, recent contacts)
 *   /dashboard/settings   WhatsApp number, ready message, pixels
 *   /dashboard/content    the site's texts and FAQ
 *   /dashboard/setup?t=…  one-time link: the rep sets his email + password
 *   /dashboard/invite     an Afnan admin (same logins as afnanfaeqasa.sa/admin) creates that link
 *   /dashboard/logout
 *
 * Everything is stored outside public_html (see lib.php). Passwords are kept only as hashes.
 */
declare(strict_types=1);
if (!isset($rep)) { http_response_code(404); exit; }

$code = (string) $rep['code'];
$https = (($_SERVER['HTTPS'] ?? '') !== '' && ($_SERVER['HTTPS'] ?? '') !== 'off') || ($_SERVER['SERVER_PORT'] ?? '') === '443';
session_name('afnrep');
session_set_cookie_params(['lifetime' => 0, 'path' => '/dashboard', 'secure' => $https, 'httponly' => true, 'samesite' => 'Strict']);
session_start();
header('Cache-Control: no-store');
header('X-Frame-Options: DENY');
header('Referrer-Policy: no-referrer');
date_default_timezone_set('Asia/Riyadh');

$sub = trim(substr($path, strlen('/dashboard')), '/');
$q = $local ? '?rep=' . rawurlencode($label) : '';
$D = fn(string $p = ''): string => '/dashboard' . ($p !== '' ? '/' . $p : '') . $q;
$amp = $local ? '&' : '?';
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
$csrfOk = fn(): bool => hash_equals((string) $_SESSION['csrf'], (string) ($_POST['csrf'] ?? ''));
$csrf = '<input type="hidden" name="csrf" value="' . e($_SESSION['csrf']) . '">';
$user = rep_read($code, 'user.json');
$authed = !empty($_SESSION['rep']) && $_SESSION['rep'] === $code && !empty($user['email']) && ($_SESSION['since'] ?? 0) >= ($user['set'] ?? 0);

/* ---------- brute-force guard (per IP) ---------- */
$lockFile = rep_data_root() . '/.lock-' . hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? 'x') . $code);
$lock = is_file($lockFile) ? (json_decode((string) file_get_contents($lockFile), true) ?: []) : [];
if (($lock['n'] ?? 0) >= 5 && time() - ($lock['t'] ?? 0) > 900) $lock = [];
$locked = ($lock['n'] ?? 0) >= 5;
$fail = function () use ($lockFile, &$lock, &$locked): void {
    $lock = ['n' => ($lock['n'] ?? 0) + 1, 't' => time()];
    @file_put_contents($lockFile, json_encode($lock));
    $locked = $lock['n'] >= 5;
    usleep(800000);
};

$msg = ''; $err = '';
/* one-time links can also ship with a deploy (tokens.json holds only sha256 hashes); a used link never works again */
$usedFile = rep_data_root() . '/.used-tokens';
$tokOk = function (string $tok, array $stored) use ($usedFile): bool {
    if ($tok === '') return false;
    $h = hash('sha256', $tok);
    if (str_contains((string) @file_get_contents($usedFile), $h)) return false;
    foreach ($stored as $x) { if (!empty($x['h']) && hash_equals((string) $x['h'], $h) && ($x['exp'] ?? 0) > time()) return true; }
    return false;
};
$tokUse = function (string $tok) use ($usedFile): void { @file_put_contents($usedFile, hash('sha256', $tok) . "\n", FILE_APPEND | LOCK_EX); };
$shipped = json_decode((string) @file_get_contents(__DIR__ . '/tokens.json'), true) ?: [];
$posted = ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';
if ($posted && !$csrfOk()) $err = 'انتهت الجلسة، حدّث الصفحة وحاول مرة ثانية.';
$page = 'login';

/* ---------- admin: create the rep's one-time setup link ---------- */
if ($sub === 'invite') {
    $page = 'invite';
    $secretFile = PHP_SAPI === 'cli-server' ? __DIR__ . '/.data/admin-secret.php' : dirname(__DIR__, 2) . '/afnan-admin-secret.php';
    $secret = is_file($secretFile) ? require $secretFile : [];
    $admins = array_change_key_case((array) ($secret['users'] ?? []), CASE_LOWER);
    // marketing admins for the rep sites (set once through /dashboard/admin-setup), on top of the main site's admins
    foreach ((array) (json_decode((string) @file_get_contents(rep_data_root() . '/admins.json'), true) ?: []) as $em => $h) $admins[strtolower((string) $em)] = (string) $h;
    $noAdmins = !$admins;
    $link = '';
    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && !$locked && $csrfOk()) {
        $em = strtolower(trim((string) ($_POST['email'] ?? '')));
        $pw = (string) ($_POST['password'] ?? '');
        $st = (string) ($admins[$em] ?? '');
        $ok = $st !== '' && (str_starts_with($st, '$2y$') || str_starts_with($st, '$argon2') ? password_verify($pw, $st) : hash_equals($st, $pw));
        if ($ok) {
            @unlink($lockFile);
            $tok = rtrim(strtr(base64_encode(random_bytes(24)), '+/', '-_'), '=');
            rep_write($code, 'invite.json', ['h' => hash('sha256', $tok), 'exp' => time() + 72 * 3600, 'by' => $em]);
            rep_append($code, 'changes.jsonl', ['t' => time(), 'by' => $em, 'what' => 'رابط تفعيل جديد']);
            $link = ($local ? 'http://' . $_SERVER['HTTP_HOST'] : 'https://' . $host) . $D('setup') . $amp . 't=' . $tok;
        } else { $fail(); $err = $locked ? 'محاولات كثيرة. حاول بعد ربع ساعة.' : 'بيانات دخول الإدارة غير صحيحة.'; }
    }
}
/* ---------- rep: set email + password from the one-time link ---------- */
/* ---------- first marketing admin: one-time token file placed in afnan-reps-data/ from cPanel ---------- */
elseif ($sub === 'admin-setup') {
    $page = 'adminsetup';
    $tf = rep_data_root() . '/admin-setup.json';
    $ts = is_file($tf) ? (json_decode((string) file_get_contents($tf), true) ?: []) : [];
    $tok = (string) ($_GET['t'] ?? $_POST['t'] ?? '');
    $valid = $tokOk($tok, [$ts, $shipped['admin'] ?? []]);
    if (!$valid) { $err = 'الرابط غير صالح أو انتهت صلاحيته.'; }
    elseif ($posted && $err === '') {
        $em = strtolower(trim((string) ($_POST['email'] ?? '')));
        $p1 = (string) ($_POST['p1'] ?? ''); $p2 = (string) ($_POST['p2'] ?? '');
        if (!filter_var($em, FILTER_VALIDATE_EMAIL)) $err = 'اكتب بريدًا إلكترونيًا صحيحًا.';
        elseif (mb_strlen($p1) < 10) $err = 'كلمة المرور لازم تكون 10 حروف أو أرقام على الأقل.';
        elseif ($p1 !== $p2) $err = 'كلمتا المرور غير متطابقتين.';
        else {
            $af = rep_data_root() . '/admins.json';
            $list = json_decode((string) @file_get_contents($af), true) ?: [];
            $list[$em] = password_hash($p1, PASSWORD_DEFAULT);
            @file_put_contents($af, json_encode($list), LOCK_EX);
            @unlink($tf); $tokUse($tok);
            header('Location: ' . $D('invite')); exit;
        }
    }
}
elseif ($sub === 'setup') {
    $page = 'setup';
    $inv = rep_read($code, 'invite.json');
    $tok = (string) ($_GET['t'] ?? $_POST['t'] ?? '');
    $valid = $tokOk($tok, [$inv, $shipped['invites'][$code] ?? []]);
    if (!$valid) { $err = 'الرابط غير صالح أو انتهت صلاحيته. اطلب رابطًا جديدًا من إدارة التسويق.'; }
    elseif (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && $csrfOk()) {
        $em = strtolower(trim((string) ($_POST['email'] ?? '')));
        $p1 = (string) ($_POST['p1'] ?? ''); $p2 = (string) ($_POST['p2'] ?? '');
        if (!filter_var($em, FILTER_VALIDATE_EMAIL)) $err = 'اكتب بريدًا إلكترونيًا صحيحًا.';
        elseif (mb_strlen($p1) < 10) $err = 'كلمة المرور لازم تكون 10 حروف أو أرقام على الأقل.';
        elseif ($p1 !== $p2) $err = 'كلمتا المرور غير متطابقتين.';
        else {
            rep_write($code, 'user.json', ['email' => $em, 'hash' => password_hash($p1, PASSWORD_DEFAULT), 'set' => time()]);
            @unlink(rep_dir($code) . '/invite.json'); $tokUse($tok);
            rep_append($code, 'changes.jsonl', ['t' => time(), 'by' => $em, 'what' => 'تفعيل الحساب']);
            session_regenerate_id(true);
            $_SESSION['rep'] = $code; $_SESSION['email'] = $em; $_SESSION['since'] = time();
            header('Location: ' . $D()); exit;
        }
    }
}
elseif ($sub === 'logout') {
    $_SESSION = []; session_destroy();
    header('Location: ' . $D()); exit;
}
elseif (!$authed) {
    $page = 'login';
    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && !$locked && $csrfOk()) {
        $em = strtolower(trim((string) ($_POST['email'] ?? '')));
        if (!empty($user['email']) && hash_equals((string) $user['email'], $em) && password_verify((string) ($_POST['password'] ?? ''), (string) $user['hash'])) {
            @unlink($lockFile);
            session_regenerate_id(true);
            $_SESSION['rep'] = $code; $_SESSION['email'] = $em; $_SESSION['since'] = time();
            header('Location: ' . $D()); exit;
        }
        $fail(); $err = $locked ? 'محاولات كثيرة. حاول بعد ربع ساعة.' : 'البريد أو كلمة المرور غير صحيحة.';
    } elseif ($locked) { $err = 'محاولات كثيرة. حاول بعد ربع ساعة.'; }
}
/* ---------- logged in ---------- */
else {
    $page = in_array($sub, ['settings', 'content'], true) ? $sub : 'stats';
    $who = (string) $_SESSION['email'];

    if ($page === 'settings' && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        if (!$csrfOk()) { $err = 'انتهت الجلسة، حدّث الصفحة وحاول مرة ثانية.'; }
        else {
            $in = fn(string $k): string => trim((string) ($_POST[$k] ?? ''));
            $digits = preg_replace('/\D/', '', strtr($in('phone'), ['٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9']));
            if (preg_match('/^05\d{8}$/', $digits)) $digits = '966' . substr($digits, 1);
            if (preg_match('/^5\d{8}$/', $digits)) $digits = '966' . $digits;
            $new = [
                'phone' => $digits,
                'wa_text' => rep_clean($in('wa_text'), 160),
                'meta_pixel' => $in('meta_pixel'), 'tiktok_pixel' => strtoupper($in('tiktok_pixel')),
                'snap_pixel' => strtolower($in('snap_pixel')), 'google_tag' => strtoupper($in('google_tag')),
            ];
            $bad = [];
            if (!preg_match('/^9665\d{8}$/', $new['phone'])) $bad[] = 'رقم الواتساب لازم يكون رقم جوال سعودي (05xxxxxxxx).';
            if ($new['meta_pixel'] !== '' && !preg_match('/^\d{10,20}$/', $new['meta_pixel'])) $bad[] = 'رقم بيكسل ميتا أرقام فقط (10 إلى 20 رقم).';
            if ($new['tiktok_pixel'] !== '' && !preg_match('/^[A-Z0-9]{10,30}$/', $new['tiktok_pixel'])) $bad[] = 'كود بيكسل تيك توك غير صحيح.';
            if ($new['snap_pixel'] !== '' && !preg_match('/^[a-f0-9-]{20,40}$/', $new['snap_pixel'])) $bad[] = 'كود بيكسل سناب غير صحيح.';
            if ($new['google_tag'] !== '' && !preg_match('/^(G|AW|GT)-[A-Z0-9]{4,20}$/', $new['google_tag'])) $bad[] = 'كود جوجل لازم يبدأ بـ G- أو AW-.';
            if ($bad) { $err = implode(' ', $bad); }
            else {
                $old = rep_read($code, 'settings.json');
                rep_write($code, 'settings.json', $new);
                $diff = array_keys(array_filter($new, fn($v, $k) => ($old[$k] ?? ($rep[$k] ?? '')) !== $v, ARRAY_FILTER_USE_BOTH));
                rep_append($code, 'changes.jsonl', ['t' => time(), 'by' => $who, 'what' => 'الإعدادات: ' . implode('، ', $diff)]);
                $rep = rep_effective($rep);
                $waBase = $new['wa_text'] ?: 'السلام عليكم، أبي أستفسر عن خدمات الرعاية المنزلية';
                $msg = 'اتحفظت الإعدادات، وظهرت على الموقع.';
            }
        }
    }
    if ($page === 'content' && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        if (!$csrfOk()) { $err = 'انتهت الجلسة، حدّث الصفحة وحاول مرة ثانية.'; }
        else {
            $lim = ['promo' => 90, 'hh' => 30, 'tall' => 50, 'plan_m' => 160, 'plan_y' => 160, 'close_t' => 50, 'close_p' => 160];
            $new = [];
            foreach ($lim as $k => $n) { $v = rep_clean((string) ($_POST[$k] ?? ''), $n); if ($v !== '') $new[$k] = $v; }
            foreach (range(0, 3) as $i) {
                foreach (['title' => 40, 'pill' => 24, 'sub' => 36] as $k => $n) {
                    $v = rep_clean((string) ($_POST['slides'][$i][$k] ?? ''), $n);
                    if ($v !== '') $new['slides'][$i][$k] = $v;
                }
            }
            $faq = [];
            foreach ((array) ($_POST['faq'] ?? []) as $f) {
                $fq = rep_clean((string) ($f['q'] ?? ''), 120); $fa = rep_clean((string) ($f['a'] ?? ''), 500);
                if ($fq !== '' && $fa !== '') $faq[] = ['q' => $fq, 'a' => $fa];
            }
            if ($faq) $new['faq'] = array_slice($faq, 0, 15);
            rep_write($code, 'content.json', $new);
            rep_append($code, 'changes.jsonl', ['t' => time(), 'by' => $who, 'what' => 'المحتوى']);
            $content = $new;
            $msg = 'اتحفظ المحتوى، وظهر على الموقع.';
        }
    }
}

/* ---------- stats ---------- */
if ($page === 'stats') {
    $days = in_array((int) ($_GET['d'] ?? 30), [7, 30, 90], true) ? (int) ($_GET['d'] ?? 30) : 30;
    $since = strtotime('today -' . ($days - 1) . ' days');
    $ev = rep_events($code, $since);
    $views = array_filter($ev, fn($x) => $x['e'] === 'view');
    $vis = []; $byDay = []; $pages = []; $src = []; $svc = []; $dev = ['m' => [], 'd' => []]; $cnt = ['whatsapp' => 0, 'call' => 0, 'quiz' => 0];
    for ($t = $since; $t <= time(); $t += 86400) $byDay[date('Y-m-d', $t)] = ['v' => [], 'c' => 0];
    foreach ($ev as $x) {
        $day = date('Y-m-d', (int) $x['t']);
        if ($x['e'] === 'view') {
            $vis[$x['v']] = 1; $dev[$x['d'] ?? 'm'][$x['v']] = 1;
            if (isset($byDay[$day])) $byDay[$day]['v'][$x['v']] = 1;
            $pages[$x['p']] = ($pages[$x['p']] ?? 0) + 1;
            if (($x['r'] ?? '') !== '') $src[$x['r']] = ($src[$x['r']] ?? 0) + 1;
            if (($x['s'] ?? '') !== '') $svc[$x['s']] = ($svc[$x['s']] ?? 0) + 1;
        } else {
            $cnt[$x['e']] = ($cnt[$x['e']] ?? 0) + 1;
            if (isset($byDay[$day])) $byDay[$day]['c']++;
            if (($x['s'] ?? '') !== '') $svc[$x['s']] = ($svc[$x['s']] ?? 0) + 3;   // a request weighs more than a visit
        }
    }
    arsort($pages); arsort($src); arsort($svc);
    $nVis = count($vis); $nCon = array_sum($cnt);
    $recent = array_slice(array_reverse(array_values(array_filter($ev, fn($x) => $x['e'] !== 'view'))), 0, 12);
    $pageNames = ['/' => 'الرئيسية', '/services' => 'الخدمات', '/about' => 'من نحن', '/faq' => 'الأسئلة الشائعة', '/contact' => 'تواصل معنا'];
    foreach ($data['sections'] as $s) $pageNames['/services/' . $s['slug']] = $s['name'];
}

$site = ($local ? 'http://' . $_SERVER['HTTP_HOST'] . '/' . $q : 'https://' . $host . '/');
$qr = '/qr/' . strtolower((string) (($rep['subdomains'][0] ?? '') ?: $rep['slug'])) . '.png';
$evName = ['whatsapp' => 'واتساب', 'call' => 'اتصال', 'quiz' => 'طلب من الاستبيان'];
$bar = function (array $items, int $limit = 6) : string {
    if (!$items) return '<p class="mut">لسه مفيش بيانات.</p>';
    $max = max($items); $h = '<ul class="bars">';
    foreach (array_slice($items, 0, $limit, true) as $k => $n) $h .= '<li><span>' . e($k) . '</span><i style="--w:' . round($n / $max * 100) . '%"></i><b>' . $n . '</b></li>';
    return $h . '</ul>';
};
$fld = fn(string $name, string $label, string $val, string $ph, int $max, bool $area = false): string =>
    '<label><span>' . e($label) . '</span>' . ($area
        ? '<textarea name="' . e($name) . '" maxlength="' . $max . '" rows="2" placeholder="' . e($ph) . '">' . e($val) . '</textarea>'
        : '<input name="' . e($name) . '" maxlength="' . $max . '" value="' . e($val) . '" placeholder="' . e($ph) . '">') . '</label>';
?><!doctype html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="robots" content="noindex, nofollow">
<title>لوحة التحكم | <?= e(BRAND) ?></title>
<link rel="icon" href="/img/mark.png">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Alexandria:wght@500;600;700&family=IBM+Plex+Sans+Arabic:wght@400;500;600&display=swap">
<style>
:root{--ink:#0b3a27;--ink2:#5b6b61;--line:#e8e2d2;--green:#0f7a46;--gold:#c9a24e;--gold-lt:#ecd8a4;--mint:#e8f3eb;--cream:#f6f0e1;--bg:#faf8f2;--red:#b3261e;--wa:#1fa855;--h:"Alexandria","IBM Plex Sans Arabic",Tahoma,sans-serif;--b:"IBM Plex Sans Arabic",Tahoma,sans-serif}
*{box-sizing:border-box}
body{margin:0;background:var(--bg);color:var(--ink);font:15px/1.6 var(--b)}
a{color:var(--green)}
h1,h2,h3{font-family:var(--h);margin:0;line-height:1.3}
.wrap{width:min(1080px,100% - 28px);margin:0 auto}
.top{background:var(--ink);color:#fff}
.top .wrap{display:flex;align-items:center;gap:12px;min-height:60px;flex-wrap:wrap}
.top img{width:34px;height:34px;border-radius:50%;background:#fff;padding:3px}
.top b{font:700 15px var(--h)}
.top small{display:block;font-size:12px;opacity:.75}
.top .sp{flex:1}
.top a{color:#fff;text-decoration:none;font-size:13px;opacity:.85}
.tabs{display:flex;gap:6px;overflow-x:auto;padding:12px 0 0}
.tabs a{padding:9px 16px;border-radius:999px;background:#fff;border:1px solid var(--line);text-decoration:none;color:var(--ink);font:600 14px var(--h);white-space:nowrap}
.tabs a[aria-current]{background:var(--ink);color:#fff;border-color:var(--ink)}
main{padding:16px 0 60px}
.card{background:#fff;border:1px solid var(--line);border-radius:18px;padding:18px;margin-top:14px}
.card h2{font-size:17px;margin-bottom:12px}
.mut{color:var(--ink2);font-size:14px;margin:0}
.kpis{display:grid;grid-template-columns:repeat(2,1fr);gap:10px;margin-top:14px}
.kpi{background:#fff;border:1px solid var(--line);border-radius:16px;padding:14px}
.kpi span{font-size:13px;color:var(--ink2)}
.kpi b{display:block;font:700 26px/1.2 var(--h);margin-top:4px}
.kpi.hi{background:var(--ink);color:#fff;border-color:var(--ink)}.kpi.hi span{color:var(--gold-lt)}
@media(min-width:760px){.kpis{grid-template-columns:repeat(5,1fr)}.grid2{display:grid;grid-template-columns:1fr 1fr;gap:14px}.grid2 .card{margin-top:14px}}
.period{display:flex;gap:6px;margin-top:14px}
.period a{padding:6px 12px;border-radius:999px;border:1px solid var(--line);background:#fff;text-decoration:none;color:var(--ink);font-size:13px}
.period a[aria-current]{background:var(--gold);border-color:var(--gold);font-weight:600}
.chart{display:flex;align-items:flex-end;gap:2px;height:130px;padding-top:8px}
.chart div{flex:1;display:flex;flex-direction:column;justify-content:flex-end;gap:1px;min-width:0}
.chart i{display:block;background:var(--mint);border-radius:3px 3px 0 0}
.chart em{display:block;background:var(--green);border-radius:3px 3px 0 0}
.legend{display:flex;gap:14px;font-size:12px;color:var(--ink2);margin-top:8px}
.legend span::before{content:"";display:inline-block;width:10px;height:10px;border-radius:2px;margin-inline-end:5px;vertical-align:-1px;background:var(--mint)}
.legend span:last-child::before{background:var(--green)}
.bars{list-style:none;padding:0;margin:0;display:grid;gap:8px}
.bars li{display:grid;grid-template-columns:minmax(90px,38%) 1fr 34px;align-items:center;gap:8px;font-size:14px}
.bars span{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.bars i{height:10px;border-radius:5px;background:linear-gradient(90deg,var(--gold),var(--green));width:var(--w)}
.bars b{font-weight:600;text-align:left}
.rows{list-style:none;padding:0;margin:0}
.rows li{display:flex;justify-content:space-between;gap:10px;padding:9px 0;border-bottom:1px solid var(--line);font-size:14px}
.rows li:last-child{border:0}
.tag{display:inline-block;padding:2px 9px;border-radius:999px;font-size:12px;font-weight:600;background:var(--mint);color:var(--green)}
.tag.call{background:var(--cream);color:#8a6a1f}.tag.quiz{background:#e8edfb;color:#24408e}
form label{display:block;margin-top:12px}
form label span{display:block;font-weight:600;font-size:14px;margin-bottom:5px}
input,textarea{width:100%;padding:11px 13px;border:1px solid var(--line);border-radius:12px;font:inherit;background:#fdfcf8;color:var(--ink)}
input:focus,textarea:focus{outline:2px solid rgba(15,122,70,.3);border-color:var(--green)}
input[dir=ltr]{text-align:left}
.btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;margin-top:16px;padding:12px 22px;border:0;border-radius:999px;background:var(--ink);color:#fff;font:600 15px var(--h);cursor:pointer;text-decoration:none}
.btn.gold{background:var(--gold);color:var(--ink)}
.note{margin-top:12px;padding:11px 14px;border-radius:12px;font-size:14px}
.note.ok{background:var(--mint);color:var(--green)}.note.bad{background:#fdecec;color:var(--red)}
.hint{font-size:12.5px;color:var(--ink2);margin-top:4px}
.preview{background:var(--mint);border-radius:12px;padding:10px 13px;font-size:14px;margin-top:8px}
fieldset{border:1px solid var(--line);border-radius:14px;padding:4px 14px 14px;margin:14px 0 0}
legend{font:600 14px var(--h);padding:0 6px;color:var(--gold)}
.faqrow{border-top:1px dashed var(--line);margin-top:12px;padding-top:2px}
.auth{min-height:100vh;display:grid;place-items:center;padding:24px 14px}
.auth .card{width:100%;max-width:400px;padding:26px 22px}
.auth .brand{display:flex;align-items:center;gap:12px;margin-bottom:6px}
.auth .brand img{width:46px}
.auth .brand b{display:block;font:700 17px var(--h)}
.auth .brand small{color:var(--ink2);font-size:13px}
.auth .btn{width:100%}
.copy{word-break:break-all;background:var(--cream);border-radius:12px;padding:12px;font-size:13px;direction:ltr;text-align:left;margin-top:10px}
</style>
</head>
<body>
<?php if (in_array($page, ['login', 'setup', 'invite', 'adminsetup'], true)): ?>
<div class="auth"><form class="card" method="post" autocomplete="on">
  <?= $csrf ?>
  <div class="brand"><img src="/img/mark.png" alt=""><div><b>لوحة تحكم الموقع</b><small><?= e($host) ?></small></div></div>
  <?php if ($page === 'login'): ?>
    <?php if (empty($user['email'])): ?>
      <p class="mut">الحساب لسه متفعّلش. هيوصلك رابط تفعيل من إدارة التسويق، افتحه واختار بريدك وكلمة المرور.</p>
    <?php else: ?>
      <label><span>البريد الإلكتروني</span><input type="email" name="email" dir="ltr" required autocomplete="username" <?= $locked ? 'disabled' : '' ?>></label>
      <label><span>كلمة المرور</span><input type="password" name="password" required autocomplete="current-password" <?= $locked ? 'disabled' : '' ?>></label>
      <button class="btn" type="submit" <?= $locked ? 'disabled' : '' ?>>دخول</button>
      <p class="hint" style="margin-top:12px">نسيت كلمة المرور؟ اطلب رابط تفعيل جديد من إدارة التسويق.</p>
    <?php endif; ?>
  <?php elseif ($page === 'adminsetup'): ?>
    <?php if (!empty($valid)): ?>
      <p class="mut">حساب الإدارة لمواقع المسوّقين. بيه بتعمل روابط التفعيل للمسوّقين من صفحة /dashboard/invite في أي موقع منهم.</p>
      <input type="hidden" name="t" value="<?= e($tok) ?>">
      <label><span>البريد الإلكتروني</span><input type="email" name="email" dir="ltr" required autocomplete="username"></label>
      <label><span>كلمة المرور (10 على الأقل)</span><input type="password" name="p1" minlength="10" required autocomplete="new-password"></label>
      <label><span>أعد كتابة كلمة المرور</span><input type="password" name="p2" minlength="10" required autocomplete="new-password"></label>
      <button class="btn" type="submit">إنشاء حساب الإدارة</button>
    <?php endif; ?>
  <?php elseif ($page === 'setup'): ?>
    <?php if ($err === '' || !empty($valid)): ?>
      <p class="mut">اختار البريد وكلمة المرور اللي هتدخل بيهم لوحة التحكم.</p>
      <input type="hidden" name="t" value="<?= e($tok) ?>">
      <label><span>البريد الإلكتروني</span><input type="email" name="email" dir="ltr" required autocomplete="username" value="<?= e((string) ($_POST['email'] ?? '')) ?>"></label>
      <label><span>كلمة المرور (10 على الأقل)</span><input type="password" name="p1" minlength="10" required autocomplete="new-password"></label>
      <label><span>أعد كتابة كلمة المرور</span><input type="password" name="p2" minlength="10" required autocomplete="new-password"></label>
      <button class="btn" type="submit">تفعيل الحساب</button>
    <?php endif; ?>
  <?php else: ?>
    <?php if ($link !== ''): ?>
      <p class="mut">ده رابط التفعيل، صالح لمدة 3 أيام ولمرة واحدة. ابعته للمسوّق بنفسك، وهو يختار بريده وكلمة المرور.</p>
      <div class="copy" id="lnk"><?= e($link) ?></div>
      <button class="btn gold" type="button" onclick="navigator.clipboard&&navigator.clipboard.writeText(document.getElementById('lnk').textContent).then(function(){this.textContent='اتنسخ ✓'}.bind(this))">انسخ الرابط</button>
      <p class="hint" style="margin-top:12px">لو المسوّق نسي كلمة المرور، اعمل رابط جديد من نفس الصفحة.</p>
    <?php else: ?>
      <p class="mut">للإدارة فقط: ادخل ببيانات حساب الإدارة، علشان تعمل رابط تفعيل لصاحب الموقع ده.</p>
      <?php if ($noAdmins): ?><div class="note bad">مفيش حساب إدارة لسه. لازم يتعمل الأول من رابط إعداد الإدارة.</div><?php endif; ?>
      <label><span>البريد الإلكتروني</span><input type="email" name="email" dir="ltr" required autocomplete="username" <?= $locked ? 'disabled' : '' ?>></label>
      <label><span>كلمة المرور</span><input type="password" name="password" required autocomplete="current-password" <?= $locked ? 'disabled' : '' ?>></label>
      <button class="btn" type="submit" <?= $locked ? 'disabled' : '' ?>>اعمل رابط التفعيل</button>
    <?php endif; ?>
  <?php endif; ?>
  <?php if ($err !== ''): ?><div class="note bad"><?= e($err) ?></div><?php endif; ?>
</form></div>

<?php else: ?>
<header class="top"><div class="wrap">
  <img src="/img/mark.png" alt=""><div><b>لوحة التحكم</b><small><?= e($host) ?></small></div>
  <span class="sp"></span>
  <a href="<?= e($site) ?>" target="_blank" rel="noopener">فتح الموقع ↗</a>
  <a href="<?= e($D('logout')) ?>">خروج</a>
</div></header>
<div class="wrap">
  <nav class="tabs"><a href="<?= e($D()) ?>"<?= $page === 'stats' ? ' aria-current="page"' : '' ?>>الإحصائيات</a><a href="<?= e($D('settings')) ?>"<?= $page === 'settings' ? ' aria-current="page"' : '' ?>>الإعدادات</a><a href="<?= e($D('content')) ?>"<?= $page === 'content' ? ' aria-current="page"' : '' ?>>محتوى الموقع</a></nav>
  <main>
  <?php if ($msg): ?><div class="note ok"><?= e($msg) ?></div><?php endif; ?>
  <?php if ($err): ?><div class="note bad"><?= e($err) ?></div><?php endif; ?>

  <?php if ($page === 'stats'): ?>
    <div class="period"><?php foreach ([7 => 'آخر 7 أيام', 30 => 'آخر 30 يوم', 90 => 'آخر 90 يوم'] as $d => $t): ?><a href="<?= e($D() . ($local ? '&' : '?') . 'd=' . $d) ?>"<?= $days === $d ? ' aria-current="page"' : '' ?>><?= $t ?></a><?php endforeach; ?></div>
    <div class="kpis">
      <div class="kpi"><span>زوّار</span><b><?= $nVis ?></b></div>
      <div class="kpi"><span>ضغطات واتساب</span><b><?= $cnt['whatsapp'] ?></b></div>
      <div class="kpi"><span>اتصالات</span><b><?= $cnt['call'] ?></b></div>
      <div class="kpi"><span>طلبات الاستبيان</span><b><?= $cnt['quiz'] ?></b></div>
      <div class="kpi hi"><span>نسبة التواصل</span><b><?= $nVis ? round($nCon / $nVis * 100) : 0 ?>%</b></div>
    </div>
    <div class="card"><h2>كل يوم</h2>
      <?php $mx = max(1, max(array_map(fn($x) => count($x['v']) + $x['c'], $byDay))); ?>
      <div class="chart"><?php foreach ($byDay as $day => $x): ?><div title="<?= e($day) ?>: <?= count($x['v']) ?> زائر، <?= $x['c'] ?> تواصل"><i style="height:<?= round(count($x['v']) / $mx * 120) ?>px"></i><em style="height:<?= round($x['c'] / $mx * 120) ?>px"></em></div><?php endforeach; ?></div>
      <div class="legend"><span>زوّار</span><span>تواصل (واتساب + اتصال + استبيان)</span></div>
    </div>
    <div class="grid2">
      <div class="card"><h2>الزوّار جايين منين</h2><?= $bar($src) ?></div>
      <div class="card"><h2>الخدمات اللي عليها اهتمام</h2><?= $bar($svc) ?></div>
      <div class="card"><h2>أكتر الصفحات زيارة</h2><?= $bar(array_combine(array_map(fn($p) => $pageNames[$p] ?? $p, array_keys($pages)), array_values($pages)) ?: []) ?></div>
      <div class="card"><h2>آخر طلبات التواصل</h2>
        <?php if (!$recent): ?><p class="mut">لسه مفيش.</p><?php else: ?><ul class="rows"><?php foreach ($recent as $x): ?><li><span><span class="tag <?= e($x['e']) ?>"><?= e($evName[$x['e']] ?? $x['e']) ?></span> <?= e(($x['s'] ?? '') ?: ($pageNames[$x['p']] ?? $x['p'])) ?></span><span class="mut"><?= e(date('j/n · g:i a', (int) $x['t'])) ?></span></li><?php endforeach; ?></ul><?php endif; ?>
      </div>
    </div>
    <div class="card"><h2>الأجهزة</h2><?= $bar(array_filter(['موبايل' => count($dev['m']), 'كمبيوتر' => count($dev['d'])])) ?>
      <p class="hint" style="margin-top:12px">الأرقام دي من الموقع نفسه، من غير كوكيز. الزائر بيتحسب مرة واحدة في اليوم. «ضغطات واتساب» معناها إن الزائر فتح واتساب، مش لازم يكون بعت الرسالة.</p>
    </div>

  <?php elseif ($page === 'settings'): ?>
    <form class="card" method="post"><?= $csrf ?>
      <h2>التواصل</h2>
      <label><span>رقم الواتساب والاتصال</span><input name="phone" dir="ltr" inputmode="tel" required value="<?= e('0' . substr((string) $rep['phone'], 3)) ?>" placeholder="05xxxxxxxx"></label>
      <p class="hint">كل أزرار الواتساب والاتصال في الموقع بتروح للرقم ده.</p>
      <label><span>رسالة الواتساب الجاهزة</span><input name="wa_text" maxlength="160" value="<?= e($waBase) ?>"></label>
      <div class="preview">الرسالة هتوصل كده: <b><?= e($waBase) ?> (<?= e($code) ?>)</b></div>
      <p class="hint">الكود (<?= e($code) ?>) بيتضاف في الآخر لوحده ومينفعش يتشال، علشان الطلب يتحسب لك.</p>
      <h2 style="margin-top:22px">البيكسلات (اختياري)</h2>
      <p class="mut">حط بيكسلاتك انت بس. أي ضغطة واتساب أو اتصال بتتسجل عندهم «Contact».</p>
      <label><span>بيكسل ميتا (فيسبوك وإنستجرام)</span><input name="meta_pixel" dir="ltr" value="<?= e((string) ($rep['meta_pixel'] ?? '')) ?>" placeholder="123456789012345"></label>
      <label><span>بيكسل تيك توك</span><input name="tiktok_pixel" dir="ltr" value="<?= e((string) ($rep['tiktok_pixel'] ?? '')) ?>" placeholder="C1A2B3C4D5E6F7G8H9"></label>
      <label><span>بيكسل سناب شات</span><input name="snap_pixel" dir="ltr" value="<?= e((string) ($rep['snap_pixel'] ?? '')) ?>" placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx"></label>
      <label><span>جوجل (Analytics أو Ads)</span><input name="google_tag" dir="ltr" value="<?= e((string) ($rep['google_tag'] ?? '')) ?>" placeholder="G-XXXXXXX أو AW-XXXXXXX"></label>
      <button class="btn" type="submit">حفظ الإعدادات</button>
    </form>
    <div class="card"><h2>روابط الموقع</h2>
      <ul class="rows"><li><span>الموقع</span><a href="<?= e($site) ?>" target="_blank" rel="noopener" dir="ltr"><?= e($host) ?></a></li><li><span>صورة الـ QR</span><a href="<?= e($qr) ?>" target="_blank" rel="noopener">فتح وتحميل</a></li></ul>
    </div>

  <?php elseif ($page === 'content'): ?>
    <?php
      $theme = (string) ($rep['theme'] ?? '');
      $cv = fn(string $k): string => (string) ($content[$k] ?? '');
      $defSlides = [['رعاية كبار السن في البيت', 'اشتراك سنوي', 'نفس الفريق طول السنة'], ['علاج طبيعي بعد العمليات', 'اشتراك شهري', 'جلسات في بيتك'], ['تمريض منزلي منتظم', 'شهري أو سنوي', 'ممرضون مرخّصون'], ['رعاية الأم والمولود', 'اشتراك شهري', 'بعد الولادة مباشرة']];
      $faqNow = $data['faq'];
    ?>
    <form class="card" method="post"><?= $csrf ?>
      <p class="mut">اللي تسيبه فاضي بيرجع للنص الأصلي. الموقع باسم المركز، فاكتب كلام دقيق ومن غير أرقام تليفونات أو روابط (بتتشال لوحدها). والأسعار والتخفيضات مايتكتبوش هنا.</p>
      <?php if ($theme === 'bento'): ?>
      <fieldset><legend>أعلى الصفحة</legend>
        <?= $fld('promo', 'الشريط اللي فوق', $cv('promo'), 'رعاية منزلية باشتراك من شهر حتى سنة.', 90) ?>
        <?= $fld('hh', 'العنوان الكبير', $cv('hh'), 'رعايتكم علينا', 30) ?>
        <?= $fld('tall', 'عنوان كارت التمريض', $cv('tall'), 'التمريض المنزلي وممرضة مقيمة', 50) ?>
      </fieldset>
      <fieldset><legend>السلايدر (4 صور)</legend>
        <?php foreach ($defSlides as $i => $d): ?>
          <div class="faqrow"><p class="hint" style="margin-top:8px">الصورة <?= $i + 1 ?></p>
          <?= $fld("slides[$i][title]", 'العنوان', (string) ($content['slides'][$i]['title'] ?? ''), $d[0], 40) ?>
          <?= $fld("slides[$i][pill]", 'الكبسولة', (string) ($content['slides'][$i]['pill'] ?? ''), $d[1], 24) ?>
          <?= $fld("slides[$i][sub]", 'تحت الكبسولة', (string) ($content['slides'][$i]['sub'] ?? ''), $d[2], 36) ?></div>
        <?php endforeach; ?>
      </fieldset>
      <fieldset><legend>الاشتراكات</legend>
        <?= $fld('plan_m', 'وصف الاشتراك الشهري', $cv('plan_m'), 'للتعافي بعد العمليات والإصابات، والحالات اللي تحتاج متابعة لفترة محددة.', 160, true) ?>
        <?= $fld('plan_y', 'وصف الاشتراك السنوي', $cv('plan_y'), 'لكبار السن والأمراض المزمنة، بخطة رعاية تُراجع كل فترة ونفس الفريق طول السنة.', 160, true) ?>
      </fieldset>
      <fieldset><legend>آخر الصفحة</legend>
        <?= $fld('close_t', 'العنوان', $cv('close_t'), 'رعاية أهلكم تبدأ برسالة', 50) ?>
        <?= $fld('close_p', 'النص', $cv('close_p'), 'جاوب على ثلاثة أسئلة سريعة، ويتواصل معك الفريق الطبي على واتساب بالخطة المناسبة.', 160, true) ?>
      </fieldset>
      <?php endif; ?>
      <fieldset><legend>الأسئلة الشائعة</legend>
        <p class="hint">امسح السؤال والإجابة علشان تشيله. فيه 3 خانات فاضية في الآخر لو عايز تزوّد.</p>
        <?php foreach (array_merge($faqNow, array_fill(0, 3, ['q' => '', 'a' => ''])) as $i => $f): ?>
          <div class="faqrow">
          <?= $fld("faq[$i][q]", 'السؤال ' . ($i + 1), (string) $f['q'], 'سؤال جديد', 120) ?>
          <?= $fld("faq[$i][a]", 'الإجابة', (string) $f['a'], '', 500, true) ?></div>
        <?php endforeach; ?>
      </fieldset>
      <button class="btn" type="submit">حفظ المحتوى</button>
    </form>
  <?php endif; ?>
  </main>
</div>
<?php endif; ?>
</body>
</html>
