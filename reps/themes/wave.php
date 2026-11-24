<?php
/* Theme «wave» (care14) — calm water: aqua→ocean gradient, SVG wave dividers between sections, centred layout,
   capsule photos, drop-shaped icons, wave-edged service cards. Mada. */
if (!isset($rep, $data, $I)) { http_response_code(404); exit; }
require __DIR__ . '/_kit.php';
$pic = $K['pic'];
$wave = fn(string $cls = ''): string => '<svg class="w-wave ' . $cls . '" viewBox="0 0 1440 90" preserveAspectRatio="none" aria-hidden="true"><path d="M0 40c120 30 240 45 360 30S600 10 720 22s240 50 360 46 240-34 360-38v60H0z"/></svg>';
$cards = function () use ($sections, $u, $K): string {
    $h = '<div class="w-cards">';
    foreach ($sections as $s) { $h .= '<a class="w-card rv" href="' . e($u('/services/' . $s['slug'])) . '"><span class="w-ci">' . $K['secPic']($s) . '</span><h3>' . e($s['name']) . '</h3><p>' . e($s['short']) . '</p></a>'; }
    return $h . '</div>';
};
$ph = fn(string $t, string $lead, array $trail): string => '<header class="w-ph"><div class="w-w">' . $crumbs($trail) . '<h1>' . e($t) . '</h1><p>' . e($lead) . '</p></div>' . $wave() . '</header>';
$close = '<section class="w-close">' . $wave('w-wave-top') . '<div class="w-w rv"><h2>خلّ الرعاية تمشي بهدوء</h2><p>كلمنا عن الحالة، ونقترح لك الخطة المناسبة.</p><div class="w-acts">' . $K['quiz']('اطلب اشتراك', 'w-btn w-btn-w') . $K['tel']('w-btn w-btn-g') . '</div></div></section>';

echo $K['head']('wave', '#0e7490', $K['fonts']('family=Mada:wght@300;400;500;600;700;800'));
?>
<body class="w p-<?= e($page) ?>">
<header class="w-hdr"><div class="w-w w-hdr-in">
  <a class="w-logo" href="<?= e($u('/')) ?>"><img src="/img/mark.png" alt="" width="180" height="155"><b>أفنان الفائقة</b></a>
  <nav class="w-nav" aria-label="القائمة الرئيسية"><?php foreach ($nav as [$h, $t]): ?><a href="<?= e($u($h)) ?>"<?= $cur === $h ? ' aria-current="page"' : '' ?>><?= e($t) ?></a><?php endforeach; ?></nav>
  <?= $K['wa']('واتساب', 'w-btn w-btn-w w-hbtn') ?>
  <?= $K['menuBtn']('w-menu') ?>
</div></header>

<main>
<?php if ($page === 'home'): ?>
<section class="w-hero">
  <div class="w-w w-hero-in">
    <h1 class="rv">رعاية تصلكم<br>بهدوء وطمأنينة</h1>
    <p class="rv"><?= e($K['tagline']) ?></p>
    <div class="w-acts rv"><?= $K['quiz']('اطلب اشتراك', 'w-btn w-btn-w') ?><?= $K['tel']('w-btn w-btn-g') ?></div>
    <div class="w-caps rv"><span><?= $pic('baby', 'رعاية الأم والمولود', '', false) ?></span><span><?= $pic('majlis', 'ممرضة مع مريض في بيته', '', false) ?></span><span><?= $pic('physio', 'علاج طبيعي في البيت', '', false) ?></span></div>
  </div>
  <?= $wave() ?>
</section>

<section class="w-w w-sec"><div class="w-sh"><h2>خدماتنا</h2><p>ستة أقسام للرعاية في البيت.</p></div><?= $cards() ?></section>

