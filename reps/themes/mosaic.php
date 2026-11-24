<?php
/* Theme «mosaic» (care12) — a photo gallery: the hero is a mosaic of photos around a text tile, services sit in a
   masonry of different heights with captions on the photos. Terracotta and cream, Almarai. */
if (!isset($rep, $data, $I)) { http_response_code(404); exit; }
require __DIR__ . '/_kit.php';
$pic = $K['pic'];
$masonry = function () use ($sections, $u, $K): string {
    $h = '<div class="g-mas">';
    foreach ($sections as $i => $s) { $h .= '<a class="g-mi g-mi' . ($i % 3) . ' rv" href="' . e($u('/services/' . $s['slug'])) . '">' . $K['secPic']($s) . '<span><b>' . e($s['name']) . '</b><small>' . e($s['short']) . '</small></span></a>'; }
    return $h . '</div>';
};
$ph = fn(string $t, string $lead, array $trail, string $photo): string => '<header class="g-ph"><div class="g-w g-ph-g"><div>' . $crumbs($trail) . '<h1>' . e($t) . '</h1><p>' . e($lead) . '</p></div><div class="g-ph-m">' . $pic($photo, '', '', false) . '</div></div></header>';
$close = '<section class="g-w g-sec"><div class="g-close rv"><div class="g-close-m">' . $pic('team') . '</div><div class="g-close-t"><h2>فريقنا جاهز لخدمتكم</h2><p>اكتبوا لنا عن الحالة، ونرشّح لكم الخدمة ومدة الاشتراك.</p><div class="g-acts">' . $K['quiz']('اطلب اشتراك', 'g-btn') . $K['tel']('g-btn g-btn-o') . '</div></div></div></section>';

echo $K['head']('mosaic', '#fbf3ec', $K['fonts']('family=Almarai:wght@300;400;700;800'));
?>
<body class="g p-<?= e($page) ?>">
<header class="g-hdr"><div class="g-w g-hdr-in">
  <a class="g-logo" href="<?= e($u('/')) ?>"><img src="/img/mark.png" alt="" width="180" height="155"><span><b>أفنان الفائقة</b><small>للخدمات الطبية المنزلية</small></span></a>
  <nav class="g-nav" aria-label="القائمة الرئيسية"><?php foreach ($nav as [$h, $t]): ?><a href="<?= e($u($h)) ?>"<?= $cur === $h ? ' aria-current="page"' : '' ?>><?= e($t) ?></a><?php endforeach; ?></nav>
  <?= $K['quiz']('اطلب اشتراك', 'g-btn g-hbtn') ?>
  <?= $K['menuBtn']('g-menu') ?>
</div></header>

<main>
<?php if ($page === 'home'): ?>
<section class="g-w g-hero">
  <div class="g-t g-t1 rv"><?= $pic('walk', 'ممرضة تساعد سيدة كبيرة في السن', '', false) ?></div>
  <div class="g-t g-t2 rv"><?= $pic('baby', 'رعاية الأم والمولود', '', false) ?></div>
  <div class="g-txt rv"><p class="g-eye">رعاية طبية منزلية</p><h1>كل لحظة رعاية،<br>في بيتكم</h1><p><?= e($K['tagline']) ?></p><div class="g-acts"><?= $K['quiz']('اطلب اشتراك', 'g-btn') ?><?= $K['wa']('واتساب', 'g-btn g-btn-o') ?></div></div>
  <div class="g-t g-t3 rv"><?= $pic('doctor', 'طبيب يقيس الضغط لمريض') ?></div>
  <div class="g-t g-t4 rv"><?= $pic('physio', 'علاج طبيعي في البيت') ?></div>
  <div class="g-t g-t5 g-stat rv"><b><bdi><?= $staff ?>+</bdi></b><span>كادر صحي مرخّص</span></div>
</section>

<section class="g-w g-sec"><div class="g-sh"><h2>خدماتنا</h2><a href="<?= e($u('/services')) ?>">كل الخدمات</a></div><?= $masonry() ?></section>

