<?php
/*
 * Runtime storage for the rep sites: what the rep changes from his dashboard and the site's own visit/click log.
 * Lives OUTSIDE public_html (deploys never touch it, the web can't read it):
 *     /home/<cpanel-user>/afnan-reps-data/<CODE>/
 *         settings.json   number, WhatsApp text, pixels (override reps.json)
 *         content.json    texts the rep edited (override the theme's defaults)
 *         events-YYYY-MM.jsonl   one line per page view / WhatsApp / call / quiz send
 *         user.json       the rep's dashboard login (email + password hash)
 *         invite.json     one-time setup link (hash only) created by an admin
 *         changes.jsonl   who changed what, when
 * Local preview (php -S) uses reps/.data/ instead.
 */
declare(strict_types=1);

function rep_data_root(): string {
    static $root = null;
    if ($root !== null) return $root;
    $root = PHP_SAPI === 'cli-server' ? __DIR__ . '/.data' : dirname(__DIR__, 2) . '/afnan-reps-data';
    if (!is_dir($root)) @mkdir($root, 0700, true);
    return $root;
}
function rep_dir(string $code): string {
    $d = rep_data_root() . '/' . preg_replace('/[^A-Z0-9]/', '', strtoupper($code));
    if (!is_dir($d)) @mkdir($d, 0700, true);
    return $d;
}
function rep_read(string $code, string $file, $default = []) {
    $f = rep_dir($code) . '/' . $file;
    if (!is_file($f)) return $default;
    $v = json_decode((string) file_get_contents($f), true);
    return is_array($v) ? $v : $default;
}
function rep_write(string $code, string $file, array $value): bool {
    $f = rep_dir($code) . '/' . $file;
    $tmp = $f . '.' . bin2hex(random_bytes(4)) . '.tmp';
    if (@file_put_contents($tmp, json_encode($value, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX) === false) return false;
    return @rename($tmp, $f);
}
function rep_append(string $code, string $file, array $line): void {
    @file_put_contents(rep_dir($code) . '/' . $file, json_encode($line, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND | LOCK_EX);
}

/* ---------- the rep as the site sees him: reps.json + what he changed from the dashboard ---------- */
const REP_EDITABLE = ['phone', 'wa_text', 'meta_pixel', 'tiktok_pixel', 'snap_pixel', 'google_tag'];
function rep_effective(array $rep): array {
    $s = rep_read((string) $rep['code'], 'settings.json');
    foreach (REP_EDITABLE as $k) { if (isset($s[$k]) && is_string($s[$k])) $rep[$k] = $s[$k]; }
    return $rep;
}

/* ---------- visit / click log (first-party, no cookies) ---------- */
function rep_is_bot(): bool {
    $ua = (string) ($_SERVER['HTTP_USER_AGENT'] ?? '');
    return $ua === '' || (bool) preg_match('/bot|crawl|spider|slurp|preview|facebookexternalhit|whatsapp|telegram|curl|wget|python|headless|lighthouse|monitor|uptime/i', $ua);
}
function rep_visitor(string $code): string {
    $saltFile = rep_data_root() . '/.salt';
    if (!is_file($saltFile)) @file_put_contents($saltFile, bin2hex(random_bytes(16)));
    $salt = (string) @file_get_contents($saltFile);
    return substr(hash('sha256', $salt . '|' . $code . '|' . ($_SERVER['REMOTE_ADDR'] ?? '') . '|' . ($_SERVER['HTTP_USER_AGENT'] ?? '') . '|' . gmdate('Y-m-d')), 0, 12);
}
function rep_source(string $ref, string $qs): string {
    parse_str($qs, $q);
    $hay = strtolower(($q['utm_source'] ?? '') . ' ' . (isset($q['fbclid']) ? 'facebook' : '') . ' ' . (isset($q['ttclid']) ? 'tiktok' : '') . ' ' . (isset($q['ScCid']) ? 'snapchat' : '') . ' ' . (isset($q['gclid']) ? 'google' : '') . ' ' . (isset($q['ref']) ? 'qr' : '') . ' ' . $ref);
    foreach (['instagram' => 'إنستجرام', 'facebook' => 'فيسبوك', 'fb.' => 'فيسبوك', 'tiktok' => 'تيك توك', 'snapchat' => 'سناب شات', 'snap' => 'سناب شات', 'google' => 'جوجل', 'whatsapp' => 'واتساب', 'wa.me' => 'واتساب', 't.co' => 'إكس', 'twitter' => 'إكس', 'x.com' => 'إكس', 'youtube' => 'يوتيوب', 'qr' => 'QR'] as $k => $v) {
        if (str_contains($hay, $k)) return $v;
    }
    $h = parse_url($ref, PHP_URL_HOST);
    return $h ? (string) preg_replace('/^www\./', '', $h) : 'مباشر';
}
function rep_log(string $code, string $type, string $page, string $svc = '', string $src = ''): void {
    if (rep_is_bot()) return;
    $ua = (string) ($_SERVER['HTTP_USER_AGENT'] ?? '');
    rep_append($code, 'events-' . gmdate('Y-m') . '.jsonl', [
        't' => time(), 'e' => $type, 'p' => mb_substr($page, 0, 80), 'v' => rep_visitor($code),
        'd' => preg_match('/Mobi|Android|iPhone/i', $ua) ? 'm' : 'd',
        's' => mb_substr($svc, 0, 60), 'r' => mb_substr($src, 0, 40),
    ]);
}
function rep_events(string $code, int $since): array {
    $out = [];
    $y = (int) gmdate('Y', $since); $mo = (int) gmdate('n', $since);
    for ($m = gmmktime(0, 0, 0, $mo, 1, $y); $m <= time(); $m = gmmktime(0, 0, 0, ++$mo, 1, $y)) {
        $f = rep_dir($code) . '/events-' . gmdate('Y-m', $m) . '.jsonl';
        if (!is_file($f)) continue;
        foreach (file($f, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $l) {
            $e = json_decode($l, true);
            if (is_array($e) && ($e['t'] ?? 0) >= $since) $out[] = $e;
        }
    }
    return $out;
}

/* ---------- text the rep types: no links, no phone numbers, bounded length ---------- */
function rep_clean(string $s, int $max): string {
    $s = trim(preg_replace('/\s+/u', ' ', strip_tags($s)));
    $s = preg_replace('~(https?://|www\.)\S+|\S+\.(com|sa|net|org|me|link|ly)\b\S*~iu', '', $s);
    $s = preg_replace('/[+٠-٩0-9][\s\-٠-٩0-9]{6,}/u', '', $s);      // phone-like runs
    return mb_substr(trim($s), 0, $max);
}
