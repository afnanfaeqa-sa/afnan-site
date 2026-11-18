<?php
/*
 * Theme «bento» — the V3 design (photo tiles, capsule buttons, notched corners, ECG pulse line, request quiz)
 * moved onto a rep site and recoloured to a Saudi identity: Vision green + gold, the 8-point star lattice,
 * and the Vision 2030 logo. Included from reps/index.php after the rep, routes, links and pixels are set up.
 * Same rules as every rep site: speaks only as «مركز أفنان الفائقة», rep's number only, rep's pixels only,
 * no reviews or claims we can't back.
 */
if (!isset($rep, $data, $I)) { http_response_code(404); exit; }

$img = '/img/care1/';
$secImg = ['med' => 'doctor', 'nurse' => 'wound', 'elder' => 'walk', 'kids' => 'baby', 'rehab' => 'physio', 'lab' => 'lab'];
$pic = fn(string $name, string $alt, string $cls = '', bool $lazy = true): string =>
    '<img' . ($cls ? ' class="' . $cls . '"' : '') . ' src="' . $img . $name . '.webp" srcset="' . $img . $name . '-800.webp 800w, ' . $img . $name . '.webp 1376w" sizes="(min-width: 1024px) 50vw, 100vw" alt="' . e($alt) . '" width="1376" height="768"' . ($lazy ? ' loading="lazy" decoding="async"' : ' fetchpriority="high"') . '>';
$cssV = (string) @filemtime(__DIR__ . '/bento.css');
$code = (string) $rep['code'];
$arrow = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M19 12H5m6-6-6 6 6 6"/></svg>';
$cap = fn(string $txt, string $href, string $cls = 'b-cap', bool $ext = true): string => '<a class="' . $cls . '" href="' . e($href) . '"' . ($ext ? ' target="_blank" rel="noopener"' : '') . '><span>' . e($txt) . '</span>' . $arrow . '</a>';
$quizBtn = fn(string $txt, string $svc = '', string $dur = '', string $cls = 'b-cap'): string => '<a class="' . $cls . '" href="' . e($wa) . '" target="_blank" rel="noopener" data-quiz' . ($svc ? ' data-svc="' . e($svc) . '"' : '') . ($dur ? ' data-dur="' . e($dur) . '"' : '') . '><span>' . e($txt) . '</span>' . $arrow . '</a>';
$circ = '<span class="b-notch"><span class="b-circ">' . $arrow . '</span></span>';
$ecg = '<svg class="b-ecg" viewBox="0 0 1200 60" preserveAspectRatio="none" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" aria-hidden="true"><path d="M0 34h520l14-24 16 44 14-32 10 12h626"/></svg>';
$vision = '<img class="b-v2030" src="/img/vision-2030.png" alt="شعار رؤية المملكة 2030" width="560" height="375" loading="lazy">';

