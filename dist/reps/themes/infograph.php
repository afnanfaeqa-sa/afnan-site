<?php
/* Theme «infograph» (care13) — an infographic: giant figures, SVG rings and bars drawn from real facts only
   (staff count, 24/7, 1–12 months, 6 sections), chevron flow for the steps. Navy + signal red, Changa. */
if (!isset($rep, $data, $I)) { http_response_code(404); exit; }
require __DIR__ . '/_kit.php';
$pic = $K['pic'];
$ring = fn(string $big, string $small, float $frac): string => '<figure class="f-ring"><svg viewBox="0 0 120 120" aria-hidden="true"><circle cx="60" cy="60" r="52" class="f-tr"/><circle cx="60" cy="60" r="52" class="f-fg" style="stroke-dasharray:' . round(326.7 * $frac, 1) . ' 326.7"/></svg><figcaption><b><bdi>' . e($big) . '</bdi></b><span>' . e($small) . '</span></figcaption></figure>';
$bars = function () use ($sections, $u): string {
    $h = '<div class="f-bars">';
    foreach ($sections as $i => $s) {
        $n = count($s['items']);
        $h .= '<a class="f-bar rv" href="' . e($u('/services/' . $s['slug'])) . '"><span class="f-bn">' . sprintf('%02d', $i + 1) . '</span><span class="f-bt"><b>' . e($s['name']) . '</b><small>' . e($s['short']) . '</small></span><span class="f-bv"><i style="width:' . (25 * min($n, 4)) . '%"></i></span><span class="f-bc">' . ($n === 1 ? 'خدمة واحدة' : ($n === 2 ? 'خدمتان' : '<bdi>' . $n . '</bdi> خدمات')) . '</span></a>';
    }
    return $h . '</div>';
};
$ph = fn(string $t, string $lead, array $trail): string => '<header class="f-ph"><div class="f-w">' . $crumbs($trail) . '<h1>' . e($t) . '</h1><p>' . e($lead) . '</p></div></header>';
$close = '<section class="f-close"><div class="f-w f-close-in rv"><div><span class="f-k">الخطوة التالية</span><h2>٣ أسئلة ← خطة رعاية</h2></div><div class="f-acts">' . $K['quiz']('اطلب اشتراك', 'f-btn') . $K['tel']('f-btn f-btn-o') . '</div></div></section>';

echo $K['head']('infograph', '#0c1b3a', $K['fonts']('family=Changa:wght@300;400;500;600;700;800'));
?>
<body class="f p-<?= e($page) ?>">
<header class="f-hdr"><div class="f-w f-hdr-in">
  <a class="f-logo" href="<?= e($u('/')) ?>"><img src="/img/mark.png" alt="" width="180" height="155"><b>أفنان الفائقة</b></a>
  <nav class="f-nav" aria-label="القائمة الرئيسية"><?php foreach ($nav as [$h, $t]): ?><a href="<?= e($u($h)) ?>"<?= $cur === $h ? ' aria-current="page"' : '' ?>><?= e($t) ?></a><?php endforeach; ?></nav>
  <?= $K['tel']('f-htel') ?>
  <?= $K['menuBtn']('f-menu') ?>
</div></header>

<main>
<?php if ($page === 'home'): ?>
<section class="f-hero"><div class="f-w f-hero-g">
  <div>
    <span class="f-k">رعاية طبية منزلية · بالأرقام</span>
    <h1 class="rv">رعاية في البيت<br><em>على مدار الساعة</em></h1>
    <p class="rv"><?= e($K['tagline']) ?></p>
    <div class="f-acts rv"><?= $K['quiz']('اطلب اشتراك', 'f-btn') ?><?= $K['wa']('واتساب', 'f-btn f-btn-o') ?></div>
  </div>
  <div class="f-rings rv"><?= $ring($staff . '+', 'كادر صحي مرخّص', 1) ?><?= $ring('24/7', 'متابعة مستمرة', 1) ?><?= $ring('1–12', 'شهرًا مدة الاشتراك', 1) ?><?= $ring('6', 'أقسام رعاية', 1) ?></div>
</div></section>

<section class="f-w f-sec"><div class="f-sh"><span class="f-k">01 · الخدمات</span><h2>ستة أقسام، وعدد الخدمات في كل قسم</h2></div><?= $bars() ?></section>

