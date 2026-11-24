<?php
/* Theme «royal» (care15) — formal and elegant: deep maroon and champagne gold on ivory, symmetrical centred layout,
   double gold frames with corner ornaments, octagon medallion photos, Ruqaa display titles over Naskh text. */
if (!isset($rep, $data, $I)) { http_response_code(404); exit; }
require __DIR__ . '/_kit.php';
$pic = $K['pic'];
$ar = fn($n): string => strtr((string) $n, ['0' => '٠', '1' => '١', '2' => '٢', '3' => '٣', '4' => '٤', '5' => '٥', '6' => '٦', '7' => '٧', '8' => '٨', '9' => '٩']);
$orn = '<span class="r-orn" aria-hidden="true"><i></i>◆<i></i></span>';
$title = fn(string $t, string $tag = 'h2'): string => '<div class="r-t"><' . $tag . '>' . e($t) . '</' . $tag . '>' . $orn . '</div>';
$medals = function () use ($sections, $u, $K): string {
    $h = '<div class="r-medals">';
    foreach ($sections as $s) { $h .= '<a class="r-medal rv" href="' . e($u('/services/' . $s['slug'])) . '"><span class="r-oct">' . $K['secPic']($s) . '</span><h3>' . e($s['name']) . '</h3><p>' . e($s['short']) . '</p></a>'; }
    return $h . '</div>';
};
$ph = fn(string $t, string $lead, array $trail): string => '<header class="r-ph"><div class="r-w">' . $crumbs($trail) . $title($t, 'h1') . '<p>' . e($lead) . '</p></div></header>';
$close = '<section class="r-close"><div class="r-w r-frame rv">' . $title('في خدمتكم') . '<p>تفضّلوا بالتواصل، ويسعدنا ترتيب الرعاية المناسبة في بيتكم.</p><div class="r-acts">' . $K['quiz']('اطلب اشتراك', 'r-btn') . $K['tel']('r-btn r-btn-o') . '</div></div></section>';

echo $K['head']('royal', '#5a1426', $K['fonts']('family=Aref+Ruqaa:wght@400;700&family=Noto+Naskh+Arabic:wght@400;500;600;700'));
?>
<body class="r p-<?= e($page) ?>">
<header class="r-hdr">
  <div class="r-w r-hdr-in">
    <nav class="r-nav r-nav-a" aria-label="القائمة الرئيسية"><?php foreach (array_slice($nav, 0, 3) as [$h, $t]): ?><a href="<?= e($u($h)) ?>"<?= $cur === $h ? ' aria-current="page"' : '' ?>><?= e($t) ?></a><?php endforeach; ?></nav>
    <a class="r-crest" href="<?= e($u('/')) ?>"><img src="/img/mark.png" alt="" width="180" height="155"><b>أفنان الفائقة</b></a>
    <nav class="r-nav r-nav-b" aria-label="روابط أخرى"><?php foreach (array_slice($nav, 3) as [$h, $t]): ?><a href="<?= e($u($h)) ?>"<?= $cur === $h ? ' aria-current="page"' : '' ?>><?= e($t) ?></a><?php endforeach; ?><?= $K['tel']('r-htel') ?></nav>
    <?= $K['menuBtn']('r-menu') ?>
  </div>
</header>

<main>
<?php if ($page === 'home'): ?>
<section class="r-hero r-w">
  <p class="r-eye">مركز أفنان الفائقة للخدمات الطبية المنزلية</p>
  <h1 class="rv">رعاية تليق بأهلكم</h1>
  <?= $orn ?>
  <p class="r-lead rv"><?= e($K['tagline']) ?></p>
  <div class="r-acts rv"><?= $K['quiz']('اطلب اشتراك', 'r-btn') ?><?= $K['wa']('واتساب', 'r-btn r-btn-o') ?></div>
  <figure class="r-pframe rv"><?= $pic('majlis', 'ممرضة مع مريض في مجلس بيته', '', false) ?></figure>