$head = function (string $title, string $lead, array $trail, string $photo) use ($crumbs, $pic): string {
    return '<header class="b-wrap b-phead"><div class="b-phead-t">' . $pic($photo, '', 'b-bg', false) . '<div class="b-pad">' . $crumbs($trail) . '<h1>' . e($title) . '</h1><p>' . e($lead) . '</p></div></div></header>';
};
$close = '<section class="b-sec b-wrap"><div class="b-close">' . $ecg . '<div><h2>رعاية أهلكم تبدأ برسالة</h2><p>جاوب على ثلاثة أسئلة سريعة، ويتواصل معك الفريق الطبي على واتساب بالخطة المناسبة.</p></div><div class="b-acts">' . $quizBtn('ابدأ الآن', '', '', 'b-cap b-cap-light') . '<a class="b-ph" href="' . e($tel) . '"><bdi dir="ltr">' . e($shown) . '</bdi></a></div></div></section>';
$secTiles = function () use ($sections, $u, $pic, $secImg, $circ): string {
    $h = '<div class="b-tiles">';
    foreach ($sections as $s) {
        $h .= '<a class="b-t b-dark b-tile" href="' . e($u('/services/' . $s['slug'])) . '">' . $pic($secImg[$s['key']] ?? 'team', '', 'b-bg')
            . '<div class="b-pad"><h3>' . e($s['name']) . '</h3><p>' . e($s['short']) . '</p></div>' . $circ . '</a>';
    }
    return $h . '</div>';
};
$faq = function (array $items): string {
    $h = '<div class="b-faq">';
    foreach ($items as $f) { $h .= '<details><summary>' . e($f['q']) . '<i aria-hidden="true"></i></summary><p>' . e($f['a']) . '</p></details>'; }
    return $h . '</div>';
};
$slides = [
    ['walk',   'رعاية كبار السن في البيت', 'اشتراك سنوي',   'نفس الفريق طول السنة'],
    ['physio', 'علاج طبيعي بعد العمليات',   'اشتراك شهري',   'جلسات في بيتك'],
    ['wound',  'تمريض منزلي منتظم',          'شهري أو سنوي',  'ممرضون مرخّصون'],
    ['baby',   'رعاية الأم والمولود',         'اشتراك شهري',   'بعد الولادة مباشرة'],
];

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
<meta name="theme-color" content="#0b3a27">
<link rel="icon" href="/img/mark.png">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Alexandria:wght@400;500;600;700&family=IBM+Plex+Sans+Arabic:wght@400;500;600&display=swap">
<?php if ($page === 'home'): ?><link rel="preload" as="image" href="<?= $img ?>walk-800.webp" imagesrcset="<?= $img ?>walk-800.webp 800w, <?= $img ?>walk.webp 1376w" imagesizes="100vw"><?php endif; ?>
<link rel="stylesheet" href="/themes/bento.css?v=<?= e($cssV) ?>">
<?= $px ?>
</head>
<body class="b p-<?= e($page) ?>">
<div class="b-promo"><span class="b-promo-v">2030</span>رعاية منزلية باشتراك من شهر حتى سنة. <a href="<?= e($wa) ?>" target="_blank" rel="noopener" data-quiz>اعرف الخطة المناسبة</a></div>

<header class="b-hdr">
  <div class="b-hbar"><div class="b-wrap b-hbar-in">
    <a class="b-hlogo" href="<?= e($u('/')) ?>"><span class="b-disc"><img src="/img/mark.png" alt="" width="180" height="155"></span><span><strong><?= e(BRAND) ?></strong><small>للخدمات الطبية المنزلية</small></span></a>
    <nav class="b-nav" aria-label="القائمة الرئيسية"><?php foreach ($nav as [$h, $t]): ?><a href="<?= e($u($h)) ?>"<?= $cur === $h ? ' aria-current="page"' : '' ?>><?= e($t) ?></a><?php endforeach; ?></nav>
    <span class="b-hsp"></span>
    <a class="b-hcall" href="<?= e($tel) ?>" aria-label="اتصل بنا"><?= $I['tel'] ?></a>
    <a class="b-hgo" href="<?= e($wa) ?>" target="_blank" rel="noopener" data-quiz>اشترك</a>
    <button class="b-menu" type="button" aria-controls="b-sheet" aria-label="القائمة"><i><b></b><b></b></i></button>
  </div></div>
  <svg class="b-pulse" viewBox="0 0 1500 20" preserveAspectRatio="xMidYMid slice" fill="none" stroke-width="1.6" stroke-linejoin="round" stroke-linecap="round" aria-hidden="true"><path class="base" stroke="currentColor" d="M0 10h120l5-6 6 12 5-9 4 3h150l5-6 6 12 5-9 4 3h150l5-6 6 12 5-9 4 3h150l5-6 6 12 5-9 4 3h150l5-6 6 12 5-9 4 3h150l5-6 6 12 5-9 4 3h150l5-6 6 12 5-9 4 3h150"/><path class="run" stroke="currentColor" d="M0 10h120l5-6 6 12 5-9 4 3h150l5-6 6 12 5-9 4 3h150l5-6 6 12 5-9 4 3h150l5-6 6 12 5-9 4 3h150l5-6 6 12 5-9 4 3h150l5-6 6 12 5-9 4 3h150l5-6 6 12 5-9 4 3h150"/></svg>
</header>

<div class="b-qz" id="b-sheet" role="dialog" aria-modal="true" aria-label="القائمة" hidden><div class="b-qz-p">
  <div class="b-grab"></div>
  <div class="b-qz-top"><b>القائمة</b><button type="button" data-close>إغلاق</button></div>
  <div class="b-ans">
    <?php foreach ($nav as [$h, $t]): ?><a class="b-an" href="<?= e($u($h)) ?>"<?= $cur === $h ? ' aria-current="page"' : '' ?>><?= e($t) ?></a><?php endforeach; ?>
    <a class="b-an" href="<?= e($tel) ?>"><span>اتصل بنا</span><bdi dir="ltr"><?= e($shown) ?></bdi></a>
  </div>
