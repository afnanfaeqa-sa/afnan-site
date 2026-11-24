<?php
/* Theme «bold» (care7) — neo-brutalist: thick black outlines, hard offset shadows, sunflower yellow + black + coral,
   a moving word strip, sticker labels, giant numbered blocks. */
if (!isset($rep, $data, $I)) { http_response_code(404); exit; }
require __DIR__ . '/_kit.php';
$pic = $K['pic'];
$cols = ['#ffd84d', '#9ee6c4', '#ffb3a7', '#b9c8ff', '#ffd84d', '#9ee6c4'];
$cards = function () use ($sections, $u, $K, $cols): string {
    $h = '<div class="o-cards">';
    foreach ($sections as $i => $s) {
        $h .= '<a class="o-card rv" href="' . e($u('/services/' . $s['slug'])) . '" style="--c:' . $cols[$i] . '"><span class="o-img">' . $K['secPic']($s) . '</span><span class="o-stk">' . sprintf('%02d', $i + 1) . '</span><h3>' . e($s['name']) . '</h3><p>' . e($s['short']) . '</p></a>';
    }
    return $h . '</div>';
};
$strip = '<div class="o-strip" aria-hidden="true"><div>' . str_repeat('<span>تمريض منزلي</span><span>★</span><span>كبار السن</span><span>★</span><span>علاج طبيعي</span><span>★</span><span>اشتراك شهري أو سنوي</span><span>★</span><span>على مدار الساعة</span><span>★</span>', 2) . '</div></div>';
$ph = fn(string $t, string $lead, array $trail): string => '<header class="o-ph"><div class="o-w">' . $crumbs($trail) . '<h1>' . e($t) . '</h1><p>' . e($lead) . '</p></div></header>';
$close = '<section class="o-w o-sec"><div class="o-close rv"><h2>خلّنا نرتّب الرعاية!</h2><p>ثلاث أسئلة، وبعدها الفريق الطبي يكلمك على واتساب.</p><div class="o-acts">' . $K['quiz']('اطلب اشتراك', 'o-btn') . $K['tel']('o-btn o-btn-w') . '</div></div></section>';

echo $K['head']('bold', '#ffd84d', $K['fonts']('family=Lalezar&family=Rubik:wght@400;500;700'));
?>
<body class="o p-<?= e($page) ?>">
<header class="o-hdr"><div class="o-w o-hdr-in">
  <a class="o-logo" href="<?= e($u('/')) ?>"><img src="/img/mark.png" alt="" width="180" height="155"><b>أفنان الفائقة</b></a>
  <nav class="o-nav" aria-label="القائمة الرئيسية"><?php foreach ($nav as [$h, $t]): ?><a href="<?= e($u($h)) ?>"<?= $cur === $h ? ' aria-current="page"' : '' ?>><?= e($t) ?></a><?php endforeach; ?></nav>
  <?= $K['tel']('o-btn o-btn-w o-htel') ?>
  <?= $K['menuBtn']('o-menu') ?>
</div></header>

<main>
<?php if ($page === 'home'): ?>
<section class="o-w o-hero">
  <div class="o-hero-t">
    <span class="o-sticker">رعاية طبية منزلية</span>
    <h1 class="rv">الرعاية<br><span>تجي لين بيتك</span></h1>
    <p class="rv"><?= e($K['tagline']) ?></p>
    <div class="o-acts rv"><?= $K['quiz']('اطلب اشتراك', 'o-btn') ?><?= $K['wa']('واتساب', 'o-btn o-btn-w') ?></div>
  </div>
  <div class="o-hero-m rv"><?= $pic('physio', 'معالج طبيعي يساعد مريضًا في بيته', '', false) ?><span class="o-badge"><b><bdi><?= $staff ?>+</bdi></b>كادر مرخّص</span></div>
</section>
<?= $strip ?>

<section class="o-w o-sec"><h2 class="o-h">وش نقدّم؟</h2><?= $cards() ?></section>