<section class="f-w f-sec">
  <div class="f-sh"><span class="f-k">02 · الاشتراك</span><h2>شهري أو سنوي</h2></div>
  <div class="f-tl rv" aria-hidden="true"><?php for ($m = 1; $m <= 12; $m++): ?><span class="<?= $m <= 3 ? 'a' : 'b' ?>"><bdi><?= $m ?></bdi></span><?php endfor; ?></div>
  <div class="f-plans"><?php foreach ($K['plans'] as $i => [$v, $t, $s, $d]): ?><div class="f-plan f-plan<?= $i ?> rv"><h3><?= e($t) ?></h3><b><?= e($s) ?></b><p><?= e($d) ?></p><?= $K['quiz']('ابدأ ' . $v, $i ? 'f-btn' : 'f-btn f-btn-o', '', $v) ?></div><?php endforeach; ?></div>
</section>

<section class="f-navy"><div class="f-w f-sec">
  <div class="f-sh"><span class="f-k">03 · الخطوات</span><h2>من الرسالة إلى الرعاية</h2></div>
  <ol class="f-flow"><?php foreach ($K['steps'] as $i => [$t, $d]): ?><li class="rv"><b><?= $i + 1 ?></b><h3><?= e($t) ?></h3><p><?= e($d) ?></p></li><?php endforeach; ?></ol>
</div></section>

<section class="f-w f-sec">
  <div class="f-sh"><span class="f-k">04 · المميزات</span><h2>لماذا أفنان</h2></div>
  <div class="f-why"><?php foreach ($K['why'] as [$ic, $t, $d]): ?><div class="rv"><span><?= $I[$ic] ?></span><h3><?= e($t) ?></h3><p><?= e($d) ?></p></div><?php endforeach; ?></div>
</section>

<section class="f-w f-sec f-faqw">
  <div class="f-sh"><span class="f-k">05 · أسئلة</span><h2>أسئلة تتكرر</h2><a href="<?= e($u('/faq')) ?>">كل الأسئلة</a></div>
  <?= $K['faq'](array_slice($data['faq'], 0, 5), 'k-faq f-faq') ?>
</section>
<?= $close ?>

<?php elseif ($page === 'services'): ?>
<?= $ph('خدماتنا', $K['lead']['services'], [[null, 'خدماتنا']]) ?>
<section class="f-w f-sec"><?= $bars() ?></section>
<?php foreach ($sections as $i => $s): ?>
<section class="f-w f-sec f-grp" id="<?= e($s['slug']) ?>"><div class="f-sh"><span class="f-k"><?= sprintf('%02d', $i + 1) ?></span><h2><?= e($s['name']) ?></h2><a href="<?= e($u('/services/' . $s['slug'])) ?>">التفاصيل</a></div>
  <div class="f-tbl"><?php foreach ($s['items'] as $it): ?><div class="rv"><b><?= e($it['title']) ?></b><span><?= e($it['description']) ?></span></div><?php endforeach; ?></div></section>
<?php endforeach; ?>
<?= $close ?>

<?php elseif ($page === 'section'): ?>
<header class="f-ph"><div class="f-w f-ph-g"><div><?= $crumbs([['/services', 'خدماتنا'], [null, $sec['name']]]) ?><h1><?= e($sec['name']) ?></h1><p><?= e($K['secLead']($sec)) ?></p>
  <div class="f-acts"><?= $K['quiz']('اسأل عن ' . $sec['name'], 'f-btn', $sec['name']) ?><?= $K['tel']('f-btn f-btn-o') ?></div></div>
  <div class="f-ph-m"><?= $K['secPic']($sec, '', false) ?><span class="f-tag"><?php $n = count($sec['items']); echo $n === 1 ? 'خدمة واحدة' : ($n === 2 ? 'خدمتان' : '<bdi>' . $n . '</bdi> خدمات'); ?></span></div></div></header>
<section class="f-w f-sec">
  <?php foreach ($sec['items'] as $i => $it): ?><article class="f-card rv"><span class="f-cn"><?= sprintf('%02d', $i + 1) ?></span><div><h2><?= e($it['title']) ?></h2><p><?= e($it['description']) ?></p>
    <?php if ($it['includes']): ?><h3>تشمل (<bdi><?= count($it['includes']) ?></bdi>)</h3><ul class="f-ticks"><?php foreach ($it['includes'] as $x): ?><li><?= $I['check'] ?><?= e($x) ?></li><?php endforeach; ?></ul><?php endif; ?>
    <?php if ($it['suitable_for']): ?><h3>مناسبة لـ</h3><p><?= e(implode('، ', $it['suitable_for'])) ?></p><?php endif; ?></div></article><?php endforeach; ?>
  <div class="f-sh f-mt"><span class="f-k">تصفّح</span><h2>أقسام أخرى</h2></div>
  <div class="f-chips"><?php foreach ($sections as $s): if ($s['slug'] === $sec['slug']) continue; ?><a href="<?= e($u('/services/' . $s['slug'])) ?>"><?= e($s['name']) ?></a><?php endforeach; ?></div>