</div></div>

<main>
<?php if ($page === 'home'): ?>
<div class="b-tick" aria-hidden="true"><div class="b-tick-t"><?php for ($k = 0; $k < 2; $k++): foreach (["أكثر من $staff كادر صحي مرخّص", 'متابعة على مدار الساعة', 'جميع مناطق المملكة', 'اشتراك من شهر حتى سنة', 'ممرضة مقيمة حسب الحالة'] as $t): ?><span><?= $I['check'] ?><?= e($t) ?></span><?php endforeach; endfor; ?></div></div>

<div class="b-wrap">
  <h1 class="b-hh rv">رعايتكم علينا <svg viewBox="0 0 120 32" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 18h34l6-12 8 22 7-16 4 6h57"/></svg></h1>

  <div class="b-bento">
    <div class="b-t b-hero rv" id="b-hero">
      <div class="b-slides"><?php foreach ($slides as $i => $s): ?><div class="b-slide<?= $i ? '' : ' on' ?>"><?= $pic($s[0], '', '', $i > 0) ?></div><?php endforeach; ?></div>
      <div class="b-pad">
        <h2 id="b-heroH" aria-live="polite"><span><?= e($slides[0][1]) ?></span></h2>
        <div class="b-glass"><b id="b-heroC"><?= e($slides[0][2]) ?></b><small id="b-heroS"><?= e($slides[0][3]) ?></small></div>
      </div>
      <svg class="b-pulse-l" viewBox="0 0 600 60" preserveAspectRatio="none" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round" aria-hidden="true"><path d="M0 34h200l12-22 14 40 12-30 8 12h354"/></svg>
      <div class="b-dots" role="tablist" aria-label="الشرائح"></div>
      <span class="b-notch"><?= $quizBtn('ابدأ الآن') ?></span>
    </div>

    <a class="b-t b-tall b-dark rv" href="<?= e($u('/services/nursing')) ?>">
      <?= $pic('wound', 'ممرض من مركز أفنان يعتني بجرح مريض في بيته', 'b-bg') ?>
      <div class="b-pad"><h3>التمريض المنزلي وممرضة مقيمة</h3></div>
      <span class="b-cap b-cap-full"><span>تفاصيل التمريض</span><?= $arrow ?></span>
    </a>

    <a class="b-t b-sm b-v rv" href="<?= e($u('/about')) ?>">
      <div class="b-pad"><h3>نساهم في مستهدفات رؤية 2030 للقطاع الصحي</h3></div>
      <?= $vision ?>
      <?= $circ ?>
    </a>

    <a class="b-t b-sm b-dark rv" href="<?= e($u('/services/home-doctor')) ?>">
      <?= $pic('doctor', 'طبيب يقيس الضغط لمريض في مجلس بيته', 'b-bg') ?>
      <div class="b-pad"><h3>طبيب أسرة في بيتك</h3></div>
      <?= $circ ?>
    </a>
  </div>
</div>

<section class="b-sec b-wrap">
  <h2 class="b-sh rv">وش تحتاجون؟</h2>
  <div class="b-goals rv"><?php foreach ($sections as $s): ?><a class="b-goal" href="<?= e($u('/services/' . $s['slug'])) ?>"><?= e($s['name']) ?></a><?php endforeach; ?></div>
</section>

<section class="b-sec b-wrap b-sec0">
  <h2 class="b-sh rv">شهري أو سنوي</h2>
  <div class="b-plans">
    <div class="b-pl b-pl-m rv"><span class="b-lbl">اشتراك شهري</span><span class="b-big"><bdi>1–3</bdi> أشهر</span><p>للتعافي بعد العمليات والإصابات، والحالات اللي تحتاج متابعة لفترة محددة.</p><?= $quizBtn('ابدأ شهري', '', 'شهري') ?></div>
    <div class="b-pl b-pl-y rv"><span class="b-tag">رعاية مستمرة</span><span class="b-lbl">اشتراك سنوي</span><span class="b-big"><bdi>12</bdi> شهر</span><p>لكبار السن والأمراض المزمنة، بخطة رعاية تُراجع كل فترة ونفس الفريق طول السنة.</p><?= $quizBtn('ابدأ سنوي', '', 'سنوي', 'b-cap b-cap-light') ?></div>
  </div>