<section class="o-w o-sec">
  <h2 class="o-h">اختر اشتراكك</h2>
  <div class="o-plans"><?php foreach ($K['plans'] as $i => [$v, $t, $s, $d]): ?><article class="o-plan rv" style="--c:<?= $i ? '#9ee6c4' : '#ffb3a7' ?>"><span class="o-sticker"><?= e($s) ?></span><h3><?= e($t) ?></h3><p><?= e($d) ?></p><?= $K['quiz']('ابدأ ' . $v, 'o-btn', '', $v) ?></article><?php endforeach; ?></div>
</section>

<section class="o-black"><div class="o-w o-sec">
  <h2 class="o-h o-h-y">ليش أفنان؟</h2>
  <div class="o-why"><?php foreach ($K['why'] as [$ic, $t, $d]): ?><div class="rv"><span><?= $I[$ic] ?></span><h3><?= e($t) ?></h3><p><?= e($d) ?></p></div><?php endforeach; ?></div>
</div></section>

<section class="o-w o-sec">
  <h2 class="o-h">أربع خطوات وخلاص</h2>
  <ol class="o-steps"><?php foreach ($K['steps'] as $i => [$t, $d]): ?><li class="rv"><b><?= $i + 1 ?></b><h3><?= e($t) ?></h3><p><?= e($d) ?></p></li><?php endforeach; ?></ol>
</section>

<section class="o-w o-sec o-narrow">
  <h2 class="o-h">أسئلة؟</h2>
  <?= $K['faq'](array_slice($data['faq'], 0, 5), 'k-faq o-faq') ?>
  <p><a class="o-lnk" href="<?= e($u('/faq')) ?>">كل الأسئلة ←</a></p>
</section>
<?= $close ?>

<?php elseif ($page === 'services'): ?>
<?= $ph('خدماتنا', $K['lead']['services'], [[null, 'خدماتنا']]) ?>
<section class="o-w o-sec"><?= $cards() ?></section>
<?php foreach ($sections as $i => $s): ?>
<section class="o-w o-sec o-grp" id="<?= e($s['slug']) ?>"><div class="o-box" style="--c:<?= $cols[$i] ?>"><h2><?= e($s['name']) ?></h2>
  <div class="o-items"><?php foreach ($s['items'] as $it): ?><div><b><?= e($it['title']) ?></b><p><?= e($it['description']) ?></p></div><?php endforeach; ?></div>
  <a class="o-lnk" href="<?= e($u('/services/' . $s['slug'])) ?>">التفاصيل ←</a></div></section>
<?php endforeach; ?>
<?= $close ?>

<?php elseif ($page === 'section'): ?>
<header class="o-ph"><div class="o-w o-hero o-hero-s"><div><?= $crumbs([['/services', 'خدماتنا'], [null, $sec['name']]]) ?><h1><?= e($sec['name']) ?></h1><p><?= e($K['secLead']($sec)) ?></p>
  <div class="o-acts"><?= $K['quiz']('اسأل عن ' . $sec['name'], 'o-btn', $sec['name']) ?><?= $K['tel']('o-btn o-btn-w') ?></div></div><div class="o-hero-m"><?= $K['secPic']($sec, '', false) ?></div></div></header>
<section class="o-w o-sec o-narrow">
  <?php foreach ($sec['items'] as $i => $it): ?><article class="o-box rv" style="--c:<?= $cols[$i % 6] ?>"><h2><?= e($it['title']) ?></h2><p><?= e($it['description']) ?></p>
    <?php if ($it['includes']): ?><h3>تشمل</h3><ul class="o-ticks"><?php foreach ($it['includes'] as $x): ?><li><?= $I['check'] ?><?= e($x) ?></li><?php endforeach; ?></ul><?php endif; ?>
    <?php if ($it['suitable_for']): ?><h3>مناسبة لـ</h3><p><?= e(implode('، ', $it['suitable_for'])) ?></p><?php endif; ?></article><?php endforeach; ?>
  <h2 class="o-h">أقسام ثانية</h2>
  <div class="o-tags"><?php foreach ($sections as $s): if ($s['slug'] === $sec['slug']) continue; ?><a href="<?= e($u('/services/' . $s['slug'])) ?>"><?= e($s['name']) ?></a><?php endforeach; ?></div>
