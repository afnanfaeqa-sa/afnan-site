<?php
/*
 * Theme «majlis» — a completely separate design for a rep site (first used by care1).
 * Included from reps/index.php after the rep, routes, contact links and pixels are set up; it only renders.
 * Look: brand-guide navy + green, warm sand, Najdi mud-wall triangles, full-bleed photos of our own
 * staff in green scrubs with the white Afnan mark embroidered on the left chest (reps/img/care1/*).
 * Same rules as every rep site: speaks only as «مركز أفنان الفائقة», rep's number only, rep's pixels only.
 */
if (!isset($rep, $data, $I)) { http_response_code(404); exit; }

$img = '/img/care1/';
$secImg = ['med' => 'doctor', 'nurse' => 'wound', 'elder' => 'walk', 'kids' => 'baby', 'rehab' => 'physio', 'lab' => 'lab'];
$pic = fn(string $name, string $alt, string $cls = '', bool $lazy = true): string =>
    '<img' . ($cls ? ' class="' . $cls . '"' : '') . ' src="' . $img . $name . '.webp" srcset="' . $img . $name . '-800.webp 800w, ' . $img . $name . '.webp 1376w" sizes="(min-width: 900px) 60vw, 100vw" alt="' . e($alt) . '" width="1376" height="768"' . ($lazy ? ' loading="lazy" decoding="async"' : ' fetchpriority="high"') . '>';
$cssV = (string) @filemtime(__DIR__ . '/majlis.css');

$wa1 = fn(string $txt = 'راسلنا على واتساب', string $cls = 'm-btn m-wa'): string => '<a class="' . $cls . '" href="' . e($wa) . '" target="_blank" rel="noopener">' . $I['wa'] . '<span>' . e($txt) . '</span></a>';
$tel1 = fn(string $cls = 'm-btn m-out'): string => '<a class="' . $cls . '" href="' . e($tel) . '">' . $I['tel'] . '<span><bdi dir="ltr">' . e($shown) . '</bdi></span></a>';

$head = function (string $title, string $lead, array $trail, string $photo = 'team') use ($crumbs, $pic): string {
    return '<header class="m-phead">' . $pic($photo, '', 'm-phead-bg', false) . '<div class="m-wrap m-phead-in">' . $crumbs($trail) . '<h1>' . e($title) . '</h1><p>' . e($lead) . '</p></div></header>';
};
$cta = '<section class="m-cta"><div class="m-wrap m-cta-in"><div><p class="m-eyebrow m-on-dark">نحن هنا على مدار الساعة</p><h2>كلّمنا عن الحالة، ونرتّب لك الرعاية</h2><p>يرشّح لك الفريق الطبي الخدمة ومدة الاشتراك المناسبة، ويبدأ الكادر في البيت.</p></div><div class="m-cta-btns">' . $wa1('ابدأ على واتساب', 'm-btn m-wa m-lg') . $tel1('m-btn m-out-light m-lg') . '</div></div></section>';
$svcRows = function () use ($sections, $u, $I, $pic, $secImg): string {
    $h = '<div class="m-rows">';
    foreach ($sections as $i => $s) {
        $h .= '<a class="m-row" href="' . e($u('/services/' . $s['slug'])) . '"><figure>' . $pic($secImg[$s['key']] ?? 'team', '') . '</figure>'
            . '<div class="m-row-b"><span class="m-num">' . str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) . '</span><h3>' . e($s['name']) . '</h3><p>' . e($s['short']) . '</p>'
            . '<ul>' . implode('', array_map(fn($it) => '<li>' . e($it['title']) . '</li>', array_slice($s['items'], 0, 3))) . '</ul>'
            . '<span class="m-go">تفاصيل القسم' . $I['arrow'] . '</span></div></a>';
    }
    return $h . '</div>';
};