</section>

<section class="b-sec b-wrap b-sec0">
  <h2 class="b-sh rv">ليش أفنان؟</h2>
  <div class="b-feat">
    <div class="b-f rv"><span class="b-ic"><?= $I['clock'] ?></span><h3>متابعة <bdi>24/7</bdi></h3><p>بالاتصال أو واتساب لأي تعديل أو طارئ.</p></div>
    <div class="b-f rv"><span class="b-ic"><?= $I['team'] ?></span><h3>فريق ثابت</h3><p>نفس الكادر يتابع الحالة طوال الاشتراك.</p></div>
    <div class="b-f rv"><span class="b-ic"><?= $I['shield'] ?></span><h3>كادر مرخّص</h3><p>أكثر من <bdi><?= $staff ?></bdi> من الكوادر الصحية.</p></div>
    <div class="b-f rv"><span class="b-ic"><?= $I['pin'] ?></span><h3>جميع مناطق المملكة</h3><p>نصل إليكم أينما كنتم.</p></div>
  </div>
</section>

<section class="b-sec b-wrap b-sec0">
  <h2 class="b-sh rv">كيف يبدأ اشتراككم؟</h2>
  <p class="b-sub rv">أربع خطوات من أول رسالة لين يكون عندكم فريق ثابت يتابع الحالة.</p>
  <div class="b-rail">
    <?php foreach ([['step-call', 'تواصلون معنا', 'اتصال أو واتساب، ونسمع عن الحالة واحتياجها.'], ['step-assess', 'نقيّم الحالة', 'الفريق الطبي يحدد الخدمة والكادر المناسب.'], ['step-arrive', 'خطة الاشتراك', 'نتفق على المدة، من شهر حتى سنة، ويبدأ الكادر.'], ['step-report', 'متابعة مستمرة', 'نفس الفريق يتابع الحالة طوال الاشتراك.']] as $i => [$f, $t, $d]): ?>
    <article class="b-step rv"><img src="/img/v3/<?= $f ?>.webp" alt="" width="640" height="857" loading="lazy" decoding="async"><span class="b-no"><bdi>0<?= $i + 1 ?></bdi></span><h3><?= e($t) ?></h3><p><?= e($d) ?></p></article>
    <?php endforeach; ?>
  </div>
</section>

<section class="b-sec b-wrap b-sec0">
  <div class="b-shrow"><h2 class="b-sh rv">خدماتنا</h2><a class="rv" href="<?= e($u('/services')) ?>">كل الخدمات</a></div>
  <?= $secTiles() ?>
  <div class="b-bign rv"><b><bdi><?= $staff ?>+</bdi></b><span>كادر صحي مرخّص يخدم جميع مناطق المملكة، على مدار الساعة</span></div>
</section>

<section class="b-sec b-wrap b-sec0">
  <div class="b-shrow"><h2 class="b-sh rv">أسئلة تتكرر</h2><a class="rv" href="<?= e($u('/faq')) ?>">كل الأسئلة</a></div>
  <div class="rv"><?= $faq(array_slice($data['faq'], 0, 5)) ?></div>
</section>
<?= $close ?>

<?php elseif ($page === 'services'): ?>
<?= $head('خدماتنا', 'رعاية طبية منزلية في ستة أقسام، كلها باشتراك من شهر حتى سنة.', [[null, 'خدماتنا']], 'team') ?>
<section class="b-sec b-wrap">
  <?= $secTiles() ?>
  <?php foreach ($sections as $s): ?>
  <div class="b-group rv" id="<?= e($s['slug']) ?>">
    <div class="b-shrow"><h2 class="b-sh"><?= e($s['name']) ?></h2><a href="<?= e($u('/services/' . $s['slug'])) ?>">التفاصيل</a></div>
    <ul class="b-list"><?php foreach ($s['items'] as $it): ?><li><b><?= e($it['title']) ?></b><span><?= e($it['description']) ?></span></li><?php endforeach; ?></ul>
  </div>
  <?php endforeach; ?>
</section>
<?= $close ?>