</section>
<?= $close ?>

<?php elseif ($page === 'about'): ?>
<?= $ph('من نحن', $K['lead']['about'], [[null, 'من نحن']]) ?>
<section class="o-w o-sec o-narrow"><?php foreach ($K['about'] as $i => [$t, $p]): ?><div class="o-box rv" style="--c:<?= $cols[$i + 1] ?>"><h2><?= e($t) ?></h2><p><?= e($p) ?></p></div><?php endforeach; ?>
  <div class="o-box o-vis rv" style="--c:#fff"><img src="/img/vision-2030.png" alt="شعار رؤية المملكة 2030" width="560" height="375" loading="lazy"><div><h2>مع رؤية المملكة 2030</h2><p><?= e($K['vision']) ?></p></div></div></section>
<?= $close ?>

<?php elseif ($page === 'faq'): ?>
<?= $ph('الأسئلة الشائعة', $K['lead']['faq'], [[null, 'الأسئلة الشائعة']]) ?>
<section class="o-w o-sec o-narrow"><?= $K['faq']($data['faq'], 'k-faq o-faq') ?></section>
<?= $close ?>

<?php elseif ($page === 'contact'): ?>
<?= $ph('تواصل معنا', $K['lead']['contact'], [[null, 'تواصل معنا']]) ?>
<section class="o-w o-sec o-narrow"><div class="o-plans">
  <a class="o-plan o-ct" style="--c:#9ee6c4" href="<?= e($wa) ?>" target="_blank" rel="noopener"><?= $I['wa'] ?><h3>واتساب</h3><p>اكتب لنا عن الحالة</p><b><bdi dir="ltr"><?= e($shown) ?></bdi></b></a>
  <a class="o-plan o-ct" style="--c:#ffd84d" href="<?= e($tel) ?>"><?= $I['tel'] ?><h3>اتصال</h3><p>على مدار الساعة</p><b><bdi dir="ltr"><?= e($shown) ?></bdi></b></a>
</div>
<div class="o-tags o-mt"><span>على مدار الساعة</span><span>جميع مناطق المملكة</span><span>اشتراك من شهر حتى سنة</span></div></section>

<?php else: ?>
<?= $ph('الصفحة غير موجودة', $K['lead']['notfound'], [[null, 'غير موجودة']]) ?>
<section class="o-w o-sec"><a class="o-btn" href="<?= e($u('/')) ?>"><span>العودة للرئيسية</span></a></section>
<?php endif; ?>
</main>

<footer class="o-ft"><?= $strip ?><div class="o-w o-ft-in">
  <a class="o-logo" href="<?= e($u('/')) ?>"><img src="/img/mark.png" alt="" width="180" height="155" loading="lazy"><b>أفنان الفائقة</b></a>
  <div class="o-ft-g">
    <nav aria-label="الموقع"><?php foreach ($nav as [$h, $t]): ?><a href="<?= e($u($h)) ?>"><?= e($t) ?></a><?php endforeach; ?></nav>
    <nav aria-label="الخدمات"><?php foreach ($sections as $s): ?><a href="<?= e($u('/services/' . $s['slug'])) ?>"><?= e($s['name']) ?></a><?php endforeach; ?></nav>
    <div><?= $K['tel']('o-fl') ?><?= $K['wa']('واتساب', 'o-fl') ?></div>
  </div>
  <?= $K['pay'] ?>
  <p class="o-copy">© <?= date('Y') ?> <?= e(BRAND) ?> للخدمات الطبية المنزلية</p>
</div></footer>
<?= $K['tail'] ?>
</body></html>
