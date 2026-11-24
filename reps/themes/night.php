<?php
/* Theme «night» (care4) — dark and immersive: full-screen photo hero, glass panels, emerald glow,
   services as a horizontal swipe rail of tall photo cards. */
if (!isset($rep, $data, $I)) { http_response_code(404); exit; }
require __DIR__ . '/_kit.php';
$pic = $K['pic'];
$rail = function () use ($sections, $u, $K): string {
    $h = '<div class="n-rail" tabindex="0" aria-label="الخدمات">';
    foreach ($sections as $s) {
        $h .= '<a class="n-rc" href="' . e($u('/services/' . $s['slug'])) . '">' . $K['secPic']($s) . '<div><h3>' . e($s['name']) . '</h3><p>' . e($s['short']) . '</p></div></a>';
    }
    return $h . '</div>';
};
$ph = fn(string $title, string $lead, array $trail, string $photo): string => '<header class="n-ph">' . $pic($photo, '', 'n-bg', false) . '<div class="n-w">' . $crumbs($trail) . '<h1>' . e($title) . '</h1><p>' . e($lead) . '</p></div></header>';
$close = '<section class="n-w n-sec"><div class="n-close rv"><h2>رعاية أهلكم تبدأ برسالة</h2><p>جاوب على ثلاثة أسئلة، ويتواصل معك الفريق الطبي على واتساب.</p><div class="n-acts">' . $K['quiz']('اطلب اشتراك', 'n-btn') . $K['tel']('n-btn n-btn-g') . '</div></div></section>';

echo $K['head']('night', '#07120e', $K['fonts']('family=Cairo:wght@400;600;700;800'));
?>
<body class="n p-<?= e($page) ?>">
<header class="n-hdr"><div class="n-w n-hdr-in">
  <a class="n-logo" href="<?= e($u('/')) ?>"><img src="/img/mark.png" alt="" width="180" height="155"><b>أفنان الفائقة</b></a>
  <nav class="n-nav" aria-label="القائمة الرئيسية"><?php foreach ($nav as [$h, $t]): ?><a href="<?= e($u($h)) ?>"<?= $cur === $h ? ' aria-current="page"' : '' ?>><?= e($t) ?></a><?php endforeach; ?></nav>
  <?= $K['tel']('n-htel') ?>
  <?= $K['menuBtn']('n-menu') ?>
</div></header>

<main>
<?php if ($page === 'home'): ?>
<section class="n-hero">
  <?= $pic('majlis', '', 'n-bg', false) ?>
  <div class="n-w n-hero-in">
    <p class="n-eye">مركز أفنان الفائقة للخدمات الطبية المنزلية</p>
    <h1 class="rv">رعاية طبية<br><span>في هدوء بيتك</span></h1>
    <p class="n-lead rv"><?= e($K['tagline']) ?></p>
    <div class="n-acts rv"><?= $K['quiz']('اطلب اشتراك', 'n-btn') ?><?= $K['wa']('واتساب', 'n-btn n-btn-g') ?></div>
  </div>
  <div class="n-w n-glass-row rv"><?php foreach ([[$staff . '+', 'كادر صحي مرخّص'], ['24/7', 'متابعة مستمرة'], ['1–12', 'شهرًا اشتراك']] as [$b, $s]): ?><div class="n-glass"><b><bdi><?= e($b) ?></bdi></b><span><?= e($s) ?></span></div><?php endforeach; ?></div>
</section>

<section class="n-sec">
  <div class="n-w n-sh"><h2>خدماتنا</h2><a href="<?= e($u('/services')) ?>">كل الخدمات <?= $I['arrow'] ?></a></div>
  <div class="n-w"><?= $rail() ?></div>
</section>