<?php elseif ($page === 'section'): ?>
<?= $head($sec['name'], $sec['short'] . '، باشتراك من شهر حتى سنة، في جميع مناطق المملكة.', [['/services', 'خدماتنا'], [null, $sec['name']]], $secImg[$sec['key']] ?? 'team') ?>
<section class="b-sec b-wrap b-sec-tight">
  <div class="b-btns rv"><?= $quizBtn('اسأل عن ' . $sec['name'], $sec['name']) ?><a class="b-out" href="<?= e($tel) ?>"><?= $I['tel'] ?><bdi dir="ltr"><?= e($shown) ?></bdi></a></div>
  <div class="b-svcs">
  <?php foreach ($sec['items'] as $it): ?>
    <article class="b-svc rv">
      <h2><?= e($it['title']) ?></h2>
      <p><?= e($it['description']) ?></p>
      <?php if ($it['includes']): ?><h3>تشمل</h3><ul class="b-ticks"><?php foreach ($it['includes'] as $x): ?><li><?= $I['check'] ?><?= e($x) ?></li><?php endforeach; ?></ul><?php endif; ?>
      <?php if ($it['suitable_for']): ?><h3>مناسبة لـ</h3><p class="b-for"><?= e(implode('، ', $it['suitable_for'])) ?></p><?php endif; ?>
    </article>
  <?php endforeach; ?>
  </div>
  <h2 class="b-sh b-also">أقسام أخرى</h2>
  <div class="b-goals"><?php foreach ($sections as $s): if ($s['slug'] === $sec['slug']) continue; ?><a class="b-goal" href="<?= e($u('/services/' . $s['slug'])) ?>"><?= e($s['name']) ?></a><?php endforeach; ?></div>
</section>
<?= $close ?>

<?php elseif ($page === 'about'): ?>
<?= $head('من نحن', 'مقدّم خاص لخدمات الرعاية الصحية المنزلية في المملكة العربية السعودية، ومقرّه مدينة الرياض.', [[null, 'من نحن']], 'majlis') ?>
<section class="b-sec b-wrap b-sec-tight">
  <div class="b-about">
    <div class="b-prose rv">
      <h2>نبذة عن المركز</h2>
      <p><?= e(BRAND) ?> للخدمات الطبية المنزلية يقدّم رعاية صحية في البيت: تمريض، ورعاية كبار السن، وطب منزلي، وعلاج طبيعي، وتحاليل وأشعة، ورعاية الأم والمولود.</p>
      <h2>الكادر والتغطية</h2>
      <p>يضم المركز أكثر من <?= $staff ?> من الكوادر الصحية المرخّصة، ويقدّم خدماته في مختلف مناطق المملكة على مدار الساعة.</p>
      <h2>طريقة العمل</h2>
      <p>نعمل بنظام الاشتراك فقط، من شهر واحد حتى سنة كاملة، بخطة رعاية واضحة وكادر ثابت يتابع الحالة. ولا نقدّم زيارات منفردة.</p>
    </div>
    <div class="b-t b-photo rv"><?= $pic('team', 'فريق من مركز أفنان أمام منزل في الرياض', 'b-bg') ?></div>
  </div>
  <div class="b-vband rv"><?= $vision ?><div><h2>مع رؤية المملكة 2030</h2><p>نساهم في مستهدفات رؤية المملكة 2030 في القطاع الصحي، بنقل الرعاية إلى بيت المريض ورفع جودة الحياة لأهلنا وكبارنا.</p></div></div>
  <div class="b-feat b-feat-n">
    <div class="b-f rv"><b><bdi><?= $staff ?>+</bdi></b><p>كادر صحي مرخّص</p></div>
    <div class="b-f rv"><b><bdi>24/7</bdi></b><p>على مدار الساعة</p></div>
    <div class="b-f rv"><b>المملكة</b><p>جميع المناطق</p></div>
    <div class="b-f rv"><b><bdi>1–12</bdi></b><p>شهرًا مدة الاشتراك</p></div>
  </div>
</section>
<?= $close ?>

<?php elseif ($page === 'faq'): ?>
<?= $head('الأسئلة الشائعة', 'إجابات مختصرة عن الحجز والاشتراك والتغطية والدفع.', [[null, 'الأسئلة الشائعة']], 'majlis') ?>
<section class="b-sec b-wrap b-sec-tight"><?= $faq($data['faq']) ?></section>
<?= $close ?>