ob_start();
?><!doctype html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<script>document.documentElement.className+=" js"</script>
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="robots" content="noindex, nofollow">
<title><?= e($docTitle) ?></title>
<meta name="description" content="<?= e(BRAND) ?> للخدمات الطبية المنزلية: تمريض ورعاية كبار السن وطب منزلي وعلاج طبيعي وتحاليل، باشتراك من شهر حتى سنة، في جميع مناطق المملكة.">
<meta name="theme-color" content="#01246F">
<link rel="icon" href="/img/mark.png">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Readex+Pro:wght@500;600;700&family=Tajawal:wght@400;500;700&display=swap">
<?php if ($page === 'home'): ?><link rel="preload" as="image" href="<?= $img ?>majlis.webp" imagesrcset="<?= $img ?>majlis-800.webp 800w, <?= $img ?>majlis.webp 1376w" imagesizes="100vw"><?php endif; ?>
<link rel="stylesheet" href="/themes/majlis.css?v=<?= e($cssV) ?>">
<?= $px ?>
</head>
<body class="m p-<?= e($page) ?>">
<div class="m-strip"><div class="m-wrap m-strip-in"><span><?= $I['clock'] ?>على مدار الساعة، طوال أيام الأسبوع</span><span class="m-strip-pin"><?= $I['pin'] ?>جميع مناطق المملكة</span><a href="<?= e($tel) ?>"><?= $I['tel'] ?><bdi dir="ltr"><?= e($shown) ?></bdi></a></div></div>
<header class="m-top"><div class="m-wrap m-top-in">
  <a class="m-brand" href="<?= e($u('/')) ?>"><img src="/img/mark.png" alt="" width="52" height="45"><span><b><?= e(BRAND) ?></b><small>للخدمات الطبية المنزلية</small></span></a>
  <nav class="m-nav" aria-label="القائمة الرئيسية"><?php foreach ($nav as [$h, $t]): ?><a href="<?= e($u($h)) ?>"<?= $cur === $h ? ' aria-current="page"' : '' ?>><?= e($t) ?></a><?php endforeach; ?></nav>
  <?= $wa1('احجز الآن', 'm-btn m-wa m-sm m-top-wa') ?>
  <button class="m-menu" type="button" aria-expanded="false" aria-controls="m-drawer" aria-label="القائمة"><?= $I['menu'] ?></button>
</div>
<div class="m-drawer" id="m-drawer" hidden><div class="m-wrap">
  <?php foreach ($nav as [$h, $t]): ?><a href="<?= e($u($h)) ?>"<?= $cur === $h ? ' aria-current="page"' : '' ?>><?= e($t) ?><?= $I['arrow'] ?></a><?php endforeach; ?>
  <div class="m-drawer-btns"><?= $wa1('واتساب') ?><?= $tel1() ?></div>
</div></div></header>

<main>
<?php if ($page === 'home'): ?>
<section class="m-hero">
  <?= $pic('majlis', 'ممرضة من مركز أفنان تتابع نبض جدّ في مجلس بيته', 'm-hero-bg', false) ?>
  <div class="m-wrap m-hero-in">
    <p class="m-eyebrow m-on-dark">رعاية طبية منزلية باشتراك</p>
    <h1><span class="m-ln"><span>نرعى أهلك</span></span><span class="m-ln"><span>في <em>مجلسهم</em></span></span></h1>
    <p class="m-lead">ممرضون وممرضات وأطباء مرخّصون يتابعون الحالة في البيت، باشتراك من شهر حتى سنة، في جميع مناطق المملكة.</p>
    <div class="m-hero-btns"><?= $wa1('اطلب الرعاية على واتساب', 'm-btn m-wa m-lg') ?><?= $tel1('m-btn m-out-light m-lg') ?></div>
  </div>
</section>
<section class="m-stats" aria-label="أرقام المركز"><div class="m-wrap m-stats-in">
  <div><b><span data-count="<?= $staff ?>"><?= $staff ?></span>+</b><span>كادر صحي مرخّص</span></div>
  <div><b>24/7</b><span>على مدار الساعة</span></div>
  <div><b><span data-count="6">6</span></b><span>أقسام رعاية</span></div>
  <div><b>1–12</b><span>شهرًا مدة الاشتراك</span></div>
