<?php
/* Theme «clinic» (care3) — clean Swiss-medical: white with a faint grid, cobalt-teal, numbered cards, a comparison
   table for the two plans, a horizontal step line, sticky two-column FAQ. */
if (!isset($rep, $data, $I)) { http_response_code(404); exit; }
require __DIR__ . '/_kit.php';
$pic = $K['pic'];
$cards = function () use ($sections, $u, $I): string {
    $h = '<div class="c-cards">';
    foreach ($sections as $i => $s) {
        $h .= '<a class="c-card rv" href="' . e($u('/services/' . $s['slug'])) . '"><span class="c-no">' . sprintf('%02d', $i + 1) . '</span><h3>' . e($s['name']) . '</h3><p>' . e($s['short']) . '</p><span class="c-more">التفاصيل ' . $I['arrow'] . '</span></a>';
    }
    return $h . '</div>';
};
$ph = fn(string $title, string $lead, array $trail): string => '<header class="c-ph"><div class="c-w">' . $crumbs($trail) . '<h1>' . e($title) . '</h1><p>' . e($lead) . '</p></div></header>';
$close = '<section class="c-w c-close rv"><div><h2>جاهزين نرتّب الرعاية؟</h2><p>ثلاثة أسئلة سريعة، ويتواصل معك الفريق الطبي على واتساب.</p></div><div class="c-acts">' . $K['quiz']('اطلب اشتراك', 'c-btn c-btn-w') . $K['tel']('c-btn c-btn-o') . '</div></section>';

echo $K['head']('clinic', '#ffffff', $K['fonts']('family=Tajawal:wght@400;500;700;800'));
?>
<body class="c p-<?= e($page) ?>">
<header class="c-hdr"><div class="c-w c-hdr-in">
  <a class="c-logo" href="<?= e($u('/')) ?>"><img src="/img/mark.png" alt="" width="180" height="155"><span><b>أفنان الفائقة</b><small>رعاية طبية منزلية</small></span></a>
  <nav class="c-nav" aria-label="القائمة الرئيسية"><?php foreach ($nav as [$h, $t]): ?><a href="<?= e($u($h)) ?>"<?= $cur === $h ? ' aria-current="page"' : '' ?>><?= e($t) ?></a><?php endforeach; ?></nav>
  <?= $K['quiz']('اطلب اشتراك', 'c-btn c-hbtn') ?>
  <?= $K['menuBtn']('c-menu') ?>
</div></header>

<main>
<?php if ($page === 'home'): ?>
<section class="c-w c-hero">
  <div class="c-hero-t">
    <span class="c-pill"><i></i> متاحون على مدار الساعة</span>
    <h1 class="rv">رعاية طبية منزلية<br><em>بخطة واضحة وفريق ثابت</em></h1>
    <p class="rv"><?= e($K['tagline']) ?> تمريض، ورعاية كبار السن، وطب منزلي، وعلاج طبيعي، وتحاليل.</p>
    <div class="c-acts rv"><?= $K['quiz']('اطلب اشتراك', 'c-btn') ?><?= $K['wa']('واتساب', 'c-btn c-btn-o') ?></div>
    <dl class="c-stats rv"><div><dt><bdi><?= $staff ?>+</bdi></dt><dd>كادر صحي مرخّص</dd></div><div><dt><bdi>24/7</bdi></dt><dd>متابعة</dd></div><div><dt><bdi>1–12</bdi></dt><dd>شهرًا مدة الاشتراك</dd></div></dl>
  </div>
  <div class="c-hero-m rv"><?= $pic('doctor', 'طبيب يقيس الضغط لمريض في بيته', '', false) ?><div class="c-chip"><?= $I['shield'] ?><span><b>كادر مرخّص</b><small>نفس الفريق طوال الاشتراك</small></span></div></div>
</section>

<section class="c-w c-sec">
  <div class="c-sh"><span>الخدمات</span><h2>ستة أقسام لرعاية متكاملة</h2></div>
  <?= $cards() ?>
</section>

