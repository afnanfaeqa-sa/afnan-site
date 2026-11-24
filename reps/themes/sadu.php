<?php
/* Theme «sadu» (care6) — Saudi heritage: Sadu weave bands drawn in CSS, deep red / black / ochre on wool cream,
   Kufi headlines, arched photo windows and arched service cards. */
if (!isset($rep, $data, $I)) { http_response_code(404); exit; }
require __DIR__ . '/_kit.php';
$pic = $K['pic'];
$band = '<div class="s-band" aria-hidden="true"></div>';
$arches = function () use ($sections, $u, $K): string {
    $h = '<div class="s-arches">';
    foreach ($sections as $s) {
        $h .= '<a class="s-arch rv" href="' . e($u('/services/' . $s['slug'])) . '"><span class="s-win">' . $K['secPic']($s) . '</span><h3>' . e($s['name']) . '</h3><p>' . e($s['short']) . '</p></a>';
    }
    return $h . '</div>';
};
$title = fn(string $t, string $tag = 'h2'): string => '<div class="s-t"><' . $tag . '>' . e($t) . '</' . $tag . '><i aria-hidden="true"></i></div>';
$ph = fn(string $t, string $lead, array $trail): string => '<header class="s-ph"><div class="s-w">' . $crumbs($trail) . $title($t, 'h1') . '<p>' . e($lead) . '</p></div>' . $band . '</header>';
$close = '<section class="s-close">' . $band . '<div class="s-w"><h2>حيّاكم في أفنان</h2><p>كلمونا عن الحالة، ونرتّب لكم الرعاية المناسبة في بيتكم.</p><div class="s-acts">' . $K['quiz']('اطلب اشتراك', 's-btn s-btn-o') . $K['tel']('s-btn s-btn-l') . '</div></div></section>';

echo $K['head']('sadu', '#1d1512', $K['fonts']('family=Reem+Kufi:wght@500;700&family=Noto+Kufi+Arabic:wght@400;500;600'));
?>
<body class="s p-<?= e($page) ?>">
<header class="s-hdr"><?= $band ?><div class="s-w s-hdr-in">
  <a class="s-logo" href="<?= e($u('/')) ?>"><img src="/img/mark.png" alt="" width="180" height="155"><b>أفنان الفائقة</b></a>
  <nav class="s-nav" aria-label="القائمة الرئيسية"><?php foreach ($nav as [$h, $t]): ?><a href="<?= e($u($h)) ?>"<?= $cur === $h ? ' aria-current="page"' : '' ?>><?= e($t) ?></a><?php endforeach; ?></nav>
  <?= $K['tel']('s-htel') ?>
  <?= $K['menuBtn']('s-menu') ?>
</div></header>

<main>
<?php if ($page === 'home'): ?>
<section class="s-w s-hero">
  <div class="s-hero-t">
    <p class="s-eye">مركز أفنان الفائقة للخدمات الطبية المنزلية</p>
    <h1 class="rv">رعاية أهلنا<br>في بيوتهم</h1>
    <p class="rv">من الرياض إلى كل مناطق المملكة: تمريض، ورعاية كبار السن، وطب منزلي، وعلاج طبيعي، باشتراك من شهر حتى سنة.</p>
    <div class="s-acts rv"><?= $K['quiz']('اطلب اشتراك', 's-btn') ?><?= $K['wa']('واتساب', 's-btn s-btn-l') ?></div>
  </div>
  <div class="s-hwin rv"><?= $pic('walk', 'ممرضة تساعد سيدة كبيرة في السن على المشي', '', false) ?></div>
</section>
<?= $band ?>

<section class="s-w s-sec"><?= $title('خدماتنا') ?><?= $arches() ?></section>

<section class="s-dark"><div class="s-w s-sec">
  <?= $title('مدة الاشتراك') ?>
  <div class="s-plans"><?php foreach ($K['plans'] as [$v, $t, $s, $d]): ?><article class="s-plan rv"><h3><?= e($t) ?></h3><b><?= e($s) ?></b><p><?= e($d) ?></p><?= $K['quiz']('ابدأ ' . $v, 's-btn s-btn-o', '', $v) ?></article><?php endforeach; ?></div>
</div></section>

<section class="s-w s-sec">
  <?= $title('ليش أفنان') ?>
  <div class="s-why"><?php foreach ($K['why'] as [$ic, $t, $d]): ?><div class="rv"><span><?= $I[$ic] ?></span><h3><?= e($t) ?></h3><p><?= e($d) ?></p></div><?php endforeach; ?></div>
</section>

<section class="s-w s-sec">
  <?= $title('كيف نبدأ') ?>
  <ol class="s-steps"><?php foreach ($K['steps'] as $i => [$t, $d]): ?><li class="rv"><span><?= $i + 1 ?></span><h3><?= e($t) ?></h3><p><?= e($d) ?></p></li><?php endforeach; ?></ol>
</section>

<section class="s-w s-sec s-narrow">
  <?= $title('أسئلة تتكرر') ?>
  <?= $K['faq'](array_slice($data['faq'], 0, 5), 'k-faq s-faq') ?>
  <p class="s-more"><a href="<?= e($u('/faq')) ?>">كل الأسئلة</a></p>
</section>
<?= $close ?>

<?php elseif ($page === 'services'): ?>
<?= $ph('خدماتنا', $K['lead']['services'], [[null, 'خدماتنا']]) ?>
<section class="s-w s-sec"><?= $arches() ?></section>
<?php foreach ($sections as $s): ?>
<section class="s-w s-sec s-grp" id="<?= e($s['slug']) ?>"><?= $title($s['name']) ?>
  <div class="s-items"><?php foreach ($s['items'] as $it): ?><div class="rv"><h3><?= e($it['title']) ?></h3><p><?= e($it['description']) ?></p></div><?php endforeach; ?></div>
  <p class="s-more"><a href="<?= e($u('/services/' . $s['slug'])) ?>">تفاصيل <?= e($s['name']) ?></a></p></section>