</div></section>
<div class="m-marquee" aria-hidden="true"><div class="m-marquee-track"><?php for ($k = 0; $k < 2; $k++): ?><span><?php foreach ($sections as $s): ?><i><?= e($s['name']) ?></i><?php endforeach; ?><i>ممرضة مقيمة</i><i>متابعة على مدار الساعة</i><i>جميع مناطق المملكة</i></span><?php endfor; ?></div></div>

<section class="m-sec"><div class="m-wrap">
  <div class="m-h"><p class="m-eyebrow">خدماتنا</p><h2>كل ما يحتاجه المريض، <em>في بيته</em></h2><p>ستة أقسام، وفريق واحد يتابع الحالة من أول يوم حتى آخر يوم في الاشتراك.</p></div>
  <?= $svcRows() ?>
</div></section>

<section class="m-band"><div class="m-wrap m-band-in">
  <figure><?= $pic('team', 'فريق مركز أفنان أمام منزل في الرياض') ?></figure>
  <blockquote><p>فريق ثابت يعرف أهلك، <em>ويعرفونه.</em></p><footer>نختار لك ممرضًا أو ممرضة حسب تفضيلك، ويبقى الكادر نفسه طوال الاشتراك.</footer></blockquote>
</div></section>

<section class="m-sec m-steps-sec"><div class="m-wrap">
  <div class="m-h m-h-light"><p class="m-eyebrow m-on-dark">كيف نبدأ</p><h2>ثلاث خطوات وتبدأ الرعاية</h2></div>
  <ol class="m-steps">
    <li><b>تواصل معنا</b><span>على واتساب أو بالاتصال، وأخبرنا عن الحالة.</span></li>
    <li><b>نقيّم الحالة</b><span>يحدد الفريق الطبي الخدمة ومدة الاشتراك المناسبة.</span></li>
    <li><b>تبدأ الرعاية في البيت</b><span>يصل الكادر في الموعد، ونتابع معك طوال الاشتراك.</span></li>
  </ol>
</div></section>

<section class="m-sec m-story"><div class="m-wrap m-story-in">
  <figure class="m-story-art"><?= $pic('walk', 'ممرضة ترافق جدّة في فناء بيت نجدي') ?></figure>
  <div>
    <p class="m-eyebrow">قصة الاسم</p><h2>همّة مثل <em>طويق</em></h2>
    <p>من همّة السعوديين التي تشبه جبل طويق في ثباتها استلهمنا اسمنا، ونحمل هذه الهمّة إلى كل بيت نخدمه: رعاية ثابتة، ومتابعة لا تنقطع، وكادر يعرف أهلك ويعرفونه.</p>
    <p class="m-vision"><span aria-hidden="true">2030</span>نساهم في تحقيق مستهدفات رؤية المملكة 2030 في القطاع الصحي، بنقل الرعاية إلى بيت المريض.</p>
  </div>
</div></section>

<section class="m-sec m-sand"><div class="m-wrap m-sub">
  <div><p class="m-eyebrow">الاشتراك</p><h2>خطة رعاية، <em>لا زيارة عابرة</em></h2><p>خدماتنا باشتراك فقط، من شهر واحد حتى سنة كاملة، حتى يكون للحالة فريق ثابت وخطة متابعة واضحة.</p></div>
  <ul class="m-ticks">
    <li><?= $I['check'] ?>ممرض أو ممرضة مقيمة حسب الحالة</li>
    <li><?= $I['check'] ?>تختار ممرضًا أو ممرضة حسب تفضيلك</li>
    <li><?= $I['check'] ?>الدفع خلال 24 ساعة بعد تقديم الخدمة</li>
    <li><?= $I['check'] ?>مدى، فيزا، Apple Pay، STC Pay، تابي، تمارا</li>
  </ul>