</section>

<section class="r-w r-sec"><?= $title('خدماتنا') ?><?= $medals() ?></section>

<section class="r-mar"><div class="r-w r-sec">
  <?= $title('مدة الاشتراك') ?>
  <div class="r-plans"><?php foreach ($K['plans'] as [$v, $t, $s, $d]): ?><div class="r-plan rv"><h3><?= e($t) ?></h3><small><?= e($s) ?></small><p><?= e($d) ?></p><?= $K['quiz']('ابدأ ' . $v, 'r-btn r-btn-g', '', $v) ?></div><?php endforeach; ?></div>
</div></section>

<section class="r-w r-sec">
  <?= $title('لماذا أفنان') ?>
  <ul class="r-why"><?php foreach ($K['why'] as [$ic, $t, $d]): ?><li class="rv"><span><?= $I[$ic] ?></span><b><?= e($t) ?></b><small><?= e($d) ?></small></li><?php endforeach; ?></ul>
</section>

<section class="r-w r-sec">
  <?= $title('خطوات الاشتراك') ?>
  <ol class="r-steps"><?php foreach ($K['steps'] as $i => [$t, $d]): ?><li class="rv"><b><?= $ar($i + 1) ?></b><h3><?= e($t) ?></h3><p><?= e($d) ?></p></li><?php endforeach; ?></ol>
</section>

<section class="r-w r-sec r-narrow">
  <?= $title('أسئلة تتكرر') ?>
  <?= $K['faq'](array_slice($data['faq'], 0, 5), 'k-faq r-faq') ?>
  <p class="r-c"><a class="r-lnk" href="<?= e($u('/faq')) ?>">كل الأسئلة</a></p>
</section>
<?= $close ?>

<?php elseif ($page === 'services'): ?>
<?= $ph('خدماتنا', $K['lead']['services'], [[null, 'خدماتنا']]) ?>
<section class="r-w r-sec"><?= $medals() ?></section>
<?php foreach ($sections as $s): ?>
<section class="r-w r-sec r-grp" id="<?= e($s['slug']) ?>"><?= $title($s['name']) ?>
  <div class="r-items"><?php foreach ($s['items'] as $it): ?><div class="rv"><h3><?= e($it['title']) ?></h3><p><?= e($it['description']) ?></p></div><?php endforeach; ?></div>
  <p class="r-c"><a class="r-lnk" href="<?= e($u('/services/' . $s['slug'])) ?>">تفاصيل <?= e($s['name']) ?></a></p></section>
<?php endforeach; ?>
<?= $close ?>

<?php elseif ($page === 'section'): ?>
<header class="r-ph"><div class="r-w"><?= $crumbs([['/services', 'خدماتنا'], [null, $sec['name']]]) ?><?= $title($sec['name'], 'h1') ?><p><?= e($K['secLead']($sec)) ?></p>
  <div class="r-acts"><?= $K['quiz']('اسأل عن ' . $sec['name'], 'r-btn', $sec['name']) ?><?= $K['tel']('r-btn r-btn-o') ?></div>
  <figure class="r-pframe r-pframe-s"><?= $K['secPic']($sec, '', false) ?></figure></div></header>
<section class="r-w r-sec r-narrow">
  <?php foreach ($sec['items'] as $it): ?><article class="r-art rv"><h2><?= e($it['title']) ?></h2><p><?= e($it['description']) ?></p>
    <?php if ($it['includes']): ?><h3>تشمل</h3><ul class="r-ticks"><?php foreach ($it['includes'] as $x): ?><li><?= e($x) ?></li><?php endforeach; ?></ul><?php endif; ?>
    <?php if ($it['suitable_for']): ?><h3>مناسبة لـ</h3><p><?= e(implode('، ', $it['suitable_for'])) ?></p><?php endif; ?></article><?php endforeach; ?>
  <?= $title('أقسام أخرى') ?>
  <div class="r-links"><?php foreach ($sections as $s): if ($s['slug'] === $sec['slug']) continue; ?><a href="<?= e($u('/services/' . $s['slug'])) ?>"><?= e($s['name']) ?></a><?php endforeach; ?></div>