<?php elseif ($page === 'contact'): ?>
<?= $head('تواصل معنا', 'نرد عليك على واتساب أو بالاتصال، على مدار الساعة.', [[null, 'تواصل معنا']], 'doctor') ?>
<section class="b-sec b-wrap b-sec-tight">
  <div class="b-contact">
    <a class="b-cbox b-cbox-wa rv" href="<?= e($wa) ?>" target="_blank" rel="noopener"><?= $I['wa'] ?><b>واتساب</b><span>اكتب لنا عن الحالة ونرد عليك</span><bdi dir="ltr"><?= e($shown) ?></bdi></a>
    <a class="b-cbox rv" href="<?= e($tel) ?>"><?= $I['tel'] ?><b>اتصال</b><span>كلّمنا مباشرة</span><bdi dir="ltr"><?= e($shown) ?></bdi></a>
  </div>
  <div class="b-feat b-feat-c">
    <div class="b-f rv"><span class="b-ic"><?= $I['clock'] ?></span><h3>ساعات العمل</h3><p>على مدار الساعة، طوال أيام الأسبوع</p></div>
    <div class="b-f rv"><span class="b-ic"><?= $I['pin'] ?></span><h3>التغطية</h3><p>جميع مناطق المملكة العربية السعودية</p></div>
    <div class="b-f rv"><span class="b-ic"><?= $I['cal'] ?></span><h3>الاشتراك</h3><p>من شهر واحد حتى سنة كاملة</p></div>
  </div>
</section>

<?php else: ?>
<?= $head('الصفحة غير موجودة', 'الرابط غير صحيح أو تغيّر.', [[null, 'غير موجودة']], 'majlis') ?>
<section class="b-sec b-wrap b-sec-tight"><?= $cap('العودة للرئيسية', $u('/'), 'b-cap', false) ?></section>
<?php endif; ?>
</main>

<footer class="b-ft"><div class="b-wrap">
  <div class="b-ft-g">
    <div class="b-ft-b"><a class="b-logo" href="<?= e($u('/')) ?>"><img src="/img/mark.png" alt="" width="180" height="155" loading="lazy"><b>أفنان <em>الفائقة</em></b></a><p>مركز أفنان الفائقة للخدمات الطبية المنزلية. رعاية طبية في البيت باشتراك من شهر حتى سنة.</p><?= $vision ?></div>
    <div><h3>الموقع</h3><ul><?php foreach ($nav as [$h, $t]): ?><li><a href="<?= e($u($h)) ?>"><?= e($t) ?></a></li><?php endforeach; ?></ul></div>
    <div><h3>الخدمات</h3><ul><?php foreach ($sections as $s): ?><li><a href="<?= e($u('/services/' . $s['slug'])) ?>"><?= e($s['name']) ?></a></li><?php endforeach; ?></ul></div>
    <div><h3>تواصل</h3><ul><li><a href="<?= e($tel) ?>"><bdi dir="ltr"><?= e($shown) ?></bdi></a></li><li><a href="<?= e($wa) ?>" target="_blank" rel="noopener">واتساب</a></li><li>على مدار الساعة</li><li>جميع مناطق المملكة</li></ul></div>
  </div>
  <ul class="b-pay" aria-label="طرق الدفع"><?php foreach (['mada' => 'مدى', 'visa' => 'Visa', 'applepay' => 'Apple Pay', 'stcpay' => 'STC Pay', 'tabby' => 'تابي', 'tamara' => 'تمارا'] as $f => $alt): ?><li><img src="/img/pay/<?= $f ?>.png" alt="<?= e($alt) ?>" height="20" loading="lazy"></li><?php endforeach; ?></ul>
  <div class="b-ft-l"><span>© <?= date('Y') ?> <?= e(BRAND) ?> للخدمات الطبية المنزلية</span></div>
</div></footer>

<a class="b-float" href="<?= e($wa) ?>" target="_blank" rel="noopener" aria-label="راسلنا على واتساب"><?= $I['wa'] ?></a>