</div></section>

<section class="m-sec"><div class="m-wrap m-narrow">
  <div class="m-h"><p class="m-eyebrow">أسئلة شائعة</p><h2>قبل أن تسأل</h2></div>
  <?= $faqList(array_slice($data['faq'], 0, 4)) ?>
  <p class="m-more"><a href="<?= e($u('/faq')) ?>">كل الأسئلة<?= $I['arrow'] ?></a></p>
</div></section>
<?= $cta ?>

<?php elseif ($page === 'services'): ?>
<?= $head('خدماتنا', 'رعاية طبية منزلية في ستة أقسام، كلها باشتراك من شهر حتى سنة.', [[null, 'خدماتنا']], 'doctor') ?>
<section class="m-sec"><div class="m-wrap">
  <?= $svcRows() ?>
</div></section>
<?= $cta ?>

<?php elseif ($page === 'section'): ?>
<?= $head($sec['name'], $sec['short'] . '، باشتراك من شهر حتى سنة، في جميع مناطق المملكة.', [['/services', 'خدماتنا'], [null, $sec['name']]], $secImg[$sec['key']] ?? 'team') ?>
<section class="m-sec"><div class="m-wrap">
  <div class="m-svc-btns"><?= $wa1('اسأل عن ' . $sec['name'], 'm-btn m-wa m-lg') ?><?= $tel1('m-btn m-out m-lg') ?></div>
  <div class="m-svcs">
  <?php foreach ($sec['items'] as $it): ?>
    <article class="m-svc">
      <h2><?= e($it['title']) ?></h2>
      <p><?= e($it['description']) ?></p>
      <?php if ($it['includes']): ?><h3>تشمل</h3><ul class="m-ticks m-ticks-sm"><?php foreach ($it['includes'] as $x): ?><li><?= $I['check'] ?><?= e($x) ?></li><?php endforeach; ?></ul><?php endif; ?>
      <?php if ($it['suitable_for']): ?><h3>مناسبة لـ</h3><p class="m-for"><?= e(implode('، ', $it['suitable_for'])) ?></p><?php endif; ?>
    </article>
  <?php endforeach; ?>
  </div>
  <h2 class="m-also">أقسام أخرى</h2>
  <div class="m-chips"><?php foreach ($sections as $s): if ($s['slug'] === $sec['slug']) continue; ?><a href="<?= e($u('/services/' . $s['slug'])) ?>"><?= e($s['name']) ?></a><?php endforeach; ?></div>
</div></section>
<?= $cta ?>

<?php elseif ($page === 'about'): ?>
<?= $head('من نحن', 'مقدّم خاص لخدمات الرعاية الصحية المنزلية في المملكة العربية السعودية، ومقرّه مدينة الرياض.', [[null, 'من نحن']], 'team') ?>
<section class="m-sec"><div class="m-wrap m-about">
  <div class="m-prose">
    <h2>نبذة عن المركز</h2>
    <p><?= e(BRAND) ?> للخدمات الطبية المنزلية يقدّم رعاية صحية في البيت: تمريض، ورعاية كبار السن، وطب منزلي، وعلاج طبيعي، وتحاليل وأشعة، ورعاية الأم والمولود.</p>
    <h2>الكادر والتغطية</h2>
    <p>يضم المركز أكثر من <?= $staff ?> من الكوادر الصحية المرخّصة، ويقدّم خدماته في مختلف مناطق المملكة على مدار الساعة.</p>
    <h2>طريقة العمل</h2>
    <p>نعمل بنظام الاشتراك فقط، من شهر واحد حتى سنة كاملة، بخطة رعاية واضحة وكادر ثابت يتابع الحالة. ولا نقدّم زيارات منفردة.</p>
  </div>
  <div class="m-mosaic"><?= $pic('baby', 'ممرضة تساعد أمًّا مع مولودها') ?><?= $pic('physio', 'أخصائي علاج طبيعي يرافق مريضًا يمشي بالمشّاية') ?><?= $pic('lab', 'ممرضة تسحب عينة دم في البيت') ?></div>