<section class="n-w n-sec">
  <div class="n-sh"><h2>اختر مدة الرعاية</h2></div>
  <div class="n-plans"><?php foreach ($K['plans'] as $i => [$v, $t, $s, $d]): ?>
    <article class="n-plan<?= $i ? ' n-plan-hi' : '' ?> rv"><span class="n-tag"><?= e($s) ?></span><h3><?= e($t) ?></h3><p><?= e($d) ?></p><?= $K['quiz']('ابدأ ' . $v, $i ? 'n-btn' : 'n-btn n-btn-g', '', $v) ?></article>
  <?php endforeach; ?></div>
</section>

<section class="n-w n-sec">
  <div class="n-sh"><h2>لماذا أفنان</h2></div>
  <div class="n-why"><?php foreach ($K['why'] as [$ic, $t, $d]): ?><div class="n-g rv"><span><?= $I[$ic] ?></span><h3><?= e($t) ?></h3><p><?= e($d) ?></p></div><?php endforeach; ?></div>
</section>

<section class="n-w n-sec">
  <div class="n-sh"><h2>كيف يبدأ اشتراككم</h2></div>
  <ol class="n-steps"><?php foreach ($K['steps'] as $i => [$t, $d]): ?><li class="rv"><b><bdi>0<?= $i + 1 ?></bdi></b><h3><?= e($t) ?></h3><p><?= e($d) ?></p></li><?php endforeach; ?></ol>
</section>

<section class="n-w n-sec n-narrow">
  <div class="n-sh"><h2>أسئلة تتكرر</h2><a href="<?= e($u('/faq')) ?>">كل الأسئلة <?= $I['arrow'] ?></a></div>
  <?= $K['faq'](array_slice($data['faq'], 0, 5), 'k-faq n-faq') ?>
</section>
<?= $close ?>

<?php elseif ($page === 'services'): ?>
<?= $ph('خدماتنا', $K['lead']['services'], [[null, 'خدماتنا']], 'team') ?>
<section class="n-w n-sec"><?= $rail() ?></section>
<?php foreach ($sections as $s): ?>
<section class="n-w n-sec n-grp" id="<?= e($s['slug']) ?>"><div class="n-sh"><h2><?= e($s['name']) ?></h2><a href="<?= e($u('/services/' . $s['slug'])) ?>">التفاصيل <?= $I['arrow'] ?></a></div>
  <div class="n-why"><?php foreach ($s['items'] as $it): ?><div class="n-g rv"><h3><?= e($it['title']) ?></h3><p><?= e($it['description']) ?></p></div><?php endforeach; ?></div></section>
<?php endforeach; ?>
<?= $close ?>

<?php elseif ($page === 'section'): ?>
<header class="n-ph"><?= $K['secPic']($sec, 'n-bg', false) ?><div class="n-w"><?= $crumbs([['/services', 'خدماتنا'], [null, $sec['name']]]) ?><h1><?= e($sec['name']) ?></h1><p><?= e($K['secLead']($sec)) ?></p>
  <div class="n-acts"><?= $K['quiz']('اسأل عن ' . $sec['name'], 'n-btn', $sec['name']) ?><?= $K['tel']('n-btn n-btn-g') ?></div></div></header>
<section class="n-w n-sec n-narrow">
  <?php foreach ($sec['items'] as $it): ?><article class="n-g n-svc rv"><h2><?= e($it['title']) ?></h2><p><?= e($it['description']) ?></p>
    <?php if ($it['includes']): ?><h3>تشمل</h3><ul class="n-ticks"><?php foreach ($it['includes'] as $x): ?><li><?= $I['check'] ?><?= e($x) ?></li><?php endforeach; ?></ul><?php endif; ?>
    <?php if ($it['suitable_for']): ?><h3>مناسبة لـ</h3><p><?= e(implode('، ', $it['suitable_for'])) ?></p><?php endif; ?></article><?php endforeach; ?>
  <div class="n-sh n-also"><h2>أقسام أخرى</h2></div>
  <div class="n-chips"><?php foreach ($sections as $s): if ($s['slug'] === $sec['slug']) continue; ?><a href="<?= e($u('/services/' . $s['slug'])) ?>"><?= e($s['name']) ?></a><?php endforeach; ?></div>