<section class="c-band"><div class="c-w c-sec">
  <div class="c-sh"><span>الاشتراكات</span><h2>قارن بين الشهري والسنوي</h2></div>
  <div class="c-tbl rv" role="table" aria-label="مقارنة الاشتراكات">
    <div class="c-tr c-th" role="row"><span role="columnheader"></span><span role="columnheader">شهري</span><span role="columnheader">سنوي</span></div>
    <div class="c-tr" role="row"><span role="rowheader">المدة</span><span role="cell">من شهر إلى ثلاثة أشهر</span><span role="cell">اثنا عشر شهرًا</span></div>
    <div class="c-tr" role="row"><span role="rowheader">مناسب لـ</span><span role="cell">التعافي بعد العمليات والإصابات</span><span role="cell">كبار السن والأمراض المزمنة</span></div>
    <div class="c-tr" role="row"><span role="rowheader">الفريق</span><span role="cell"><?= $I['check'] ?> فريق ثابت</span><span role="cell"><?= $I['check'] ?> فريق ثابت طول السنة</span></div>
    <div class="c-tr" role="row"><span role="rowheader">المتابعة</span><span role="cell"><?= $I['check'] ?> على مدار الساعة</span><span role="cell"><?= $I['check'] ?> على مدار الساعة</span></div>
    <div class="c-tr c-tf" role="row"><span></span><span><?= $K['quiz']('ابدأ شهري', 'c-btn c-btn-s', '', 'شهري') ?></span><span><?= $K['quiz']('ابدأ سنوي', 'c-btn c-btn-s', '', 'سنوي') ?></span></div>
  </div>
</div></section>

<section class="c-w c-sec">
  <div class="c-sh"><span>آلية العمل</span><h2>أربع خطوات</h2></div>
  <ol class="c-steps"><?php foreach ($K['steps'] as $i => [$t, $d]): ?><li class="rv"><span><?= $i + 1 ?></span><h3><?= e($t) ?></h3><p><?= e($d) ?></p></li><?php endforeach; ?></ol>
</section>

<section class="c-w c-sec c-why">
  <div class="c-why-m rv"><?= $pic('team', 'فريق مركز أفنان') ?></div>
  <div><div class="c-sh"><span>لماذا أفنان</span><h2>ما يميز الرعاية عندنا</h2></div>
    <ul class="c-checks"><?php foreach ($K['why'] as [$ic, $t, $d]): ?><li class="rv"><span class="c-ic"><?= $I[$ic] ?></span><div><b><?= e($t) ?></b><p><?= e($d) ?></p></div></li><?php endforeach; ?></ul></div>
</section>

<section class="c-w c-sec c-faqw">
  <div class="c-sh c-sticky"><span>الأسئلة</span><h2>أسئلة تتكرر</h2><a class="c-lnk" href="<?= e($u('/faq')) ?>">كل الأسئلة <?= $I['arrow'] ?></a></div>
  <?= $K['faq'](array_slice($data['faq'], 0, 5), 'k-faq c-faq') ?>
</section>
<?= $close ?>

<?php elseif ($page === 'services'): ?>
<?= $ph('خدماتنا', $K['lead']['services'], [[null, 'خدماتنا']]) ?>
<section class="c-w c-sec"><?= $cards() ?></section>
<?php foreach ($sections as $s): ?>
<section class="c-w c-sec c-grp" id="<?= e($s['slug']) ?>"><div class="c-sh"><span><?= e($s['short']) ?></span><h2><?= e($s['name']) ?></h2></div>
  <div class="c-list"><?php foreach ($s['items'] as $it): ?><div class="rv"><b><?= e($it['title']) ?></b><p><?= e($it['description']) ?></p></div><?php endforeach; ?></div></section>
<?php endforeach; ?>
<?= $close ?>

<?php elseif ($page === 'section'): ?>
<header class="c-ph c-ph-img"><div class="c-w c-ph-g"><div><?= $crumbs([['/services', 'خدماتنا'], [null, $sec['name']]]) ?><h1><?= e($sec['name']) ?></h1><p><?= e($K['secLead']($sec)) ?></p>
  <div class="c-acts"><?= $K['quiz']('اسأل عن ' . $sec['name'], 'c-btn', $sec['name']) ?><?= $K['tel']('c-btn c-btn-o') ?></div></div><div class="c-hero-m"><?= $K['secPic']($sec, '', false) ?></div></div></header>