</div></section>
<section class="m-stats m-stats-flat" aria-label="أرقام المركز"><div class="m-wrap m-stats-in">
  <div><b><?= $staff ?>+</b><span>كادر صحي مرخّص</span></div>
  <div><b>24/7</b><span>على مدار الساعة</span></div>
  <div><b>المملكة</b><span>جميع المناطق</span></div>
  <div><b>1–12</b><span>شهرًا مدة الاشتراك</span></div>
</div></section>
<section class="m-sec m-story"><div class="m-wrap m-story-in">
  <figure class="m-story-art m-story-svg"><?= $tuwaiq ?></figure>
  <div>
    <p class="m-eyebrow">قصة الاسم</p><h2>همّة مثل <em>طويق</em></h2>
    <p>من همّة السعوديين التي تشبه جبل طويق في ثباتها استلهمنا اسمنا، ونحمل هذه الهمّة إلى كل بيت نخدمه.</p>
    <p class="m-vision"><span aria-hidden="true">2030</span>نعمل بما يتماشى مع مستهدفات رؤية المملكة 2030 في جودة الحياة والرعاية الصحية.</p>
  </div>
</div></section>
<?= $cta ?>

<?php elseif ($page === 'faq'): ?>
<?= $head('الأسئلة الشائعة', 'إجابات مختصرة عن الحجز والاشتراك والتغطية والدفع.', [[null, 'الأسئلة الشائعة']], 'majlis') ?>
<section class="m-sec"><div class="m-wrap m-narrow"><?= $faqList($data['faq']) ?></div></section>
<?= $cta ?>

<?php elseif ($page === 'contact'): ?>
<?= $head('تواصل معنا', 'نرد عليك على واتساب أو بالاتصال، على مدار الساعة.', [[null, 'تواصل معنا']], 'doctor') ?>
<section class="m-sec"><div class="m-wrap">
  <div class="m-contact">
    <a class="m-cbox m-cbox-wa" href="<?= e($wa) ?>" target="_blank" rel="noopener"><?= $I['wa'] ?><b>واتساب</b><span>اكتب لنا عن الحالة ونرد عليك</span><bdi dir="ltr"><?= e($shown) ?></bdi></a>
    <a class="m-cbox" href="<?= e($tel) ?>"><?= $I['tel'] ?><b>اتصال</b><span>كلّمنا مباشرة</span><bdi dir="ltr"><?= e($shown) ?></bdi></a>
  </div>
  <ul class="m-cinfo">
    <li><?= $I['clock'] ?><span><b>ساعات العمل</b>على مدار الساعة، طوال أيام الأسبوع</span></li>
    <li><?= $I['pin'] ?><span><b>التغطية</b>جميع مناطق المملكة العربية السعودية</span></li>
    <li><?= $I['cal'] ?><span><b>الاشتراك</b>من شهر واحد حتى سنة كاملة</span></li>
  </ul>
</div></section>

<?php else: ?>
<?= $head('الصفحة غير موجودة', 'الرابط غير صحيح أو تغيّر.', [[null, 'غير موجودة']], 'majlis') ?>
<section class="m-sec"><div class="m-wrap"><a class="m-btn m-wa" href="<?= e($u('/')) ?>">العودة للرئيسية</a></div></section>
<?php endif; ?>
</main>