</section>
<?= $close ?>

<?php elseif ($page === 'about'): ?>
<?= $ph('من نحن', $K['lead']['about'], [[null, 'من نحن']], 'team') ?>
<section class="n-w n-sec n-narrow"><?php foreach ($K['about'] as [$t, $p]): ?><div class="n-g n-svc rv"><h2><?= e($t) ?></h2><p><?= e($p) ?></p></div><?php endforeach; ?>
  <div class="n-g n-vis rv"><img src="/img/vision-2030.png" alt="شعار رؤية المملكة 2030" width="560" height="375" loading="lazy"><div><h2>مع رؤية المملكة 2030</h2><p><?= e($K['vision']) ?></p></div></div></section>
<?= $close ?>

<?php elseif ($page === 'faq'): ?>
<?= $ph('الأسئلة الشائعة', $K['lead']['faq'], [[null, 'الأسئلة الشائعة']], 'majlis') ?>
<section class="n-w n-sec n-narrow"><?= $K['faq']($data['faq'], 'k-faq n-faq') ?></section>
<?= $close ?>

<?php elseif ($page === 'contact'): ?>
<?= $ph('تواصل معنا', $K['lead']['contact'], [[null, 'تواصل معنا']], 'doctor') ?>
<section class="n-w n-sec"><div class="n-plans">
  <a class="n-plan n-plan-hi n-ct" href="<?= e($wa) ?>" target="_blank" rel="noopener"><?= $I['wa'] ?><h3>واتساب</h3><p>اكتب لنا عن الحالة ونرد عليك</p><bdi dir="ltr"><?= e($shown) ?></bdi></a>
  <a class="n-plan n-ct" href="<?= e($tel) ?>"><?= $I['tel'] ?><h3>اتصال</h3><p>كلّمنا مباشرة على مدار الساعة</p><bdi dir="ltr"><?= e($shown) ?></bdi></a>
</div>
<div class="n-why n-mt"><div class="n-g"><span><?= $I['clock'] ?></span><h3>ساعات العمل</h3><p>على مدار الساعة، طوال أيام الأسبوع</p></div><div class="n-g"><span><?= $I['pin'] ?></span><h3>التغطية</h3><p>جميع مناطق المملكة</p></div><div class="n-g"><span><?= $I['cal'] ?></span><h3>الاشتراك</h3><p>من شهر واحد حتى سنة كاملة</p></div></div></section>

<?php else: ?>
<?= $ph('الصفحة غير موجودة', $K['lead']['notfound'], [[null, 'غير موجودة']], 'majlis') ?>
<section class="n-w n-sec"><a class="n-btn" href="<?= e($u('/')) ?>"><span>العودة للرئيسية</span></a></section>
<?php endif; ?>
</main>

<footer class="n-ft"><div class="n-w">
  <div class="n-ft-top"><a class="n-logo" href="<?= e($u('/')) ?>"><img src="/img/mark.png" alt="" width="180" height="155" loading="lazy"><b>أفنان الفائقة</b></a>
    <nav aria-label="الموقع"><?php foreach ($nav as [$h, $t]): ?><a href="<?= e($u($h)) ?>"><?= e($t) ?></a><?php endforeach; ?></nav></div>
  <div class="n-ft-mid"><nav aria-label="الخدمات"><?php foreach ($sections as $s): ?><a href="<?= e($u('/services/' . $s['slug'])) ?>"><?= e($s['name']) ?></a><?php endforeach; ?></nav>
    <div><?= $K['tel']('n-fl') ?><?= $K['wa']('واتساب', 'n-fl') ?></div></div>
  <?= $K['pay'] ?>
  <p class="n-copy">© <?= date('Y') ?> <?= e(BRAND) ?> للخدمات الطبية المنزلية · <?= e($K['tagline']) ?></p>
</div></footer>
<?= $K['tail'] ?>
</body></html>