<div class="b-qz" id="b-quiz" role="dialog" aria-modal="true" aria-labelledby="b-qT" hidden><div class="b-qz-p">
  <div class="b-grab"></div>
  <div class="b-qz-top"><button type="button" id="b-qBack">رجوع</button><b id="b-qT">طلب اشتراك</b><button type="button" data-close>إغلاق</button></div>
  <div class="b-bar"><i id="b-qBar"></i></div>
  <div class="b-q" data-q="0"><h4>وش الخدمة اللي تحتاجها؟</h4><div class="b-ans" data-k="svc"><?php foreach ($sections as $s): ?><button class="b-an" type="button"><?= e($s['name']) ?></button><?php endforeach; ?></div></div>
  <div class="b-q" data-q="1"><h4>الرعاية لمين؟</h4><div class="b-ans" data-k="who"><?php foreach (['لي أنا', 'لأبوي أو أمي', 'لزوجي أو زوجتي', 'لطفلي', 'لشخص ثاني'] as $t): ?><button class="b-an" type="button"><?= e($t) ?></button><?php endforeach; ?></div></div>
  <div class="b-q" data-q="2"><h4>كم مدة الاشتراك؟</h4><div class="b-ans" data-k="dur">
    <button class="b-an" type="button" data-v="شهري"><span>شهري<small>من شهر إلى ثلاثة أشهر</small></span></button>
    <button class="b-an" type="button" data-v="سنوي"><span>سنوي<small>رعاية مستمرة طول السنة</small></span></button>
    <button class="b-an" type="button" data-v="مو متأكد"><span>مو متأكد<small>نساعدك نحدد الأنسب</small></span></button></div></div>
  <div class="b-q" data-q="3"><h4>آخر خطوة</h4>
    <div class="b-fld"><label for="b-qCity">المدينة</label><select id="b-qCity"><?php foreach (['الرياض', 'جدة', 'مكة المكرمة', 'المدينة المنورة', 'الدمام', 'الخبر', 'القصيم', 'أبها', 'تبوك', 'حائل', 'جازان', 'نجران', 'الباحة', 'الجوف', 'عرعر', 'مدينة أخرى'] as $c): ?><option><?= e($c) ?></option><?php endforeach; ?></select></div>
    <div class="b-sum" id="b-qSum"></div>
    <a class="b-cap b-cap-full" id="b-qSend" href="<?= e($wa) ?>" target="_blank" rel="noopener"><span>أرسل الطلب على واتساب</span><?= $arrow ?></a>
    <p class="b-note">يفتح واتساب برسالة جاهزة فيها اختياراتك، ويرد عليك الفريق الطبي.</p>
  </div>
</div></div>