<section class="c-w c-sec">
  <div class="c-svcs"><?php foreach ($sec['items'] as $it): ?><article class="c-svc rv"><h2><?= e($it['title']) ?></h2><p><?= e($it['description']) ?></p>
    <?php if ($it['includes']): ?><h3>تشمل</h3><ul class="c-ticks"><?php foreach ($it['includes'] as $x): ?><li><?= $I['check'] ?><?= e($x) ?></li><?php endforeach; ?></ul><?php endif; ?>
    <?php if ($it['suitable_for']): ?><h3>مناسبة لـ</h3><p class="c-for"><?= e(implode('، ', $it['suitable_for'])) ?></p><?php endif; ?></article><?php endforeach; ?></div>
  <div class="c-sh c-also"><span>تصفّح</span><h2>أقسام أخرى</h2></div>
  <div class="c-chips"><?php foreach ($sections as $s): if ($s['slug'] === $sec['slug']) continue; ?><a href="<?= e($u('/services/' . $s['slug'])) ?>"><?= e($s['name']) ?></a><?php endforeach; ?></div>
</section>
<?= $close ?>

<?php elseif ($page === 'about'): ?>
<?= $ph('من نحن', $K['lead']['about'], [[null, 'من نحن']]) ?>
<section class="c-w c-sec c-why">
  <div><?php foreach ($K['about'] as [$t, $p]): ?><div class="c-ab rv"><h2><?= e($t) ?></h2><p><?= e($p) ?></p></div><?php endforeach; ?></div>
  <div class="c-why-m rv"><?= $pic('majlis', 'مجلس بيت سعودي') ?></div>
</section>
<section class="c-band"><div class="c-w c-sec c-vis"><img src="/img/vision-2030.png" alt="شعار رؤية المملكة 2030" width="560" height="375" loading="lazy"><div><h2>مع رؤية المملكة 2030</h2><p><?= e($K['vision']) ?></p></div></div></section>
<?= $close ?>

<?php elseif ($page === 'faq'): ?>
<?= $ph('الأسئلة الشائعة', $K['lead']['faq'], [[null, 'الأسئلة الشائعة']]) ?>
<section class="c-w c-sec"><?= $K['faq']($data['faq'], 'k-faq c-faq') ?></section>
<?= $close ?>

<?php elseif ($page === 'contact'): ?>
<?= $ph('تواصل معنا', $K['lead']['contact'], [[null, 'تواصل معنا']]) ?>
<section class="c-w c-sec c-cgrid">
  <a class="c-cbox c-cwa rv" href="<?= e($wa) ?>" target="_blank" rel="noopener"><?= $I['wa'] ?><b>واتساب</b><span>اكتب لنا عن الحالة ونرد عليك</span><bdi dir="ltr"><?= e($shown) ?></bdi></a>
  <a class="c-cbox rv" href="<?= e($tel) ?>"><?= $I['tel'] ?><b>اتصال</b><span>كلّمنا مباشرة</span><bdi dir="ltr"><?= e($shown) ?></bdi></a>
  <div class="c-cbox"><?= $I['clock'] ?><b>ساعات العمل</b><span>على مدار الساعة، طوال أيام الأسبوع</span></div>
  <div class="c-cbox"><?= $I['pin'] ?><b>التغطية</b><span>جميع مناطق المملكة العربية السعودية</span></div>
</section>

<?php else: ?>
<?= $ph('الصفحة غير موجودة', $K['lead']['notfound'], [[null, 'غير موجودة']]) ?>
<section class="c-w c-sec"><a class="c-btn" href="<?= e($u('/')) ?>"><span>العودة للرئيسية</span></a></section>
<?php endif; ?>
</main>

<footer class="c-ft"><div class="c-w">
  <div class="c-ft-g">
    <div><a class="c-logo c-logo-w" href="<?= e($u('/')) ?>"><img src="/img/mark.png" alt="" width="180" height="155" loading="lazy"><span><b>أفنان الفائقة</b><small>رعاية طبية منزلية</small></span></a><p><?= e($K['tagline']) ?></p></div>
    <nav aria-label="الموقع"><h3>الموقع</h3><?php foreach ($nav as [$h, $t]): ?><a href="<?= e($u($h)) ?>"><?= e($t) ?></a><?php endforeach; ?></nav>
    <nav aria-label="الخدمات"><h3>الخدمات</h3><?php foreach ($sections as $s): ?><a href="<?= e($u('/services/' . $s['slug'])) ?>"><?= e($s['name']) ?></a><?php endforeach; ?></nav>
    <div><h3>تواصل</h3><?= $K['tel']('c-flink') ?><?= $K['wa']('واتساب', 'c-flink') ?></div>
  </div>
  <?= $K['pay'] ?>
  <p class="c-copy">© <?= date('Y') ?> <?= e(BRAND) ?> للخدمات الطبية المنزلية</p>
</div></footer>
<?= $K['tail'] ?>
</body></html>
