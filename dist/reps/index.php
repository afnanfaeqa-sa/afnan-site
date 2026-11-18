<?php
/*
 * Authorised representative sites — one copy of the full Afnan site for every rep.
 * Served from public_html/reps/ as the document root of each subdomain (mahmoud.afnanfaeqasa.sa …).
 * site/ holds the main site's pages (built by build.py) with the center's numbers replaced by placeholders;
 * this file fills them with the rep's number, adds noindex and the rep's pixels.
 * Adding a rep = a subdomain + one entry in reps.json.
 */
declare(strict_types=1);

// local preview with `php -S`: let the built-in server hand out real files (css, images)
if (PHP_SAPI === 'cli-server' && is_file(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) { return false; }

const ROOT_DOMAIN = 'afnanfaeqasa.sa';

$reps = json_decode((string) file_get_contents(__DIR__ . '/reps.json'), true)['reps'] ?? [];

/* ---------- which rep? ---------- */
$host = strtolower(preg_replace('/:\d+$/', '', $_SERVER['HTTP_HOST'] ?? ''));
$slug = '';
if (str_ends_with($host, '.' . ROOT_DOMAIN)) {
    $parts = explode('.', substr($host, 0, -strlen('.' . ROOT_DOMAIN)));
    $slug = end($parts);                       // www.mahmoud → mahmoud
} elseif (in_array($host, ['localhost', '127.0.0.1'], true)) {
    $slug = (string) ($_GET['rep'] ?? ($_COOKIE['rep_preview'] ?? ''));   // local preview only
    if ($slug !== '') { setcookie('rep_preview', $slug); }
}
$rep = null;
foreach ($reps as $r) { if (($r['slug'] ?? '') === $slug) { $rep = $r; break; } }

header('X-Robots-Tag: noindex, nofollow');
if (!$rep) { http_response_code(404); header('Content-Type: text/html; charset=utf-8'); echo '<!doctype html><meta charset="utf-8"><meta name="robots" content="noindex"><title>غير موجود</title><p style="font-family:sans-serif;text-align:center;margin-top:20vh">الصفحة غير موجودة</p>'; exit; }

/* ---------- route: / → index.html, /about or /about.html → about.html, the 3 scripts ---------- */
$path = trim((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');
$file = $path === '' ? 'index.html' : $path;
if (!preg_match('/\.(html|js)$/', $file)) { $file .= '.html'; }
$src = __DIR__ . '/site/' . $file;
if (!preg_match('/^[a-z0-9-]+\.(html|js)$/', $file) || !is_file($src)) {
    http_response_code(404);
    $file = 'index.html';            // unknown path: show the home page with a 404 status
    $src = __DIR__ . '/site/index.html';
}

$phone = (string) $rep['phone'];                         // 9665XXXXXXXX
$local = '0' . substr($phone, 3);                        // 05XXXXXXXX
$display = '+966 ' . substr($phone, 3, 2) . ' ' . substr($phone, 5, 3) . ' ' . substr($phone, 8);
$out = strtr((string) file_get_contents($src), [
    '{{WA}}' => $phone, '{{LOCAL}}' => $local, '{{DISPLAY}}' => $display, '{{CODE}}' => (string) $rep['code'],
]);

if (str_ends_with($file, '.js')) {
    header('Content-Type: application/javascript; charset=utf-8');
    header('Cache-Control: no-cache');
    echo $out;
    exit;
}

/* ---------- pixels (only those with a value) ---------- */
function e($s): string { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }
$px = '';
if (!empty($rep['meta_pixel'])) {
    $id = e($rep['meta_pixel']);
    $px .= "<script>!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');fbq('init','$id');fbq('track','PageView');</script>\n";
}
if (!empty($rep['tiktok_pixel'])) {
    $id = e($rep['tiktok_pixel']);
    $px .= "<script>!function(w,d,t){w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];ttq.methods=['page','track','identify','instances','debug','on','off','once','ready','alias','group','enableCookie','disableCookie'];ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);ttq.instance=function(t){for(var e=ttq._i[t]||[],n=0;n<ttq.methods.length;n++)ttq.setAndDefer(e,ttq.methods[n]);return e};ttq.load=function(e,n){var i='https://analytics.tiktok.com/i18n/pixel/events.js';ttq._i=ttq._i||{},ttq._i[e]=[],ttq._i[e]._u=i,ttq._t=ttq._t||{},ttq._t[e]=+new Date,ttq._o=ttq._o||{},ttq._o[e]=n||{};var o=d.createElement('script');o.type='text/javascript',o.async=!0,o.src=i+'?sdkid='+e+'&lib='+t;var a=d.getElementsByTagName('script')[0];a.parentNode.insertBefore(o,a)};ttq.load('$id');ttq.page()}(window,document,'ttq');</script>\n";
}
if (!empty($rep['snap_pixel'])) {
    $id = e($rep['snap_pixel']);
    $px .= "<script>(function(e,t,n){if(e.snaptr)return;var a=e.snaptr=function(){a.handleRequest?a.handleRequest.apply(a,arguments):a.queue.push(arguments)};a.queue=[];var s='script';var r=t.createElement(s);r.async=!0;r.src=n;var u=t.getElementsByTagName(s)[0];u.parentNode.insertBefore(r,u)})(window,document,'https://sc-static.net/scevent.min.js');snaptr('init','$id',{});snaptr('track','PAGE_VIEW');</script>\n";
}
if (!empty($rep['google_tag'])) {
    $id = e($rep['google_tag']);
    $px .= "<script async src=\"https://www.googletagmanager.com/gtag/js?id=$id\"></script><script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments)}gtag('js',new Date());gtag('config','$id');</script>\n";
}

// every WhatsApp / call click (including links the site's scripts build) → Contact
$track = "<script>document.addEventListener('click',function(ev){var a=ev.target.closest&&ev.target.closest('a[href]');if(!a)return;var h=a.getAttribute('href')||'';var k=h.indexOf('wa.me')>-1?'whatsapp':(h.indexOf('tel:')===0?'call':'');if(!k)return;"
       . "try{if(window.fbq){fbq('track','Contact',{method:k});fbq('track','Lead',{method:k});}}catch(_){}"
       . "try{if(window.ttq){ttq.track('Contact',{method:k});}}catch(_){}"
       . "try{if(window.snaptr){snaptr('track','CUSTOM_EVENT_1',{description:'Contact '+k});}}catch(_){}"
       . "try{if(window.gtag){gtag('event','contact',{method:k});}}catch(_){}"
       . "},true);</script>";

header('Content-Type: text/html; charset=utf-8');
echo str_replace(['<!--REP_HEAD-->', '<!--REP_BODY-->'], [$px, $track], $out);