<section class="w-pale"><?= $wave('w-wave-top w-wave-pale') ?><div class="w-w w-sec">
  <div class="w-sh"><h2>اختر مدة الاشتراك</h2></div>
  <div class="w-plans"><?php foreach ($K['plans'] as $i => [$v, $t, $s, $d]): ?><div class="w-plan rv"><span class="w-drop"><?= $I['cal'] ?></span><h3><?= e($t) ?></h3><small><?= e($s) ?></small><p><?= e($d) ?></p><?= $K['quiz']('ابدأ ' . $v, $i ? 'w-btn' : 'w-btn w-btn-o', '', $v) ?></div><?php endforeach; ?></div>
</div><?= $wave('w-wave-pale-b') ?></section>

<section class="w-w w-sec">
  <div class="w-sh"><h2>لماذا أفنان</h2></div>
  <div class="w-why"><?php foreach ($K['why'] as [$ic, $t, $d]): ?><div class="rv"><span class="w-drop"><?= $I[$ic] ?></span><h3><?= e($t) ?></h3><p><?= e($d) ?></p></div><?php endforeach; ?></div>
</section>

<section class="w-w w-sec">
  <div class="w-sh"><h2>كيف نبدأ</h2></div>
  <ol class="w-steps"><?php foreach ($K['steps'] as $i => [$t, $d]): ?><li class="rv"><b><?= $i + 1 ?></b><h3><?= e($t) ?></h3><p><?= e($d) ?></p></li><?php endforeach; ?></ol>
</section>

<section class="w-w w-sec w-narrow">
  <div class="w-sh"><h2>أسئلة تتكرر</h2></div>
  <?= $K['faq'](array_slice($data['faq'], 0, 5), 'k-faq w-faq') ?>
  <p class="w-c"><a class="w-lnk" href="<?= e($u('/faq')) ?>">كل الأسئلة</a></p>
</section>
<?= $close ?>

<?php elseif ($page === 'services'): ?>
<?= $ph('خدماتنا', $K['lead']['services'], [[null, 'خدماتنا']]) ?>
<section class="w-w w-sec"><?= $cards() ?></section>
<?php foreach ($sections as $s): ?>
<section class="w-w w-sec w-grp" id="<?= e($s['slug']) ?>"><div class="w-sh"><h2><?= e($s['name']) ?></h2></div>
  <div class="w-items"><?php foreach ($s['items'] as $it): ?><div class="rv"><h3><?= e($it['title']) ?></h3><p><?= e($it['description']) ?></p></div><?php endforeach; ?></div>
  <p class="w-c"><a class="w-lnk" href="<?= e($u('/services/' . $s['slug'])) ?>">تفاصيل <?= e($s['name']) ?></a></p></section>
<?php endforeach; ?>
<?= $close ?>

<?php elseif ($page === 'section'): ?>
<header class="w-ph"><div class="w-w"><?= $crumbs([['/services', 'خدماتنا'], [null, $sec['name']]]) ?><h1><?= e($sec['name']) ?></h1><p><?= e($K['secLead']($sec)) ?></p>
  <div class="w-acts"><?= $K['quiz']('اسأل عن ' . $sec['name'], 'w-btn w-btn-w', $sec['name']) ?><?= $K['tel']('w-btn w-btn-g') ?></div>
  <div class="w-caps w-caps1"><span><?= $K['secPic']($sec, '', false) ?></span></div></div><?= $wave() ?></header>
<section class="w-w w-sec w-narrow">
  <?php foreach ($sec['items'] as $it): ?><article class="w-art rv"><h2><?= e($it['title']) ?></h2><p><?= e($it['description']) ?></p>
    <?php if ($it['includes']): ?><h3>تشمل</h3><ul class="w-ticks"><?php foreach ($it['includes'] as $x): ?><li><?= $I['check'] ?><?= e($x) ?></li><?php endforeach; ?></ul><?php endif; ?>
    <?php if ($it['suitable_for']): ?><h3>مناسبة لـ</h3><p><?= e(implode('، ', $it['suitable_for'])) ?></p><?php endif; ?></article><?php endforeach; ?>
  <div class="w-sh"><h2>أقسام أخرى</h2></div>
  <div class="w-pills"><?php foreach ($sections as $s): if ($s['slug'] === $sec['slug']) continue; ?><a href="<?= e($u('/services/' . $s['slug'])) ?>"><?= e($s['name']) ?></a><?php endforeach; ?></div>
