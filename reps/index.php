<?php
/*
 * Authorised representative sites — one template for every rep.
 * Served from public_html/reps/ as the document root of each subdomain (mahmoud.afnanfaeqasa.sa …).
 * The subdomain's first label is the rep's slug in reps.json; adding a rep = a subdomain + one line there.
 * Nothing on these pages carries a center number or a link to the main site.
 */
declare(strict_types=1);

// local preview with `php -S`: let the built-in server hand out real files (css, webp)
if (PHP_SAPI === 'cli-server' && is_file(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) { return false; }

const ROOT_DOMAIN = 'afnanfaeqasa.sa';
const COOKIE = 'afn_rep';
const COOKIE_DAYS = 90;

$reps = json_decode((string) file_get_contents(__DIR__ . '/reps.json'), true)['reps'] ?? [];
$data = json_decode((string) file_get_contents(__DIR__ . '/data.json'), true);

/* ---------- which rep? ---------- */
$host = strtolower(preg_replace('/:\d+$/', '', $_SERVER['HTTP_HOST'] ?? ''));
$slug = '';
if (str_ends_with($host, '.' . ROOT_DOMAIN)) {
    $label = substr($host, 0, -strlen('.' . ROOT_DOMAIN));
    $parts = explode('.', $label);
    $slug = end($parts);                       // www.mahmoud → mahmoud
} elseif (in_array($host, ['localhost', '127.0.0.1'], true)) {
    $slug = (string) ($_GET['rep'] ?? '');     // local preview only
}
$rep = null;
foreach ($reps as $r) { if (($r['slug'] ?? '') === $slug) { $rep = $r; break; } }

header('X-Robots-Tag: noindex');
header('Content-Type: text/html; charset=utf-8');
if (!$rep) { http_response_code(404); echo '<!doctype html><meta charset="utf-8"><meta name="robots" content="noindex"><title>غير موجود</title><p style="font-family:sans-serif;text-align:center;margin-top:20vh">الصفحة غير موجودة</p>'; exit; }

/* ---------- remember the rep across *.afnanfaeqasa.sa (first rep wins) ---------- */
if (empty($_COOKIE[COOKIE]) && str_ends_with($host, ROOT_DOMAIN)) {
    setcookie(COOKIE, $rep['code'], ['expires' => time() + COOKIE_DAYS * 86400, 'path' => '/', 'domain' => '.' . ROOT_DOMAIN,
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off', 'samesite' => 'Lax']);
}

/* ---------- routes ---------- */
$path = '/' . trim((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');
$sections = $data['sections'];
$bySlug = [];
foreach ($sections as $s) { $bySlug['/' . $s['slug']] = $s; }
$pages = ['/' => 'home', '/services' => 'services', '/about' => 'about', '/me' => 'me', '/faq' => 'faq', '/contact' => 'contact'];
$page = $pages[$path] ?? (isset($bySlug[$path]) ? 'section' : null);
if ($page === null) { http_response_code(404); $page = 'notfound'; }

/* ---------- helpers ---------- */
function e($s): string { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }
$first = explode(' ', $rep['name'])[0];
$waText = 'السلام عليكم، تواصلت معكم من موقع ' . $first . ' (' . $rep['code'] . ')';
$wa = 'https://wa.me/' . $rep['phone'] . '?text=' . rawurlencode($waText);
$tel = 'tel:+' . $rep['phone'];
$shown = $rep['display_phone'];
$base = in_array($host, ['localhost', '127.0.0.1'], true) ? '?rep=' . rawurlencode($slug) : '';
$u = function (string $p) use ($base): string {
    if ($base === '') return $p;
    return $p === '/' ? '/' . $base : $p . $base;
};

$ICON = [
  'wa'    => '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 2.2a9.7 9.7 0 0 0-8.3 14.8L2.4 21.8l4.9-1.3A9.7 9.7 0 1 0 12 2.2Zm4.4 11.7c-.2-.1-1.4-.7-1.7-.8-.2-.1-.4-.1-.5.1l-.8 1c-.1.2-.3.2-.5.1a6.6 6.6 0 0 1-3.3-2.9c-.2-.4.2-.4.7-1.3.1-.2 0-.3 0-.4l-.8-1.8c-.2-.5-.4-.4-.5-.4h-.5a.9.9 0 0 0-.7.3 2.8 2.8 0 0 0-.9 2.1 4.9 4.9 0 0 0 1 2.6 11.2 11.2 0 0 0 4.3 3.8c1.6.7 2.2.7 3 .6a2.6 2.6 0 0 0 1.7-1.2 2.1 2.1 0 0 0 .2-1.2c-.1-.1-.3-.2-.5-.3Z"/></svg>',
  'tel'   => '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.4 1.8.7 2.7a2 2 0 0 1-.5 2.1L8 9.8a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.7.7a2 2 0 0 1 1.7 2Z"/></svg>',
  'shield'=> '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3l7 3v5c0 4.5-3 8.2-7 10-4-1.8-7-5.5-7-10V6l7-3z" fill="none" stroke="currentColor" stroke-width="1.9"/><path d="m9 12 2 2 4-4" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"/></svg>',
  'arrow' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 6l-6 6 6 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
  'menu'  => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h10" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>',
  'close' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>',
];
$SEC_ICON = [
  'home-doctor'   => '<path d="M9 4v6a4 4 0 0 0 8 0V4M7 4h4M15 4h4"/><path d="M13 14v2a4 4 0 0 1-8 0v-1"/><circle cx="5" cy="13" r="2"/>',
  'nursing'       => '<path d="M4 20V10l8-6 8 6v10"/><path d="M12 17.5c-2.4-1.7-4-3-4-4.6a2 2 0 0 1 4-.8 2 2 0 0 1 4 .8c0 1.6-1.6 2.9-4 4.6z"/>',
  'elderly-care'  => '<circle cx="10" cy="5" r="2.2"/><path d="M10 7.5v6l-3 7M10 13.5l3 3v4M10 10l-3 2M17 12v9M17 12a2 2 0 0 0-2-2"/>',
  'mother-baby'   => '<circle cx="9" cy="6" r="2.6"/><path d="M4 21v-3.5a5 5 0 0 1 10 0"/><circle cx="17" cy="13" r="2.2"/><path d="M14.5 21a2.5 2.5 0 0 1 5 0"/>',
  'physiotherapy' => '<circle cx="14" cy="4.5" r="2.2"/><path d="M6 11l4.5-2.3 4 1.5 3 4.5M10.5 8.7 9 14.6l4.5 2.3V21M9 14.6 5.5 21"/>',
  'lab-radiology' => '<path d="M9 3h6M10 3v6l-5 9a2 2 0 0 0 1.8 3h10.4A2 2 0 0 0 19 18l-5-9V3"/><path d="M7.5 14h9"/>',
];
$secIcon = fn(string $k): string => '<svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">' . ($SEC_ICON[$k] ?? '') . '</svg>';

/* ---------- page titles ---------- */
$titles = ['home' => 'الرئيسية', 'services' => 'خدماتنا', 'about' => 'من نحن', 'me' => 'من أنا', 'faq' => 'أسئلة شائعة', 'contact' => 'تواصل معي', 'notfound' => 'الصفحة غير موجودة'];
$pageTitle = $page === 'section' ? $bySlug[$path]['name'] : $titles[$page];
$docTitle = $pageTitle . ' · ' . $rep['name'] . ' | ممثل معتمد لمركز أفنان الفائقة';

/* ---------- pixels (only those with a value) ---------- */
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

/* ---------- shared pieces ---------- */
$nav = [['/', 'الرئيسية'], ['/services', 'خدماتنا'], ['/about', 'من نحن'], ['/me', 'من أنا'], ['/faq', 'أسئلة شائعة'], ['/contact', 'تواصل']];
$avatar = function (string $cls) use ($rep): string {
    if (!empty($rep['photo'])) return '<img class="' . $cls . '" src="' . e($rep['photo']) . '" alt="' . e($rep['name']) . '" width="240" height="240">';
    return '<span class="' . $cls . ' ini" aria-hidden="true">' . e($rep['initial']) . '</span>';
};
$ctaPair = '<div class="cta-pair"><a class="b b-wa" data-contact="whatsapp" href="' . e($wa) . '" target="_blank" rel="noopener">' . $ICON['wa'] . 'تواصل معي على واتساب</a>'
         . '<a class="b b-tel" data-contact="call" href="' . e($tel) . '">' . $ICON['tel'] . 'اتصل بي <bdi class="n">' . e($shown) . '</bdi></a></div>';
$faqs = [
  ['مدة الاشتراك؟', 'من شهر لين سنة حسب الحالة، وما فيه زيارات منفردة.'],
  ['أقدر أختار ممرض أو ممرضة؟', 'نعم.'],
  ['وين تخدمون؟', 'جميع مناطق المملكة، على مدار ٢٤ ساعة.'],
  ['طرق الدفع؟', 'خلال ٢٤ ساعة من تقديم الخدمة: مدى، فيزا، Apple Pay، STC Pay، تابي، تمارا.'],
];
$faqHtml = '';
foreach ($faqs as $i => [$q, $a]) {
    $faqHtml .= '<details class="qa"' . ($i === 0 ? ' open' : '') . '><summary>' . e($q) . '<i aria-hidden="true"></i></summary><p>' . e($a) . '</p></details>';
}
$staff = (int) $data['staff'];
$trust = '<p class="trust"><span>مركز مرخّص</span><span>أكثر من ' . $staff . ' كادرًا صحيًا</span><span>على مدار الساعة</span></p>';
$steps = '<ol class="steps"><li><b>تتواصل معي</b><span>وتعطيني تفاصيل الحالة</span></li><li><b>الفريق الطبي يقيّم الحالة</b><span>ويعرض عليك الاشتراك المناسب</span></li><li><b>الكادر يبدأ الرعاية</b><span>في بيتك</span></li></ol>';

ob_start();
?><!doctype html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="robots" content="noindex, follow">
<title><?= e($docTitle) ?></title>
<meta name="description" content="<?= e($rep['name']) ?>، ممثل معتمد لمركز أفنان الفائقة للخدمات الطبية المنزلية. اشتراكات رعاية منزلية من شهر حتى سنة، في جميع مناطق المملكة.">
<meta name="theme-color" content="#0b3a27">
<link rel="icon" href="/assets/mark.webp">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Alexandria:wght@600;700;800&family=IBM+Plex+Sans+Arabic:wght@400;500;600&display=swap">
<link rel="stylesheet" href="/assets/rep.css?v=<?= e($data['v']) ?>">
<?= $px ?>
</head>
<body class="p-<?= e($page) ?>">
<header class="hd">
  <div class="hd-in">
    <a class="hd-logo" href="<?= e($u('/')) ?>" aria-label="الرئيسية"><img src="/assets/logo.webp" alt="مركز أفنان الفائقة للخدمات الطبية" width="132" height="136"></a>
    <div class="hd-who">
      <b><?= e($rep['name']) ?></b>
      <span class="badge"><?= $ICON['shield'] ?>ممثل معتمد · مركز أفنان الفائقة</span>
    </div>
    <button class="hd-menu" type="button" aria-expanded="false" aria-controls="drawer" aria-label="القائمة"><?= $ICON['menu'] ?></button>
  </div>
  <nav class="drawer" id="drawer" aria-label="القائمة الرئيسية" hidden>
    <?php foreach ($nav as [$href, $label]): ?>
      <a href="<?= e($u($href)) ?>"<?= ($path === $href || ($href === '/services' && $page === 'section')) ? ' aria-current="page"' : '' ?>><?= e($label) ?></a>
    <?php endforeach; ?>
  </nav>
</header>

<main id="main">
<?php if ($page === 'home'): ?>
  <section class="card-hero">
    <div class="vcard">
      <div class="vc-top"><?= $avatar('av') ?><div><span class="kick">ممثلك المعتمد</span><h1><?= e($rep['name']) ?></h1></div></div>
      <p class="vc-bio"><?= e($rep['bio']) ?></p>
      <?= $ctaPair ?>
    </div>
    <p class="hero-line">رعاية طبية منزلية من <b>مركز أفنان الفائقة</b>، باشتراك من شهر حتى سنة، في جميع مناطق المملكة.</p>
  </section>

  <section class="blk">
    <div class="blk-h"><h2>الخدمات</h2><a class="more" href="<?= e($u('/services')) ?>">كل الخدمات</a></div>
    <div class="tiles">
      <?php foreach ($sections as $s): ?>
        <a class="tile" href="<?= e($u('/' . $s['slug'])) ?>"><span class="ti"><?= $secIcon($s['slug']) ?></span><b><?= e($s['name']) ?></b><small><?= count($s['items']) ?> <?= count($s['items']) > 2 ? 'خدمات' : (count($s['items']) === 2 ? 'خدمتان' : 'خدمة') ?></small></a>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="blk"><div class="blk-h"><h2>كيف تبدأ الخدمة؟</h2></div><?= $steps ?></section>
  <?= $trust ?>

<?php elseif ($page === 'services'): ?>
  <section class="pg-h"><h1>خدماتنا</h1><p>كل الخدمات باشتراك من شهر حتى سنة، ينفّذها كادر صحي مرخّص من مركز أفنان الفائقة.</p></section>
  <div class="rows">
    <?php foreach ($sections as $s): ?>
      <a class="row" href="<?= e($u('/' . $s['slug'])) ?>"><span class="ti"><?= $secIcon($s['slug']) ?></span><span class="rt"><b><?= e($s['name']) ?></b><small><?= e(implode('، ', array_column($s['items'], 'title'))) ?></small></span><?= $ICON['arrow'] ?></a>
    <?php endforeach; ?>
  </div>
  <section class="end-cta"><h2>مش متأكد إيش يناسب الحالة؟</h2><p>كلّمني وأرشّح لك الخدمة المناسبة.</p><?= $ctaPair ?></section>

<?php elseif ($page === 'section'): $s = $bySlug[$path]; ?>
  <section class="pg-h sec-h"><span class="ti big"><?= $secIcon($s['slug']) ?></span><div><a class="back" href="<?= e($u('/services')) ?>">خدماتنا</a><h1><?= e($s['name']) ?></h1></div></section>
  <div class="items">
    <?php foreach ($s['items'] as $it): ?>
      <article class="item"><h2><?= e($it['title']) ?></h2><p><?= e($it['description']) ?></p></article>
    <?php endforeach; ?>
  </div>
  <section class="end-cta"><h2>تبي هذه الخدمة لأهلك؟</h2><p>أرسل لي تفاصيل الحالة على واتساب، وأرتّب لك الباقي.</p><a class="b b-wa" data-contact="whatsapp" href="<?= e($wa) ?>" target="_blank" rel="noopener"><?= $ICON['wa'] ?>اطلب الخدمة على واتساب</a></section>

<?php elseif ($page === 'about'): ?>
  <section class="pg-h"><h1>من نحن</h1><p>معلومات عن مركز أفنان الفائقة للخدمات الطبية المنزلية.</p></section>
  <article class="prose"><?= $data['about'] ?></article>

<?php elseif ($page === 'me'): ?>
  <section class="me">
    <?= $avatar('av xl') ?>
    <h1><?= e($rep['name']) ?></h1>
    <span class="badge dark"><?= $ICON['shield'] ?>ممثل معتمد · مركز أفنان الفائقة</span>
    <p><?= e($rep['bio']) ?></p>
    <?= $ctaPair ?>
  </section>

<?php elseif ($page === 'faq'): ?>
  <section class="pg-h"><h1>أسئلة شائعة</h1></section>
  <div class="faq"><?= $faqHtml ?></div>
  <section class="end-cta"><h2>عندك سؤال ثاني؟</h2><p>اسألني مباشرة.</p><?= $ctaPair ?></section>

<?php elseif ($page === 'contact'): ?>
  <section class="pg-h"><h1>تواصل معي</h1><p>أرد عليك على واتساب أو بالاتصال، وأتابع طلبك حتى تبدأ الرعاية.</p></section>
  <section class="contact-card"><?= $avatar('av') ?><b><?= e($rep['name']) ?></b><bdi class="big-n"><?= e($shown) ?></bdi><?= $ctaPair ?></section>

<?php else: ?>
  <section class="pg-h"><h1>الصفحة غير موجودة</h1><p><a href="<?= e($u('/')) ?>">ارجع للرئيسية</a></p></section>
<?php endif; ?>
</main>

<footer class="ft">
  <nav class="ft-nav" aria-label="روابط"><?php foreach ($nav as [$href, $label]): ?><a href="<?= e($u($href)) ?>"><?= e($label) ?></a><?php endforeach; ?></nav>
  <p>© مركز أفنان الفائقة للخدمات الطبية المنزلية · <?= e($rep['name']) ?>، ممثل معتمد</p>
</footer>

<a class="fwa" data-contact="whatsapp" href="<?= e($wa) ?>" target="_blank" rel="noopener" aria-label="واتساب <?= e($rep['name']) ?>"><?= $ICON['wa'] ?></a>

<script>
(function(){
  var btn=document.querySelector(".hd-menu"),dr=document.getElementById("drawer");
  btn.addEventListener("click",function(){var o=dr.hidden;dr.hidden=!o;btn.setAttribute("aria-expanded",o)});
  document.addEventListener("keydown",function(e){if(e.key==="Escape"&&!dr.hidden){dr.hidden=true;btn.setAttribute("aria-expanded","false");btn.focus()}});
  /* every WhatsApp / call tap is a Contact for each pixel that is loaded (Meta also gets Lead) */
  document.addEventListener("click",function(e){
    var a=e.target.closest&&e.target.closest("[data-contact]");if(!a)return;
    var m=a.getAttribute("data-contact"),p={content_name:m,rep:"<?= e($rep['code']) ?>"};
    try{if(window.fbq){fbq("track","Contact",p);fbq("track","Lead",p)}}catch(_){}
    try{if(window.ttq)ttq.track("Contact",p)}catch(_){}
    try{if(window.snaptr)snaptr("track","CUSTOM_EVENT_1",{description:"Contact "+m})}catch(_){}
    try{if(window.gtag)gtag("event","contact",{method:m,rep:p.rep})}catch(_){}
  },true);
})();
</script>
</body>
</html>
<?php
echo ob_get_clean();