<?php endforeach; ?>
<?= $close ?>

<?php elseif ($page === 'section'): ?>
<header class="s-ph"><div class="s-w s-ph-g"><div><?= $crumbs([['/services', 'خدماتنا'], [null, $sec['name']]]) ?><?= $title($sec['name'], 'h1') ?><p><?= e($K['secLead']($sec)) ?></p>
  <div class="s-acts"><?= $K['quiz']('اسأل عن ' . $sec['name'], 's-btn', $sec['name']) ?><?= $K['tel']('s-btn s-btn-l') ?></div></div><div class="s-hwin s-hwin-s"><?= $K['secPic']($sec, '', false) ?></div></div><?= $band ?></header>
<section class="s-w s-sec s-narrow">
  <?php foreach ($sec['items'] as $it): ?><article class="s-card rv"><h2><?= e($it['title']) ?></h2><p><?= e($it['description']) ?></p>
    <?php if ($it['includes']): ?><h3>تشمل</h3><ul class="s-ticks"><?php foreach ($it['includes'] as $x): ?><li><?= e($x) ?></li><?php endforeach; ?></ul><?php endif; ?>
    <?php if ($it['suitable_for']): ?><h3>مناسبة لـ</h3><p><?= e(implode('، ', $it['suitable_for'])) ?></p><?php endif; ?></article><?php endforeach; ?>
  <?= $title('أقسام أخرى') ?>
  <div class="s-tags"><?php foreach ($sections as $s): if ($s['slug'] === $sec['slug']) continue; ?><a href="<?= e($u('/services/' . $s['slug'])) ?>"><?= e($s['name']) ?></a><?php endforeach; ?></div>
</section>
<?= $close ?>

<?php elseif ($page === 'about'): ?>
<?= $ph('من نحن', $K['lead']['about'], [[null, 'من نحن']]) ?>
<section class="s-w s-sec s-ab">
  <div><?php foreach ($K['about'] as [$t, $p]): ?><article class="s-card rv"><h2><?= e($t) ?></h2><p><?= e($p) ?></p></article><?php endforeach; ?></div>
  <div class="s-hwin rv"><?= $pic('majlis', 'مجلس بيت سعودي') ?></div>
</section>
<section class="s-dark"><div class="s-w s-sec s-vis"><img src="/img/vision-2030.png" alt="شعار رؤية المملكة 2030" width="560" height="375" loading="lazy"><div><h2>مع رؤية المملكة 2030</h2><p><?= e($K['vision']) ?></p></div></div></section>
<?= $close ?>

<?php elseif ($page === 'faq'): ?>
<?= $ph('الأسئلة الشائعة', $K['lead']['faq'], [[null, 'الأسئلة الشائعة']]) ?>
<section class="s-w s-sec s-narrow"><?= $K['faq']($data['faq'], 'k-faq s-faq') ?></section>
<?= $close ?>

<?php elseif ($page === 'contact'): ?>
<?= $ph('تواصل معنا', $K['lead']['contact'], [[null, 'تواصل معنا']]) ?>
<section class="s-w s-sec s-narrow"><div class="s-plans s-plans-l">
  <a class="s-plan s-ct" href="<?= e($wa) ?>" target="_blank" rel="noopener"><?= $I['wa'] ?><h3>واتساب</h3><p>اكتبوا لنا عن الحالة ونرد عليكم</p><b><bdi dir="ltr"><?= e($shown) ?></bdi></b></a>
  <a class="s-plan s-ct" href="<?= e($tel) ?>"><?= $I['tel'] ?><h3>اتصال</h3><p>على مدار الساعة، طوال أيام الأسبوع</p><b><bdi dir="ltr"><?= e($shown) ?></bdi></b></a>
</div>
<div class="s-why s-why3"><div><span><?= $I['clock'] ?></span><h3>على مدار الساعة</h3></div><div><span><?= $I['pin'] ?></span><h3>جميع مناطق المملكة</h3></div><div><span><?= $I['cal'] ?></span><h3>من شهر حتى سنة</h3></div></div></section>

<?php else: ?>
<?= $ph('الصفحة غير موجودة', $K['lead']['notfound'], [[null, 'غير موجودة']]) ?>
<section class="s-w s-sec"><a class="s-btn" href="<?= e($u('/')) ?>"><span>العودة للرئيسية</span></a></section>
<?php endif; ?>
</main>

<footer class="s-ft"><div class="s-w">
  <div class="s-ft-g">
    <div><a class="s-logo" href="<?= e($u('/')) ?>"><img src="/img/mark.png" alt="" width="180" height="155" loading="lazy"><b>أفنان الفائقة</b></a><p><?= e(BRAND) ?> للخدمات الطبية المنزلية.</p></div>
    <nav aria-label="الموقع"><?php foreach ($nav as [$h, $t]): ?><a href="<?= e($u($h)) ?>"><?= e($t) ?></a><?php endforeach; ?></nav>
    <nav aria-label="الخدمات"><?php foreach ($sections as $s): ?><a href="<?= e($u('/services/' . $s['slug'])) ?>"><?= e($s['name']) ?></a><?php endforeach; ?></nav>
    <div><?= $K['tel']('s-fl') ?><?= $K['wa']('واتساب', 's-fl') ?></div>
  </div>
  <?= $K['pay'] ?>
  <p class="s-copy">© <?= date('Y') ?> <?= e(BRAND) ?></p>
</div><?= $band ?></footer>
<?= $K['tail'] ?>
</body></html>
