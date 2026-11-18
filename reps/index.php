<?php
/*
 * Authorised representative sites — one template for every rep, its own design (not a copy of the main site).
 * Served from public_html/reps/ as the document root of each subdomain.
 *
 * Which rep: the subdomain's first label is matched against each entry in reps.json —
 *   "subdomains": ["care", …]   (any neutral code works: care.afnanfaeqasa.sa; the first one is the main address,
 *   the others — and the slug/code — 301 to it)
 *   falling back to "slug" and the lowercase "code".
 * Adding a rep = a subdomain whose document root is public_html/reps + one entry in reps.json.
 *
 * The site speaks only as «مركز أفنان الفائقة»: no person's name, no center number, no center tracking code.
 * Every WhatsApp / call link goes to the rep's number; only the rep's own pixels from reps.json load.
 */
declare(strict_types=1);

// local preview with `php -S`: let the built-in server hand out real files (css, images)
if (PHP_SAPI === 'cli-server' && is_file(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) { return false; }

const ROOT_DOMAIN = 'afnanfaeqasa.sa';
const BRAND = 'مركز أفنان الفائقة';

$reps = json_decode((string) file_get_contents(__DIR__ . '/reps.json'), true)['reps'] ?? [];
$data = json_decode((string) file_get_contents(__DIR__ . '/data.json'), true);

/* ---------- which rep? ---------- */
$host = strtolower(preg_replace('/:\d+$/', '', $_SERVER['HTTP_HOST'] ?? ''));
$label = '';
$local = in_array($host, ['localhost', '127.0.0.1'], true);
if (str_ends_with($host, '.' . ROOT_DOMAIN)) {
    $parts = explode('.', substr($host, 0, -strlen('.' . ROOT_DOMAIN)));
    $label = end($parts);                      // www.care1 → care1
} elseif ($local) {
    $label = strtolower((string) ($_GET['rep'] ?? ''));   // local preview only: ?rep=care1
}
$rep = null;
foreach ($reps as $r) {
    $names = array_map('strtolower', array_merge($r['subdomains'] ?? [], [$r['slug'] ?? '', $r['code'] ?? '']));
    if ($label !== '' && in_array($label, $names, true)) { $rep = $r; break; }
}
// the first entry in "subdomains" is the rep's address; any other name (old subdomain, slug, code) redirects to it
if ($rep && !$local && !empty($rep['subdomains'][0]) && $label !== strtolower($rep['subdomains'][0])) {
    header('Location: https://' . strtolower($rep['subdomains'][0]) . '.' . ROOT_DOMAIN . ($_SERVER['REQUEST_URI'] ?? '/'), true, 301);
    exit;
}

header('X-Robots-Tag: noindex, nofollow');
header('Content-Type: text/html; charset=utf-8');
if (!$rep) { http_response_code(404); echo '<!doctype html><meta charset="utf-8"><meta name="robots" content="noindex"><title>غير موجود</title><p style="font-family:sans-serif;text-align:center;margin-top:20vh">الصفحة غير موجودة</p>'; exit; }

/* ---------- routes ---------- */
$path = '/' . trim((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');
$sections = $data['sections'];
$bySlug = [];
foreach ($sections as $s) { $bySlug['/services/' . $s['slug']] = $s; }
$pages = ['/' => 'home', '/services' => 'services', '/about' => 'about', '/faq' => 'faq', '/contact' => 'contact'];
$page = $pages[$path] ?? (isset($bySlug[$path]) ? 'section' : null);
if ($page === null) { http_response_code(404); $page = 'notfound'; }
$sec = $page === 'section' ? $bySlug[$path] : null;

/* ---------- helpers ---------- */
function e($s): string { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }
$q = $local ? '?rep=' . rawurlencode($label) : '';          // keep the rep on local preview links
$u = fn(string $p): string => $p . $q;
$waText = 'السلام عليكم، أبي أستفسر عن خدمات الرعاية المنزلية (' . $rep['code'] . ')';
$wa = 'https://wa.me/' . $rep['phone'] . '?text=' . rawurlencode($waText);
$tel = 'tel:+' . $rep['phone'];
$shown = '0' . substr((string) $rep['phone'], 3, 2) . ' ' . substr((string) $rep['phone'], 5, 3) . ' ' . substr((string) $rep['phone'], 8);
$staff = (int) $data['staff'];
$v = e($data['v']);

$I = [
  'wa'    => '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 2.2a9.7 9.7 0 0 0-8.3 14.8L2.4 21.8l4.9-1.3A9.7 9.7 0 1 0 12 2.2Zm4.4 11.7c-.2-.1-1.4-.7-1.7-.8-.2-.1-.4-.1-.5.1l-.8 1c-.1.2-.3.2-.5.1a6.6 6.6 0 0 1-3.3-2.9c-.2-.4.2-.4.7-1.3.1-.2 0-.3 0-.4l-.8-1.8c-.2-.5-.4-.4-.5-.4h-.5a.9.9 0 0 0-.7.3 2.8 2.8 0 0 0-.9 2.1 4.9 4.9 0 0 0 1 2.6 11.2 11.2 0 0 0 4.3 3.8c1.6.7 2.2.7 3 .6a2.6 2.6 0 0 0 1.7-1.2 2.1 2.1 0 0 0 .2-1.2c-.1-.1-.3-.2-.5-.3Z"/></svg>',
  'tel'   => '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.4 1.8.7 2.7a2 2 0 0 1-.5 2.1L8 9.8a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.7.7a2 2 0 0 1 1.7 2Z"/></svg>',
  'arrow' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 6l-6 6 6 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
  'menu'  => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h10" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>',
  'close' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>',
  'check' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12.5 4.5 4.5L19 7.5" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
  'shield'=> '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3l7 3v5c0 4.5-3 8.2-7 10-4-1.8-7-5.5-7-10V6l7-3z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="m9 12 2 2 4-4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>',
  'team'  => '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="8" r="3" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="M3.5 19a5.5 5.5 0 0 1 11 0M16 5.5a3 3 0 0 1 0 5.6M17.5 14.5A5 5 0 0 1 20.5 19" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>',
  'clock' => '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="8.5" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="M12 7.5V12l3 2" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>',
  'pin'   => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21s-6.5-5.6-6.5-11a6.5 6.5 0 0 1 13 0c0 5.4-6.5 11-6.5 11z" fill="none" stroke="currentColor" stroke-width="1.8"/><circle cx="12" cy="10" r="2.3" fill="none" stroke="currentColor" stroke-width="1.8"/></svg>',
  'cal'   => '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="5" width="16" height="15" rx="3" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="M4 10h16M9 3v4M15 3v4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>',
];

/* ---------- Saudi identity (no flag, emblem, leaders' images/names or official logos) ---------- */
$tuwaiq = '<svg class="tuwaiq-art" viewBox="0 0 640 360" role="img" aria-label="رسم لجروف جبل طويق عند الغروب" preserveAspectRatio="xMidYMid slice"><defs><linearGradient id="tqs" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#f6e2b3"/><stop offset=".55" stop-color="#e9b979"/><stop offset="1" stop-color="#d8955a"/></linearGradient></defs><rect width="640" height="360" fill="url(#tqs)"/><circle cx="470" cy="150" r="46" fill="#fff4d6" opacity=".9"/><path d="M0 210 L60 196 L95 204 L150 182 L210 190 L260 168 L330 176 L380 160 L450 172 L520 150 L580 162 L640 148 V360 H0Z" fill="#c78a55"/><path d="M0 246 L40 236 L70 214 L120 220 L170 200 L230 208 L270 192 L340 202 L400 186 L470 198 L530 180 L600 192 L640 184 V360 H0Z" fill="#a8683d"/><path d="M0 252 L70 246 L110 238 L112 232 L170 226 L230 230 L232 222 L300 218 L360 224 L362 214 L440 210 L520 216 L522 206 L600 204 L640 208 V360 H0Z" fill="#7d4a2b"/><path d="M0 300 L120 292 L240 298 L380 288 L520 296 L640 290 V360 H0Z" fill="#5c341f"/><g fill="#0b3a27" opacity=".85"><path d="M64 300c0-14 2-30 3-40m0 0c-8-6-18-6-24-2m24 2c6-8 16-10 24-6m-24 6c-4-10-2-18 4-24m-4 24c10-4 18-2 22 4" stroke="#0b3a27" stroke-width="3" fill="none" stroke-linecap="round"/><path d="M578 296c0-12 2-26 3-36m0 0c-7-5-16-5-21-2m21 2c5-7 14-9 21-5m-21 5c-3-9-1-16 4-21" stroke="#0b3a27" stroke-width="3" fill="none" stroke-linecap="round"/></g></svg>';
$skyline = '<svg class="skyline" viewBox="0 0 1200 120" aria-hidden="true" preserveAspectRatio="xMidYMax slice"><g fill="currentColor"><rect x="0" y="96" width="1200" height="24"/><rect x="40" y="70" width="38" height="50"/><rect x="88" y="58" width="26" height="62"/><rect x="124" y="78" width="44" height="42"/><path d="M190 120V62h14v-8h10v8h14v58z"/><rect x="250" y="80" width="40" height="40"/><path d="M330 120 L352 14 L374 120 Z"/><circle cx="352" cy="58" r="11" fill="var(--green)"/><circle cx="352" cy="58" r="8"/><rect x="392" y="74" width="30" height="46"/><rect x="430" y="88" width="56" height="32"/><path d="M520 120 V22 Q556 70 592 22 V120 Z"/><path d="M533 120 V46 Q556 84 579 46 V120 Z" fill="var(--green)"/><rect x="612" y="66" width="28" height="54"/><rect x="648" y="82" width="46" height="38"/><rect x="704" y="54" width="24" height="66"/><path d="M748 120V70l20-14 20 14v50z"/><rect x="806" y="84" width="40" height="36"/><rect x="856" y="62" width="30" height="58"/><rect x="896" y="76" width="50" height="44"/><rect x="956" y="90" width="38" height="30"/><rect x="1004" y="68" width="28" height="52"/><rect x="1042" y="80" width="46" height="40"/><rect x="1098" y="72" width="30" height="48"/><rect x="1138" y="86" width="44" height="34"/></g><g stroke="currentColor" stroke-width="3" fill="none" stroke-linecap="round"><path d="M462 96c0-12 1-22 2-30m0 0c-6-5-13-5-18-2m18 2c4-6 11-8 17-4m-17 4c-3-7-1-13 3-17"/><path d="M972 96c0-12 1-22 2-30m0 0c-6-5-13-5-18-2m18 2c4-6 11-8 17-4m-17 4c-3-7-1-13 3-17"/></g></svg>';

/* ---------- page titles ---------- */
$titles = ['home' => 'رعاية طبية منزلية باشتراك', 'services' => 'خدماتنا', 'about' => 'من نحن', 'faq' => 'الأسئلة الشائعة', 'contact' => 'تواصل معنا', 'notfound' => 'الصفحة غير موجودة'];
$pageTitle = $sec ? $sec['name'] : $titles[$page];
$docTitle = $page === 'home' ? BRAND . ' | ' . $pageTitle : $pageTitle . ' | ' . BRAND;

/* ---------- pixels: only the rep's own, from reps.json ---------- */
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
// every WhatsApp / call click → Contact (only fires on pixels that loaded above)
$track = "<script>document.addEventListener('click',function(ev){var a=ev.target.closest&&ev.target.closest('a[href]');if(!a)return;var h=a.getAttribute('href')||'';var k=h.indexOf('wa.me')>-1?'whatsapp':(h.indexOf('tel:')===0?'call':'');if(!k)return;"
       . "try{if(window.fbq){fbq('track','Contact',{method:k});fbq('track','Lead',{method:k});}}catch(_){}"
       . "try{if(window.ttq){ttq.track('Contact',{method:k});}}catch(_){}"
       . "try{if(window.snaptr){snaptr('track','CUSTOM_EVENT_1',{description:'Contact '+k});}}catch(_){}"
       . "try{if(window.gtag){gtag('event','contact',{method:k});}}catch(_){}"
       . "},true);</script>";
if ($px === '') { $track = ''; }   // no pixels set → no tracking script at all
/* ---------- end pixels ---------- */

/* ---------- shared pieces ---------- */
$nav = [['/', 'الرئيسية'], ['/services', 'خدماتنا'], ['/about', 'من نحن'], ['/faq', 'الأسئلة الشائعة'], ['/contact', 'تواصل معنا']];
$cur = $sec ? '/services' : $path;
$btnWa = fn(string $txt = 'راسلنا على واتساب', string $cls = 'btn btn-wa'): string => '<a class="' . $cls . '" href="' . e($wa) . '" target="_blank" rel="noopener">' . $I['wa'] . '<span>' . e($txt) . '</span></a>';
$btnTel = fn(string $cls = 'btn btn-line'): string => '<a class="' . $cls . '" href="' . e($tel) . '">' . $I['tel'] . '<span>اتصل بنا <bdi dir="ltr">' . e($shown) . '</bdi></span></a>';
$faqList = function (array $items) use ($I): string {
    $h = '';
    foreach ($items as $i => $f) {
        $h .= '<details class="qa"' . ($i === 0 ? ' open' : '') . '><summary>' . e($f['q']) . '<i aria-hidden="true"></i></summary><p>' . e($f['a']) . '</p></details>';
    }
    return '<div class="qas">' . $h . '</div>';
};
$cta = '<section class="cta"><div class="wrap cta-in"><div><h2>نرتّب لك الرعاية المناسبة</h2><p>كلّمنا عن الحالة، ويرشّح لك الفريق الطبي الخدمة ومدة الاشتراك المناسبة.</p></div><div class="cta-btns">' . $btnWa('ابدأ على واتساب', 'btn btn-gold') . $btnTel('btn btn-ghost') . '</div></div></section>';
$crumbs = function (array $trail) use ($u): string {
    $h = '<nav class="crumbs" aria-label="مسار الصفحة"><a href="' . e($u('/')) . '">الرئيسية</a>';
    foreach ($trail as [$href, $t]) { $h .= '<span aria-hidden="true">/</span>' . ($href ? '<a href="' . e($u($href)) . '">' . e($t) . '</a>' : '<span>' . e($t) . '</span>'); }
    return $h . '</nav>';
};
$pageHead = fn(string $title, string $lead, array $trail): string => '<header class="phead"><div class="wrap">' . $crumbs($trail) . '<h1>' . e($title) . '</h1><p>' . e($lead) . '</p></div></header>';
$secCards = function () use ($sections, $u, $I): string {
    $h = '<div class="cards">';
    foreach ($sections as $s) {
        $h .= '<a class="card" href="' . e($u('/services/' . $s['slug'])) . '"><img src="' . e($s['image']) . '" alt="" width="640" height="420" loading="lazy">'
            . '<span class="card-b"><b>' . e($s['name']) . '</b><small>' . e($s['short']) . '</small><i>' . $I['arrow'] . '</i></span></a>';
    }
    return $h . '</div>';
};

/* ---------- per-rep theme: a completely different design for one rep ("theme" in reps.json → reps/themes/<theme>.php) ---------- */
$theme = preg_replace('/[^a-z0-9_-]/', '', strtolower((string) ($rep['theme'] ?? '')));
if ($theme !== '' && is_file(__DIR__ . '/themes/' . $theme . '.php')) { require __DIR__ . '/themes/' . $theme . '.php'; exit; }

ob_start();
?><!doctype html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="robots" content="noindex, nofollow">
<title><?= e($docTitle) ?></title>
<meta name="description" content="<?= e(BRAND) ?> للخدمات الطبية المنزلية: تمريض ورعاية كبار السن وطب منزلي وعلاج طبيعي وتحاليل، باشتراك من شهر حتى سنة، في جميع مناطق المملكة.">
<meta name="theme-color" content="#0b3a27">
<link rel="icon" href="/img/mark.png">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Alexandria:wght@500;600;700&family=IBM+Plex+Sans+Arabic:wght@400;500;600&display=swap">
<link rel="stylesheet" href="/rep.css?v=<?= $v ?>">
<?= $px ?>
</head>
<body class="p-<?= e($page) ?>">
<header class="top"><div class="wrap top-in">
  <a class="brand" href="<?= e($u('/')) ?>"><img src="/img/mark.png" alt="" width="44" height="44"><span><b><?= e(BRAND) ?></b><small>للخدمات الطبية المنزلية</small></span></a>
  <nav class="nav" aria-label="القائمة الرئيسية"><?php foreach ($nav as [$h, $t]): ?><a href="<?= e($u($h)) ?>"<?= $cur === $h ? ' aria-current="page"' : '' ?>><?= e($t) ?></a><?php endforeach; ?></nav>
  <?= $btnWa('واتساب', 'btn btn-wa btn-sm top-wa') ?>
  <button class="menu-btn" type="button" aria-expanded="false" aria-controls="drawer" aria-label="القائمة"><?= $I['menu'] ?></button>
</div>
<div class="drawer" id="drawer" hidden><div class="wrap">
  <?php foreach ($nav as [$h, $t]): ?><a href="<?= e($u($h)) ?>"<?= $cur === $h ? ' aria-current="page"' : '' ?>><?= e($t) ?><?= $I['arrow'] ?></a><?php endforeach; ?>
</div></div></header>

<main>
<?php if ($page === 'home'): ?>
<section class="hero"><div class="wrap hero-in">
  <div class="hero-txt">
    <p class="kicker"><span></span>رعاية طبية منزلية باشتراك</p>
    <h1>الرعاية التي يحتاجها أهلك، <em>في بيتهم</em></h1>
    <p class="lead">ممرضون وممرضات وكوادر صحية مرخّصة تتابع الحالة في البيت، باشتراك من شهر حتى سنة، في جميع مناطق المملكة.</p>
    <div class="hero-btns"><?= $btnWa('اطلب الرعاية على واتساب') ?><?= $btnTel() ?></div>
  </div>
  <figure class="hero-img"><img src="/img/elderly-walk.jpg" alt="ممرضة ترافق سيدة مسنّة في منزلها" width="900" height="900" fetchpriority="high">
    <figcaption><b><?= $staff ?>+</b><span>كادر صحي مرخّص</span></figcaption></figure>
</div></section>

<section class="facts"><div class="wrap facts-in">
  <div><?= $I['shield'] ?><b>مركز مرخّص</b><span>كوادر صحية معتمدة</span></div>
  <div><?= $I['cal'] ?><b>من شهر حتى سنة</b><span>اشتراك بخطة واضحة</span></div>
  <div><?= $I['clock'] ?><b>على مدار الساعة</b><span>طوال أيام الأسبوع</span></div>
  <div><?= $I['pin'] ?><b>جميع مناطق المملكة</b><span>نصل إليكم أينما كنتم</span></div>
</div></section>

<section class="sec"><div class="wrap">
  <div class="sec-h"><div><p class="eyebrow">خدماتنا</p><h2>ستة أقسام لرعاية متكاملة</h2></div><a class="more" href="<?= e($u('/services')) ?>">كل الخدمات<?= $I['arrow'] ?></a></div>
  <?= $secCards() ?>
</div></section>

<section class="sec sec-tint"><div class="wrap split">
  <figure class="split-img"><img src="/img/team.jpg" alt="فريق من الممرضين والممرضات" width="900" height="700" loading="lazy"></figure>
  <div>
    <p class="eyebrow">كيف نبدأ</p><h2>ثلاث خطوات حتى تبدأ الرعاية</h2>
    <ol class="steps">
      <li><b>تواصل معنا</b><span>على واتساب أو بالاتصال، وأخبرنا عن الحالة.</span></li>
      <li><b>نقيّم الحالة</b><span>الفريق الطبي يحدد الخدمة ومدة الاشتراك المناسبة.</span></li>
      <li><b>تبدأ الرعاية في البيت</b><span>يصل الكادر في الموعد، ونتابع معك طوال الاشتراك.</span></li>
    </ol>
  </div>
</div></section>

<section class="sec hemma"><div class="wrap hemma-in">
  <figure class="hemma-art"><?= $tuwaiq ?></figure>
  <div>
    <p class="eyebrow">قصة الاسم</p><h2>همّة مثل <em>طويق</em></h2>
    <p>من همّة السعوديين التي تشبه جبل طويق في ثباتها استلهمنا اسمنا، ونحمل هذه الهمّة إلى كل بيت نخدمه: رعاية ثابتة، ومتابعة لا تنقطع، وكادر يعرف أهلك ويعرفونه.</p>
    <p class="vision-line"><span aria-hidden="true">2030</span>نساهم في تحقيق مستهدفات رؤية المملكة 2030 في القطاع الصحي، بنقل الرعاية إلى بيت المريض.</p>
  </div>
</div></section>

<section class="sec"><div class="wrap">
  <div class="sub">
    <div class="sub-txt"><p class="eyebrow">الاشتراك</p><h2>خطة رعاية، لا زيارة عابرة</h2>
      <p>خدماتنا باشتراك فقط، من شهر واحد حتى سنة كاملة، حتى يكون للحالة فريق ثابت يعرفها وخطة متابعة واضحة.</p></div>
    <ul class="ticks">
      <li><?= $I['check'] ?>ممرض أو ممرضة مقيمة حسب الحالة</li>
      <li><?= $I['check'] ?>تختار ممرضًا أو ممرضة حسب تفضيلك</li>
      <li><?= $I['check'] ?>الدفع خلال 24 ساعة بعد تقديم الخدمة</li>
      <li><?= $I['check'] ?>مدى، فيزا، Apple Pay، STC Pay، تابي، تمارا</li>
    </ul>
  </div>
</div></section>

<section class="sec sec-tint"><div class="wrap narrow">
  <div class="sec-h"><div><p class="eyebrow">أسئلة شائعة</p><h2>قبل أن تسأل</h2></div><a class="more" href="<?= e($u('/faq')) ?>">كل الأسئلة<?= $I['arrow'] ?></a></div>
  <?= $faqList(array_slice($data['faq'], 0, 3)) ?>
</div></section>
<?= $cta ?>

<?php elseif ($page === 'services'): ?>
<?= $pageHead('خدماتنا', 'رعاية طبية منزلية في ستة أقسام، كلها باشتراك من شهر حتى سنة.', [[null, 'خدماتنا']]) ?>
<section class="sec"><div class="wrap">
  <?= $secCards() ?>
  <?php foreach ($sections as $s): ?>
  <div class="svc-group" id="<?= e($s['slug']) ?>">
    <div class="sec-h"><h2><?= e($s['name']) ?></h2><a class="more" href="<?= e($u('/services/' . $s['slug'])) ?>">التفاصيل<?= $I['arrow'] ?></a></div>
    <ul class="svc-list"><?php foreach ($s['items'] as $it): ?><li><b><?= e($it['title']) ?></b><span><?= e($it['description']) ?></span></li><?php endforeach; ?></ul>
  </div>
  <?php endforeach; ?>
</div></section>
<?= $cta ?>

<?php elseif ($page === 'section'): ?>
<header class="shead"><div class="wrap shead-in">
  <div><?= $crumbs([['/services', 'خدماتنا'], [null, $sec['name']]]) ?><h1><?= e($sec['name']) ?></h1><p><?= e($sec['short']) ?>، باشتراك من شهر حتى سنة، في جميع مناطق المملكة.</p>
  <div class="hero-btns"><?= $btnWa('اسأل عن ' . $sec['name']) ?><?= $btnTel() ?></div></div>
  <figure><img src="<?= e($sec['image']) ?>" alt="" width="900" height="640"></figure>
</div></header>
<section class="sec"><div class="wrap">
  <div class="svc-detail">
  <?php foreach ($sec['items'] as $it): ?>
    <article class="svc">
      <h2><?= e($it['title']) ?></h2>
      <p><?= e($it['description']) ?></p>
      <?php if ($it['includes']): ?><h3>تشمل</h3><ul class="ticks sm"><?php foreach ($it['includes'] as $x): ?><li><?= $I['check'] ?><?= e($x) ?></li><?php endforeach; ?></ul><?php endif; ?>
      <?php if ($it['suitable_for']): ?><h3>مناسبة لـ</h3><p class="for"><?= e(implode('، ', $it['suitable_for'])) ?></p><?php endif; ?>
    </article>
  <?php endforeach; ?>
  </div>
  <h2 class="also">أقسام أخرى</h2>
  <div class="chips"><?php foreach ($sections as $s): if ($s['slug'] === $sec['slug']) continue; ?><a href="<?= e($u('/services/' . $s['slug'])) ?>"><?= e($s['name']) ?></a><?php endforeach; ?></div>
</div></section>
<?= $cta ?>

<?php elseif ($page === 'about'): ?>
<?= $pageHead('من نحن', 'مقدّم خاص لخدمات الرعاية الصحية المنزلية في المملكة العربية السعودية، ومقرّه مدينة الرياض.', [[null, 'من نحن']]) ?>
<section class="sec"><div class="wrap split">
  <figure class="split-img"><img src="/img/team.jpg" alt="فريق من الممرضين والممرضات" width="900" height="700" loading="lazy"></figure>
  <div class="prose">
    <h2>نبذة عن المركز</h2>
    <p><?= e(BRAND) ?> للخدمات الطبية المنزلية يقدّم رعاية صحية في البيت: تمريض، ورعاية كبار السن، وطب منزلي، وعلاج طبيعي، وتحاليل وأشعة، ورعاية الأم والمولود.</p>
    <h2>الكادر والتغطية</h2>
    <p>يضم المركز أكثر من <?= $staff ?> من الكوادر الصحية المرخّصة، ويقدّم خدماته في مختلف مناطق المملكة على مدار الساعة.</p>
    <h2>طريقة العمل</h2>
    <p>نعمل بنظام الاشتراك فقط، من شهر واحد حتى سنة كاملة، بخطة رعاية واضحة وكادر ثابت يتابع الحالة. ولا نقدّم زيارات منفردة.</p>
  </div>
</div></section>
<section class="sec sec-tint"><div class="wrap">
  <div class="facts-in facts-card">
    <div><?= $I['team'] ?><b><?= $staff ?>+</b><span>كادر صحي مرخّص</span></div>
    <div><?= $I['clock'] ?><b>24/7</b><span>على مدار الساعة</span></div>
    <div><?= $I['pin'] ?><b>المملكة</b><span>جميع المناطق</span></div>
    <div><?= $I['cal'] ?><b>1–12 شهرًا</b><span>مدة الاشتراك</span></div>
  </div>
  <div class="vision"><img src="/img/vision-2030.png" alt="شعار رؤية المملكة 2030" width="560" height="375" loading="lazy"><p>نعمل بما يتماشى مع مستهدفات رؤية المملكة 2030 في جودة الحياة والرعاية الصحية.</p></div>
</div></section>
<?= $cta ?>

<?php elseif ($page === 'faq'): ?>
<?= $pageHead('الأسئلة الشائعة', 'إجابات مختصرة عن الحجز والاشتراك والتغطية والدفع.', [[null, 'الأسئلة الشائعة']]) ?>
<section class="sec"><div class="wrap narrow"><?= $faqList($data['faq']) ?></div></section>
<?= $cta ?>

<?php elseif ($page === 'contact'): ?>
<?= $pageHead('تواصل معنا', 'نرد عليك على واتساب أو بالاتصال، على مدار الساعة.', [[null, 'تواصل معنا']]) ?>
<section class="sec"><div class="wrap">
  <div class="contact">
    <a class="cbox cbox-wa" href="<?= e($wa) ?>" target="_blank" rel="noopener"><?= $I['wa'] ?><b>واتساب</b><span>اكتب لنا عن الحالة ونرد عليك</span><bdi dir="ltr"><?= e($shown) ?></bdi></a>
    <a class="cbox" href="<?= e($tel) ?>"><?= $I['tel'] ?><b>اتصال</b><span>كلّمنا مباشرة</span><bdi dir="ltr"><?= e($shown) ?></bdi></a>
  </div>
  <ul class="cinfo">
    <li><?= $I['clock'] ?><span><b>ساعات العمل</b>على مدار الساعة، طوال أيام الأسبوع</span></li>
    <li><?= $I['pin'] ?><span><b>التغطية</b>جميع مناطق المملكة العربية السعودية</span></li>
    <li><?= $I['cal'] ?><span><b>الاشتراك</b>من شهر واحد حتى سنة كاملة</span></li>
  </ul>
</div></section>

<?php else: ?>
<?= $pageHead('الصفحة غير موجودة', 'الرابط غير صحيح أو تغيّر.', [[null, 'غير موجودة']]) ?>
<section class="sec"><div class="wrap"><a class="btn btn-wa" href="<?= e($u('/')) ?>">العودة للرئيسية</a></div></section>
<?php endif; ?>
</main>

<div class="sadu" aria-hidden="true"></div>
<footer class="foot"><?= $skyline ?><div class="wrap">
  <div class="foot-top">
    <div class="foot-brand"><img src="/img/logo.png" alt="<?= e(BRAND) ?>" width="120" height="123" loading="lazy"><p>رعاية صحية منزلية بكوادر مرخّصة، في جميع مناطق المملكة العربية السعودية.</p></div>
    <nav aria-label="روابط"><h3>الموقع</h3><?php foreach ($nav as [$h, $t]): ?><a href="<?= e($u($h)) ?>"><?= e($t) ?></a><?php endforeach; ?></nav>
    <nav aria-label="الخدمات"><h3>الخدمات</h3><?php foreach ($sections as $s): ?><a href="<?= e($u('/services/' . $s['slug'])) ?>"><?= e($s['name']) ?></a><?php endforeach; ?></nav>
    <div><h3>تواصل</h3><a href="<?= e($wa) ?>" target="_blank" rel="noopener">واتساب</a><a href="<?= e($tel) ?>"><bdi dir="ltr"><?= e($shown) ?></bdi></a><span>على مدار الساعة</span></div>
  </div>
  <ul class="pay" aria-label="طرق الدفع"><?php foreach (['mada' => 'مدى', 'visa' => 'Visa', 'applepay' => 'Apple Pay', 'stcpay' => 'STC Pay', 'tabby' => 'تابي', 'tamara' => 'تمارا'] as $f => $alt): ?><li><img src="/img/pay/<?= $f ?>.png" alt="<?= e($alt) ?>" height="20" loading="lazy"></li><?php endforeach; ?></ul>
  <p class="copy">© <?= date('Y') ?> <?= e(BRAND) ?> للخدمات الطبية المنزلية</p>
</div></footer>

<div class="dock"><?= $btnWa('واتساب', 'btn btn-wa') ?><?= $btnTel('btn btn-line') ?></div>

<script>
(function(){var b=document.querySelector('.menu-btn'),d=document.getElementById('drawer');if(!b||!d)return;
b.addEventListener('click',function(){var o=d.hidden;d.hidden=!o;b.setAttribute('aria-expanded',String(o));b.innerHTML=o?<?= json_encode($I['close']) ?>:<?= json_encode($I['menu']) ?>;});})();
</script>
<?= $track ?>
</body>
</html>
<?php
echo ob_get_clean();