</section>
<?= $close ?>

<?php elseif ($page === 'about'): ?>
<?= $ph('من نحن', $K['lead']['about'], [[null, 'من نحن']]) ?>
<section class="r-w r-sec r-narrow"><?php foreach ($K['about'] as [$t, $p]): ?><article class="r-art rv"><h2><?= e($t) ?></h2><p><?= e($p) ?></p></article><?php endforeach; ?></section>
<section class="r-mar"><div class="r-w r-sec r-vis"><img src="/img/vision-2030.png" alt="شعار رؤية المملكة 2030" width="560" height="375" loading="lazy"><div><h2>مع رؤية المملكة 2030</h2><p><?= e($K['vision']) ?></p></div></div></section>
<?= $close ?>

<?php elseif ($page === 'faq'): ?>
<?= $ph('الأسئلة الشائعة', $K['lead']['faq'], [[null, 'الأسئلة الشائعة']]) ?>
<section class="r-w r-sec r-narrow"><?= $K['faq']($data['faq'], 'k-faq r-faq') ?></section>
<?= $close ?>

<?php elseif ($page === 'contact'): ?>
<?= $ph('تواصل معنا', $K['lead']['contact'], [[null, 'تواصل معنا']]) ?>
<section class="r-w r-sec r-narrow"><div class="r-plans r-plans-l">
  <a class="r-plan r-ct" href="<?= e($wa) ?>" target="_blank" rel="noopener"><?= $I['wa'] ?><h3>واتساب</h3><b><bdi dir="ltr"><?= e($shown) ?></bdi></b><p>اكتبوا لنا عن الحالة</p></a>
  <a class="r-plan r-ct" href="<?= e($tel) ?>"><?= $I['tel'] ?><h3>اتصال</h3><b><bdi dir="ltr"><?= e($shown) ?></bdi></b><p>على مدار الساعة</p></a>
</div>
<ul class="r-why r-why3"><li><span><?= $I['clock'] ?></span><b>على مدار الساعة</b></li><li><span><?= $I['pin'] ?></span><b>جميع مناطق المملكة</b></li><li><span><?= $I['cal'] ?></span><b>من شهر حتى سنة</b></li></ul></section>

<?php else: ?>
<?= $ph('الصفحة غير موجودة', $K['lead']['notfound'], [[null, 'غير موجودة']]) ?>
<section class="r-w r-sec r-c"><a class="r-btn" href="<?= e($u('/')) ?>"><span>العودة للرئيسية</span></a></section>
<?php endif; ?>
</main>

<footer class="r-ft"><div class="r-w">
  <a class="r-crest" href="<?= e($u('/')) ?>"><img src="/img/mark.png" alt="" width="180" height="155" loading="lazy"><b>أفنان الفائقة</b></a>
  <?= $orn ?>
  <p><?= e(BRAND) ?> للخدمات الطبية المنزلية. <?= e($K['tagline']) ?></p>
  <nav class="r-fnav" aria-label="الموقع"><?php foreach ($nav as [$h, $t]): ?><a href="<?= e($u($h)) ?>"><?= e($t) ?></a><?php endforeach; ?></nav>
  <nav class="r-fnav" aria-label="الخدمات"><?php foreach ($sections as $s): ?><a href="<?= e($u('/services/' . $s['slug'])) ?>"><?= e($s['name']) ?></a><?php endforeach; ?></nav>
  <div class="r-fnav"><?= $K['tel']('') ?><?= $K['wa']('واتساب', '') ?></div>
  <?= $K['pay'] ?>
  <p class="r-copy">© <?= date('Y') ?> <?= e(BRAND) ?></p>
</div></footer>
<?= $K['tail'] ?>
</body></html>