<section class="g-band"><div class="g-w g-sec g-plans">
  <div class="g-sh"><h2>اشتراك يناسب الحالة</h2></div>
  <?php foreach ($K['plans'] as $i => [$v, $t, $s, $d]): ?><div class="g-plan rv"><span class="g-num"><?= $i ? '١٢' : '١–٣' ?></span><div><h3><?= e($t) ?></h3><small><?= e($s) ?></small><p><?= e($d) ?></p></div><?= $K['quiz']('ابدأ ' . $v, 'g-btn g-btn-l', '', $v) ?></div><?php endforeach; ?>
</div></section>

<section class="g-w g-sec g-whyw">
  <div class="g-whym rv"><?= $pic('majlis', 'ممرضة مع مريض في مجلس بيته') ?></div>
  <div><div class="g-sh"><h2>لماذا أفنان</h2></div><div class="g-why"><?php foreach ($K['why'] as [$ic, $t, $d]): ?><div class="rv"><span><?= $I[$ic] ?></span><b><?= e($t) ?></b><small><?= e($d) ?></small></div><?php endforeach; ?></div></div>
</section>

<section class="g-w g-sec">
  <div class="g-sh"><h2>خطوات الاشتراك</h2></div>
  <ol class="g-steps"><?php foreach ($K['steps'] as $i => [$t, $d]): ?><li class="rv"><img src="/img/v3/<?= ['step-call', 'step-assess', 'step-arrive', 'step-report'][$i] ?>.webp" alt="" width="640" height="857" loading="lazy"><span><b><?= e($t) ?></b><small><?= e($d) ?></small></span></li><?php endforeach; ?></ol>
</section>

<section class="g-w g-sec g-faqw">
  <div class="g-sh"><h2>أسئلة تتكرر</h2><a href="<?= e($u('/faq')) ?>">كل الأسئلة</a></div>
  <?= $K['faq'](array_slice($data['faq'], 0, 6), 'k-faq g-faq') ?>
</section>
<?= $close ?>

<?php elseif ($page === 'services'): ?>
<?= $ph('خدماتنا', $K['lead']['services'], [[null, 'خدماتنا']], 'team') ?>
<section class="g-w g-sec"><?= $masonry() ?></section>
<?php foreach ($sections as $s): ?>
<section class="g-w g-sec g-grp" id="<?= e($s['slug']) ?>"><div class="g-grp-m"><?= $K['secPic']($s) ?></div><div><div class="g-sh"><h2><?= e($s['name']) ?></h2><a href="<?= e($u('/services/' . $s['slug'])) ?>">التفاصيل</a></div>
  <ul class="g-ul"><?php foreach ($s['items'] as $it): ?><li><b><?= e($it['title']) ?></b><small><?= e($it['description']) ?></small></li><?php endforeach; ?></ul></div></section>
<?php endforeach; ?>
<?= $close ?>

<?php elseif ($page === 'section'): ?>
<header class="g-ph"><div class="g-w g-ph-g"><div><?= $crumbs([['/services', 'خدماتنا'], [null, $sec['name']]]) ?><h1><?= e($sec['name']) ?></h1><p><?= e($K['secLead']($sec)) ?></p>
  <div class="g-acts"><?= $K['quiz']('اسأل عن ' . $sec['name'], 'g-btn', $sec['name']) ?><?= $K['tel']('g-btn g-btn-o') ?></div></div><div class="g-ph-m"><?= $K['secPic']($sec, '', false) ?></div></div></header>
<section class="g-w g-sec g-svcs">
  <?php foreach ($sec['items'] as $it): ?><article class="g-svc rv"><h2><?= e($it['title']) ?></h2><p><?= e($it['description']) ?></p>
    <?php if ($it['includes']): ?><h3>تشمل</h3><ul class="g-ticks"><?php foreach ($it['includes'] as $x): ?><li><?= $I['check'] ?><?= e($x) ?></li><?php endforeach; ?></ul><?php endif; ?>
    <?php if ($it['suitable_for']): ?><h3>مناسبة لـ</h3><p><?= e(implode('، ', $it['suitable_for'])) ?></p><?php endif; ?></article><?php endforeach; ?>