<script>
(function(){
var $=function(s,r){return (r||document).querySelector(s)},$$=function(s,r){return [].slice.call((r||document).querySelectorAll(s))};
var RM=matchMedia('(prefers-reduced-motion: reduce)').matches;
var PHONE=<?= json_encode((string) $rep['phone']) ?>,CODE=<?= json_encode($code) ?>,BASE='السلام عليكم، أبي أستفسر عن خدمات الرعاية المنزلية';

/* ticker */
var tk=$('.b-tick-t');if(tk&&!RM){var tx=0;(function loop(){var w=tk.scrollWidth/2;tx=(tx+.45)%w;tk.style.transform='translate3d('+tx.toFixed(1)+'px,0,0)';requestAnimationFrame(loop)})()}

/* hero carousel */
var hero=$('#b-hero');if(hero){
  var S=<?= json_encode(array_map(fn($s) => [$s[1], $s[2], $s[3]], $slides), JSON_UNESCAPED_UNICODE) ?>;
  var slides=$$('.b-slide',hero),dots=$('.b-dots',hero),cur=0,timer;
  S.forEach(function(_,i){var b=document.createElement('button');b.type='button';b.setAttribute('aria-label','الشريحة '+(i+1));b.onclick=function(){show(i);auto()};dots.appendChild(b)});
  function show(i){cur=(i+S.length)%S.length;slides.forEach(function(s,k){s.classList.toggle('on',k===cur);var im=s.querySelector('img');if(k===cur&&im)im.loading='eager'});$$('button',dots).forEach(function(b,k){b.setAttribute('aria-current',k===cur?'true':'false')});
    $('#b-heroH').innerHTML='<span>'+S[cur][0]+'</span>';$('#b-heroC').textContent=S[cur][1];$('#b-heroS').textContent=S[cur][2]}
  function auto(){clearInterval(timer);if(!RM)timer=setInterval(function(){show(cur+1)},5200)}
  show(0);auto();
  var sx=null;hero.addEventListener('touchstart',function(e){sx=e.touches[0].clientX},{passive:true});
  hero.addEventListener('touchend',function(e){if(sx===null)return;var dx=e.changedTouches[0].clientX-sx;if(Math.abs(dx)>40){show(cur+(dx>0?1:-1));auto()}sx=null});
}

/* sheets */
function openSh(el){el.hidden=false;requestAnimationFrame(function(){el.classList.add('open')});document.body.style.overflow='hidden'}
function closeSh(el){el.classList.remove('open');document.body.style.overflow='';setTimeout(function(){el.hidden=true},380)}
$$('.b-qz').forEach(function(el){el.addEventListener('click',function(e){if(e.target===el||e.target.closest('[data-close]'))closeSh(el)})});
addEventListener('keydown',function(e){if(e.key==='Escape')$$('.b-qz').forEach(function(el){if(!el.hidden)closeSh(el)})});
$('.b-menu').addEventListener('click',function(){openSh($('#b-sheet'))});

/* quiz → WhatsApp message with the rep code at the end */
var quiz=$('#b-quiz'),qs=$$('.b-q',quiz),ans={svc:'',who:'',dur:''},step=0;
function msg(){var l=[BASE];if(ans.svc)l.push('الخدمة: '+ans.svc);if(ans.who)l.push('الرعاية: '+ans.who);if(ans.dur)l.push('المدة: '+ans.dur);l.push('المدينة: '+$('#b-qCity').value);return l.join('\n')+'\n('+CODE+')'}
function upd(){$('#b-qSend').href='https://wa.me/'+PHONE+'?text='+encodeURIComponent(msg())}
function go(n){step=Math.max(0,Math.min(3,n));qs.forEach(function(q,i){q.classList.toggle('on',i===step)});$('#b-qBar').style.transform='scaleX('+((step+1)/4)+')';$('#b-qBack').style.visibility=step?'visible':'hidden';
  if(step===3){var esc=function(s){return String(s).replace(/[<>&]/g,'')};$('#b-qSum').innerHTML='<span>الخدمة: <b>'+esc(ans.svc||'—')+'</b></span><span>الرعاية: <b>'+esc(ans.who||'—')+'</b></span><span>المدة: <b>'+esc(ans.dur||'—')+'</b></span>';upd()}}
$$('.b-ans[data-k]',quiz).forEach(function(g){$$('.b-an',g).forEach(function(b){b.addEventListener('click',function(){$$('.b-an',g).forEach(function(x){x.setAttribute('aria-pressed',x===b?'true':'false')});ans[g.dataset.k]=b.dataset.v||b.textContent.trim();
  var nx=step+1;if(g.dataset.k==='who'&&ans.dur)nx=3;setTimeout(function(){go(nx)},RM?0:220)})})});
$('#b-qCity').addEventListener('change',upd);
$('#b-qBack').addEventListener('click',function(){go(step-1)});
function start(svc,dur){ans={svc:'',who:'',dur:''};$$('.b-an',quiz).forEach(function(x){x.setAttribute('aria-pressed','false')});var s=0;
  if(svc){ans.svc=svc;var b=$$('[data-k="svc"] .b-an',quiz).filter(function(x){return x.textContent.trim()===svc})[0];b&&b.setAttribute('aria-pressed','true');s=1}
  if(dur){ans.dur=dur;var d=$$('[data-k="dur"] .b-an',quiz).filter(function(x){return x.dataset.v===dur})[0];d&&d.setAttribute('aria-pressed','true')}
  go(s);openSh(quiz)}
$$('[data-quiz]').forEach(function(el){el.addEventListener('click',function(e){e.preventDefault();start(el.dataset.svc,el.dataset.dur)})});

/* entrance */
var els=$$('.rv');if(!('IntersectionObserver' in window)||RM){els.forEach(function(e){e.classList.add('in')})}else{
  var io=new IntersectionObserver(function(es){es.forEach(function(x){if(x.isIntersecting){x.target.classList.add('in');io.unobserve(x.target)}})},{rootMargin:'0px 0px -6% 0px',threshold:.08});
  els.forEach(function(e,i){var sib=[].slice.call(e.parentNode.children).filter(function(x){return x.classList.contains('rv')}),k=sib.indexOf(e);if(k>0)e.style.transitionDelay=Math.min(k*70,350)+'ms';io.observe(e)})}
})();
</script>
<?= $track ?>
</body>
</html>
<?php
echo ob_get_clean();