</section>
<?= $close ?>

<?php elseif ($page === 'about'): ?>
<?= $ph('من نحن', $K['lead']['about'], [[null, 'من نحن']]) ?>
<section class="w-w w-sec w-narrow"><?php foreach ($K['about'] as [$t, $p]): ?><article class="w-art rv"><h2><?= e($t) ?></h2><p><?= e($p) ?></p></article><?php endforeach; ?>
  <article class="w-art w-vis rv"><img src="/img/vision-2030.png" alt="شعار رؤية المملكة 2030" width="560" height="375" loading="lazy"><div><h2>مع رؤية المملكة 2030</h2><p><?= e($K['vision']) ?></p></div></article></section>
<?= $close ?>

<?php elseif ($page === 'faq'): ?>
<?= $ph('الأسئلة الشائعة', $K['lead']['faq'], [[null, 'الأسئلة الشائعة']]) ?>
<section class="w-w w-sec w-narrow"><?= $K['faq']($data['faq'], 'k-faq w-faq') ?></section>
<?= $close ?>

<?php elseif ($page === 'contact'): ?>
<?= $ph('تواصل معنا', $K['lead']['contact'], [[null, 'تواصل معنا']]) ?>
<section class="w-w w-sec w-narrow"><div class="w-plans">
  <a class="w-plan w-ct" href="<?= e($wa) ?>" target="_blank" rel="noopener"><span class="w-drop"><?= $I['wa'] ?></span><h3>واتساب</h3><b><bdi dir="ltr"><?= e($shown) ?></bdi></b><p>اكتب لنا عن الحالة ونرد عليك</p></a>
  <a class="w-plan w-ct" href="<?= e($tel) ?>"><span class="w-drop"><?= $I['tel'] ?></span><h3>اتصال</h3><b><bdi dir="ltr"><?= e($shown) ?></bdi></b><p>على مدار الساعة</p></a>
</div>
<div class="w-why w-why3"><div><span class="w-drop"><?= $I['clock'] ?></span><h3>على مدار الساعة</h3></div><div><span class="w-drop"><?= $I['pin'] ?></span><h3>جميع مناطق المملكة</h3></div><div><span class="w-drop"><?= $I['cal'] ?></span><h3>من شهر حتى سنة</h3></div></div></section>

<?php else: ?>
<?= $ph('الصفحة غير موجودة', $K['lead']['notfound'], [[null, 'غير موجودة']]) ?>
<section class="w-w w-sec"><a class="w-btn" href="<?= e($u('/')) ?>"><span>العودة للرئيسية</span></a></section>
<?php endif; ?>
</main>

<footer class="w-ft"><div class="w-w">
  <a class="w-logo" href="<?= e($u('/')) ?>"><img src="/img/mark.png" alt="" width="180" height="155" loading="lazy"><b>أفنان الفائقة</b></a>
  <p><?= e(BRAND) ?> للخدمات الطبية المنزلية. <?= e($K['tagline']) ?></p>
  <nav class="w-fnav" aria-label="الموقع"><?php foreach ($nav as [$h, $t]): ?><a href="<?= e($u($h)) ?>"><?= e($t) ?></a><?php endforeach; ?></nav>
  <nav class="w-fnav" aria-label="الخدمات"><?php foreach ($sections as $s): ?><a href="<?= e($u('/services/' . $s['slug'])) ?>"><?= e($s['name']) ?></a><?php endforeach; ?></nav>
  <div class="w-fnav"><?= $K['tel']('') ?><?= $K['wa']('واتساب', '') ?></div>
  <?= $K['pay'] ?>
  <p class="w-copy">© <?= date('Y') ?> <?= e(BRAND) ?></p>
</div></footer>
<?= $K['tail'] ?>
</body></html>