</section>
<?= $close ?>

<?php elseif ($page === 'about'): ?>
<?= $ph('من نحن', $K['lead']['about'], [[null, 'من نحن']]) ?>
<section class="f-w f-sec"><div class="f-rings f-rings-w"><?= $ring($staff . '+', 'كادر صحي مرخّص', 1) ?><?= $ring('24/7', 'على مدار الساعة', 1) ?><?= $ring('1–12', 'شهرًا مدة الاشتراك', 1) ?><?= $ring('6', 'أقسام رعاية', 1) ?></div></section>
<section class="f-w f-sec"><?php foreach ($K['about'] as $i => [$t, $p]): ?><article class="f-card rv"><span class="f-cn"><?= sprintf('%02d', $i + 1) ?></span><div><h2><?= e($t) ?></h2><p><?= e($p) ?></p></div></article><?php endforeach; ?>
  <article class="f-card f-vis rv"><img src="/img/vision-2030.png" alt="شعار رؤية المملكة 2030" width="560" height="375" loading="lazy"><div><h2>مع رؤية المملكة 2030</h2><p><?= e($K['vision']) ?></p></div></article></section>
<?= $close ?>

<?php elseif ($page === 'faq'): ?>
<?= $ph('الأسئلة الشائعة', $K['lead']['faq'], [[null, 'الأسئلة الشائعة']]) ?>
<section class="f-w f-sec f-faqw"><?= $K['faq']($data['faq'], 'k-faq f-faq') ?></section>
<?= $close ?>

<?php elseif ($page === 'contact'): ?>
<?= $ph('تواصل معنا', $K['lead']['contact'], [[null, 'تواصل معنا']]) ?>
<section class="f-w f-sec"><div class="f-plans">
  <a class="f-plan f-plan1 f-ct" href="<?= e($wa) ?>" target="_blank" rel="noopener"><?= $I['wa'] ?><h3>واتساب</h3><b><bdi dir="ltr"><?= e($shown) ?></bdi></b><p>اكتب لنا عن الحالة ونرد عليك</p></a>
  <a class="f-plan f-plan0 f-ct" href="<?= e($tel) ?>"><?= $I['tel'] ?><h3>اتصال</h3><b><bdi dir="ltr"><?= e($shown) ?></bdi></b><p>كلّمنا مباشرة</p></a>
</div>
<div class="f-rings f-rings-w f-mt"><?= $ring('24/7', 'ساعات العمل', 1) ?><?= $ring('KSA', 'جميع مناطق المملكة', 1) ?><?= $ring('1–12', 'شهرًا مدة الاشتراك', 1) ?></div></section>

<?php else: ?>
<?= $ph('الصفحة غير موجودة', $K['lead']['notfound'], [[null, 'غير موجودة']]) ?>
<section class="f-w f-sec"><a class="f-btn" href="<?= e($u('/')) ?>"><span>العودة للرئيسية</span></a></section>
<?php endif; ?>
</main>

<footer class="f-ft"><div class="f-w">
  <div class="f-ft-g">
    <div><a class="f-logo" href="<?= e($u('/')) ?>"><img src="/img/mark.png" alt="" width="180" height="155" loading="lazy"><b>أفنان الفائقة</b></a><p><?= e($K['tagline']) ?></p></div>
    <nav aria-label="الموقع"><?php foreach ($nav as [$h, $t]): ?><a href="<?= e($u($h)) ?>"><?= e($t) ?></a><?php endforeach; ?></nav>
    <nav aria-label="الخدمات"><?php foreach ($sections as $s): ?><a href="<?= e($u('/services/' . $s['slug'])) ?>"><?= e($s['name']) ?></a><?php endforeach; ?></nav>
    <div><?= $K['tel']('f-fl') ?><?= $K['wa']('واتساب', 'f-fl') ?></div>
  </div>
  <?= $K['pay'] ?>
  <p class="f-copy">© <?= date('Y') ?> <?= e(BRAND) ?> للخدمات الطبية المنزلية</p>
</div></footer>
<?= $K['tail'] ?>
</body></html>