</section>
<section class="g-w g-sec"><div class="g-sh"><h2>أقسام أخرى</h2></div>
  <div class="g-mas g-mas-s"><?php foreach ($sections as $s): if ($s['slug'] === $sec['slug']) continue; ?><a class="g-mi g-mi1" href="<?= e($u('/services/' . $s['slug'])) ?>"><?= $K['secPic']($s) ?><span><b><?= e($s['name']) ?></b></span></a><?php endforeach; ?></div></section>
<?= $close ?>

<?php elseif ($page === 'about'): ?>
<?= $ph('من نحن', $K['lead']['about'], [[null, 'من نحن']], 'team') ?>
<section class="g-w g-sec g-svcs"><?php foreach ($K['about'] as [$t, $p]): ?><article class="g-svc rv"><h2><?= e($t) ?></h2><p><?= e($p) ?></p></article><?php endforeach; ?>
  <article class="g-svc g-vis rv"><img src="/img/vision-2030.png" alt="شعار رؤية المملكة 2030" width="560" height="375" loading="lazy"><div><h2>مع رؤية المملكة 2030</h2><p><?= e($K['vision']) ?></p></div></article></section>
<?= $close ?>

<?php elseif ($page === 'faq'): ?>
<?= $ph('الأسئلة الشائعة', $K['lead']['faq'], [[null, 'الأسئلة الشائعة']], 'majlis') ?>
<section class="g-w g-sec g-faqw"><?= $K['faq']($data['faq'], 'k-faq g-faq') ?></section>
<?= $close ?>

<?php elseif ($page === 'contact'): ?>
<?= $ph('تواصل معنا', $K['lead']['contact'], [[null, 'تواصل معنا']], 'doctor') ?>
<section class="g-w g-sec g-cts">
  <a class="g-ct rv" href="<?= e($wa) ?>" target="_blank" rel="noopener"><?= $pic('wound') ?><span><?= $I['wa'] ?><b>واتساب</b><bdi dir="ltr"><?= e($shown) ?></bdi></span></a>
  <a class="g-ct rv" href="<?= e($tel) ?>"><?= $pic('doctor') ?><span><?= $I['tel'] ?><b>اتصال</b><bdi dir="ltr"><?= e($shown) ?></bdi></span></a>
  <div class="g-ct g-ct-t"><span><?= $I['clock'] ?><b>على مدار الساعة</b><small>جميع مناطق المملكة · من شهر حتى سنة</small></span></div>
</section>

<?php else: ?>
<?= $ph('الصفحة غير موجودة', $K['lead']['notfound'], [[null, 'غير موجودة']], 'majlis') ?>
<section class="g-w g-sec"><a class="g-btn" href="<?= e($u('/')) ?>"><span>العودة للرئيسية</span></a></section>
<?php endif; ?>
</main>

<footer class="g-ft"><div class="g-w">
  <div class="g-ft-g">
    <div><a class="g-logo" href="<?= e($u('/')) ?>"><img src="/img/mark.png" alt="" width="180" height="155" loading="lazy"><span><b>أفنان الفائقة</b><small>للخدمات الطبية المنزلية</small></span></a><p><?= e($K['tagline']) ?></p></div>
    <nav aria-label="الموقع"><?php foreach ($nav as [$h, $t]): ?><a href="<?= e($u($h)) ?>"><?= e($t) ?></a><?php endforeach; ?></nav>
    <nav aria-label="الخدمات"><?php foreach ($sections as $s): ?><a href="<?= e($u('/services/' . $s['slug'])) ?>"><?= e($s['name']) ?></a><?php endforeach; ?></nav>
    <div><?= $K['tel']('g-fl') ?><?= $K['wa']('واتساب', 'g-fl') ?></div>
  </div>
  <?= $K['pay'] ?>
  <p class="g-copy">© <?= date('Y') ?> <?= e(BRAND) ?></p>
</div></footer>
<?= $K['tail'] ?>
</body></html>