<footer class="m-foot"><div class="m-najdi" aria-hidden="true"></div><div class="m-wrap">
  <div class="m-foot-top">
    <div class="m-foot-brand"><img src="/img/mark.png" alt="" width="64" height="55" loading="lazy"><b><?= e(BRAND) ?></b><p>رعاية صحية منزلية بكوادر مرخّصة، في جميع مناطق المملكة العربية السعودية.</p></div>
    <nav aria-label="روابط"><h3>الموقع</h3><?php foreach ($nav as [$h, $t]): ?><a href="<?= e($u($h)) ?>"><?= e($t) ?></a><?php endforeach; ?></nav>
    <nav aria-label="الخدمات"><h3>الخدمات</h3><?php foreach ($sections as $s): ?><a href="<?= e($u('/services/' . $s['slug'])) ?>"><?= e($s['name']) ?></a><?php endforeach; ?></nav>
    <div><h3>تواصل</h3><a href="<?= e($wa) ?>" target="_blank" rel="noopener">واتساب</a><a href="<?= e($tel) ?>"><bdi dir="ltr"><?= e($shown) ?></bdi></a><span>على مدار الساعة</span></div>
  </div>
  <ul class="m-pay" aria-label="طرق الدفع"><?php foreach (['mada' => 'مدى', 'visa' => 'Visa', 'applepay' => 'Apple Pay', 'stcpay' => 'STC Pay', 'tabby' => 'تابي', 'tamara' => 'تمارا'] as $f => $alt): ?><li><img src="/img/pay/<?= $f ?>.png" alt="<?= e($alt) ?>" height="20" loading="lazy"></li><?php endforeach; ?></ul>
  <p class="m-copy">© <?= date('Y') ?> <?= e(BRAND) ?> للخدمات الطبية المنزلية</p>
</div></footer>

<a class="m-float" href="<?= e($wa) ?>" target="_blank" rel="noopener" aria-label="راسلنا على واتساب"><?= $I['wa'] ?></a>

<script>
(function(){
var reduce=window.matchMedia&&matchMedia('(prefers-reduced-motion: reduce)').matches,de=document.documentElement;
var go=function(){de.classList.add('ready')};requestAnimationFrame(go);setTimeout(go,120);
var top=document.querySelector('.m-top');addEventListener('scroll',function(){top&&top.classList.toggle('scrolled',scrollY>30)},{passive:true});
var sel='.m-h,.m-row,.m-steps li,.m-story-art,.m-story-in>div,.m-sub>div,.m-ticks li,.qa,.m-band figure,.m-band blockquote,.m-stats-in>div,.m-svc,.m-cbox,.m-cinfo li,.m-mosaic img,.m-prose,.m-cta-in>*,.m-chips a,.m-svc-btns,.m-foot-top>*';
var els=[].slice.call(document.querySelectorAll(sel));
els.forEach(function(el){el.classList.add('rv');var sib=[].slice.call(el.parentNode.children).filter(function(x){return x.matches&&x.matches(sel)});var i=sib.indexOf(el);if(i>0)el.style.transitionDelay=Math.min(i*90,450)+'ms'});
function count(el){var n=+el.getAttribute('data-count'),t0=null,d=1400;if(reduce||!n){el.textContent=n;return}function f(t){t0=t0||t;var p=Math.min((t-t0)/d,1);el.textContent=Math.round(n*(1-Math.pow(1-p,3)));if(p<1)requestAnimationFrame(f)}el.textContent='0';requestAnimationFrame(f)}
if(!('IntersectionObserver' in window)||reduce){els.forEach(function(e){e.classList.add('in')});return}
var io=new IntersectionObserver(function(es){es.forEach(function(x){if(!x.isIntersecting)return;x.target.classList.add('in');[].forEach.call(x.target.querySelectorAll('[data-count]'),count);io.unobserve(x.target)})},{rootMargin:'0px 0px -8% 0px',threshold:.12});
els.forEach(function(e){io.observe(e)});
})();
(function(){var b=document.querySelector('.m-menu'),d=document.getElementById('m-drawer');if(!b||!d)return;
b.addEventListener('click',function(){var o=d.hidden;d.hidden=!o;b.setAttribute('aria-expanded',String(o));b.innerHTML=o?<?= json_encode($I['close']) ?>:<?= json_encode($I['menu']) ?>;});})();
</script>
<?= $track ?>
</body>
</html>
<?php
echo ob_get_clean();
