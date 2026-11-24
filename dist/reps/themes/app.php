<?php
/* Theme «app» (care10) — feels like a mobile app: a phone mock-up in the hero, app-icon service tiles, widget cards,
   a progress tracker, and a bottom tab bar on phones. Blue→cyan gradient, IBM Plex Sans Arabic. */
if (!isset($rep, $data, $I)) { http_response_code(404); exit; }
require __DIR__ . '/_kit.php';
$pic = $K['pic'];
$icons = function (string $cls = 'a-icons') use ($sections, $u, $K): string {
    $h = '<div class="' . $cls . '">';
    foreach ($sections as $s) { $h .= '<a class="a-ic rv" href="' . e($u('/services/' . $s['slug'])) . '"><span>' . $K['secPic']($s) . '</span><b>' . e($s['name']) . '</b><small>' . e($s['short']) . '</small></a>'; }
    return $h . '</div>';
};
$ph = fn(string $t, string $lead, array $trail): string => '<header class="a-ph"><div class="a-w">' . $crumbs($trail) . '<h1>' . e($t) . '</h1><p>' . e($lead) . '</p></div></header>';
$close = '<section class="a-w a-sec"><div class="a-close rv"><div><h2>جاهز تطلب؟</h2><p>ثلاث خطوات داخل نافذة واحدة، وبعدها واتساب برسالة جاهزة.</p></div><div class="a-acts">' . $K['quiz']('اطلب اشتراك', 'a-btn a-btn-w') . $K['tel']('a-btn a-btn-g') . '</div></div></section>';

echo $K['head']('app', '#2563eb', $K['fonts']('family=IBM+Plex+Sans+Arabic:wght@400;500;600;700'));
?>
<body class="a p-<?= e($page) ?>">
<header class="a-hdr"><div class="a-w a-hdr-in">
  <a class="a-logo" href="<?= e($u('/')) ?>"><span><img src="/img/mark.png" alt="" width="180" height="155"></span><b>أفنان الفائقة</b></a>
  <nav class="a-nav" aria-label="القائمة الرئيسية"><?php foreach ($nav as [$h, $t]): ?><a href="<?= e($u($h)) ?>"<?= $cur === $h ? ' aria-current="page"' : '' ?>><?= e($t) ?></a><?php endforeach; ?></nav>
  <?= $K['tel']('a-htel') ?>
  <?= $K['menuBtn']('a-menu') ?>
</div></header>

<main>
<?php if ($page === 'home'): ?>
<section class="a-hero"><div class="a-w a-hero-g">
  <div>
    <span class="a-chip"><i></i> متاح الآن · على مدار الساعة</span>
    <h1 class="rv">رعايتك الطبية<br>بضغطة زر</h1>
    <p class="rv"><?= e($K['tagline']) ?> اختر الخدمة والمدة، ويتواصل معك الفريق الطبي على واتساب.</p>
    <div class="a-acts rv"><?= $K['quiz']('اطلب اشتراك', 'a-btn a-btn-w') ?><?= $K['wa']('واتساب', 'a-btn a-btn-g') ?></div>
  </div>
  <div class="a-phone rv" aria-hidden="true"><div class="a-scr">
    <div class="a-scr-top"><b>أهلًا بك</b><small>وش تحتاج اليوم؟</small></div>
    <div class="a-scr-card"><?= $pic('wound', '', '', false) ?><div><b>التمريض المنزلي</b><small>ممرضة مقيمة ورعاية مستمرة</small></div></div>
    <div class="a-scr-row"><span>كبار السن</span><span>علاج طبيعي</span><span>الأم والمولود</span></div>
    <div class="a-scr-btn">اطلب اشتراك</div>
  </div></div>
</div></section>

<section class="a-w a-sec a-widgets">
  <div class="a-wd rv"><small>الكادر</small><b><bdi><?= $staff ?>+</bdi></b><span>كادر صحي مرخّص</span></div>
  <div class="a-wd rv"><small>المتابعة</small><b><bdi>24/7</bdi></b><span>على مدار الساعة</span></div>
  <div class="a-wd rv"><small>الاشتراك</small><b><bdi>1–12</bdi></b><span>شهرًا</span></div>
  <div class="a-wd rv"><small>التغطية</small><b>المملكة</b><span>جميع المناطق</span></div>
</section>

<section class="a-w a-sec"><div class="a-sh"><h2>الخدمات</h2><a href="<?= e($u('/services')) ?>">عرض الكل</a></div><?= $icons() ?></section>

<section class="a-w a-sec">
  <div class="a-sh"><h2>الاشتراكات</h2></div>
  <div class="a-plans"><?php foreach ($K['plans'] as $i => [$v, $t, $s, $d]): ?><div class="a-plan<?= $i ? ' a-plan-hi' : '' ?> rv"><span class="a-tag"><?= e($s) ?></span><h3><?= e($t) ?></h3><p><?= e($d) ?></p><?= $K['quiz']('اختر ' . $v, $i ? 'a-btn a-btn-w' : 'a-btn', '', $v) ?></div><?php endforeach; ?></div>
</section>

<section class="a-w a-sec">
  <div class="a-sh"><h2>حالة طلبك</h2></div>
  <ol class="a-track"><?php foreach ($K['steps'] as $i => [$t, $d]): ?><li class="rv<?= $i === 0 ? ' on' : '' ?>"><span><?= $I['check'] ?></span><div><b><?= e($t) ?></b><small><?= e($d) ?></small></div></li><?php endforeach; ?></ol>
</section>

<section class="a-w a-sec">
  <div class="a-sh"><h2>المميزات</h2></div>
  <div class="a-feat"><?php foreach ($K['why'] as [$ic, $t, $d]): ?><div class="rv"><span><?= $I[$ic] ?></span><b><?= e($t) ?></b><small><?= e($d) ?></small></div><?php endforeach; ?></div>
</section>

<section class="a-w a-sec">
  <div class="a-sh"><h2>المساعدة</h2><a href="<?= e($u('/faq')) ?>">كل الأسئلة</a></div>
  <?= $K['faq'](array_slice($data['faq'], 0, 5), 'k-faq a-faq') ?>
</section>
<?= $close ?>

<?php elseif ($page === 'services'): ?>
<?= $ph('خدماتنا', $K['lead']['services'], [[null, 'خدماتنا']]) ?>
<section class="a-w a-sec"><?= $icons() ?></section>
<?php foreach ($sections as $s): ?>
<section class="a-w a-sec a-grp" id="<?= e($s['slug']) ?>"><div class="a-sh"><h2><?= e($s['name']) ?></h2><a href="<?= e($u('/services/' . $s['slug'])) ?>">التفاصيل</a></div>
  <div class="a-list"><?php foreach ($s['items'] as $it): ?><div class="rv"><b><?= e($it['title']) ?></b><small><?= e($it['description']) ?></small></div><?php endforeach; ?></div></section>
<?php endforeach; ?>
<?= $close ?>

<?php elseif ($page === 'section'): ?>
<header class="a-ph"><div class="a-w a-ph-g"><div><?= $crumbs([['/services', 'خدماتنا'], [null, $sec['name']]]) ?><h1><?= e($sec['name']) ?></h1><p><?= e($K['secLead']($sec)) ?></p>
  <div class="a-acts"><?= $K['quiz']('اسأل عن ' . $sec['name'], 'a-btn a-btn-w', $sec['name']) ?><?= $K['tel']('a-btn a-btn-g') ?></div></div><div class="a-phimg"><?= $K['secPic']($sec, '', false) ?></div></div></header>
<section class="a-w a-sec">
  <?php foreach ($sec['items'] as $it): ?><article class="a-card rv"><h2><?= e($it['title']) ?></h2><p><?= e($it['description']) ?></p>
    <?php if ($it['includes']): ?><h3>تشمل</h3><ul class="a-ticks"><?php foreach ($it['includes'] as $x): ?><li><?= $I['check'] ?><?= e($x) ?></li><?php endforeach; ?></ul><?php endif; ?>
    <?php if ($it['suitable_for']): ?><h3>مناسبة لـ</h3><div class="a-chips"><?php foreach ($it['suitable_for'] as $x): ?><span><?= e($x) ?></span><?php endforeach; ?></div><?php endif; ?></article><?php endforeach; ?>
  <div class="a-sh a-mt"><h2>أقسام أخرى</h2></div>
  <?php ob_start(); foreach ($sections as $s) { if ($s['slug'] !== $sec['slug']) echo '<a class="a-ic" href="' . e($u('/services/' . $s['slug'])) . '"><span>' . $K['secPic']($s) . '</span><b>' . e($s['name']) . '</b></a>'; } $o = ob_get_clean(); ?>
  <div class="a-icons a-icons-s"><?= $o ?></div>
</section>
<?= $close ?>

<?php elseif ($page === 'about'): ?>
<?= $ph('من نحن', $K['lead']['about'], [[null, 'من نحن']]) ?>
<section class="a-w a-sec"><?php foreach ($K['about'] as [$t, $p]): ?><article class="a-card rv"><h2><?= e($t) ?></h2><p><?= e($p) ?></p></article><?php endforeach; ?>
  <article class="a-card a-vis rv"><img src="/img/vision-2030.png" alt="شعار رؤية المملكة 2030" width="560" height="375" loading="lazy"><div><h2>مع رؤية المملكة 2030</h2><p><?= e($K['vision']) ?></p></div></article></section>
<?= $close ?>

<?php elseif ($page === 'faq'): ?>
<?= $ph('الأسئلة الشائعة', $K['lead']['faq'], [[null, 'الأسئلة الشائعة']]) ?>
<section class="a-w a-sec"><?= $K['faq']($data['faq'], 'k-faq a-faq') ?></section>
<?= $close ?>

<?php elseif ($page === 'contact'): ?>
<?= $ph('تواصل معنا', $K['lead']['contact'], [[null, 'تواصل معنا']]) ?>
<section class="a-w a-sec">
  <div class="a-plans">
    <a class="a-plan a-plan-hi a-ct" href="<?= e($wa) ?>" target="_blank" rel="noopener"><?= $I['wa'] ?><h3>واتساب</h3><p>اكتب لنا عن الحالة ونرد عليك</p><b><bdi dir="ltr"><?= e($shown) ?></bdi></b></a>
    <a class="a-plan a-ct" href="<?= e($tel) ?>"><?= $I['tel'] ?><h3>اتصال</h3><p>كلّمنا مباشرة</p><b><bdi dir="ltr"><?= e($shown) ?></bdi></b></a>
  </div>
  <div class="a-widgets a-mt"><div class="a-wd"><small>ساعات العمل</small><b>24/7</b><span>طوال الأسبوع</span></div><div class="a-wd"><small>التغطية</small><b>المملكة</b><span>جميع المناطق</span></div><div class="a-wd"><small>الاشتراك</small><b><bdi>1–12</bdi></b><span>شهرًا</span></div></div>
</section>

<?php else: ?>
<?= $ph('الصفحة غير موجودة', $K['lead']['notfound'], [[null, 'غير موجودة']]) ?>
<section class="a-w a-sec"><a class="a-btn" href="<?= e($u('/')) ?>"><span>العودة للرئيسية</span></a></section>
<?php endif; ?>
</main>

<footer class="a-ft"><div class="a-w">
  <div class="a-ft-g">
    <div><a class="a-logo" href="<?= e($u('/')) ?>"><span><img src="/img/mark.png" alt="" width="180" height="155" loading="lazy"></span><b>أفنان الفائقة</b></a><p><?= e($K['tagline']) ?></p></div>
    <nav aria-label="الموقع"><?php foreach ($nav as [$h, $t]): ?><a href="<?= e($u($h)) ?>"><?= e($t) ?></a><?php endforeach; ?></nav>
    <nav aria-label="الخدمات"><?php foreach ($sections as $s): ?><a href="<?= e($u('/services/' . $s['slug'])) ?>"><?= e($s['name']) ?></a><?php endforeach; ?></nav>
  </div>
  <?= $K['pay'] ?>
  <p class="a-copy">© <?= date('Y') ?> <?= e(BRAND) ?> للخدمات الطبية المنزلية</p>
</div></footer>

<nav class="a-tabs" aria-label="اختصارات">
  <a href="<?= e($u('/')) ?>"<?= $cur === '/' ? ' aria-current="page"' : '' ?>><?= $I['team'] ?><span>الرئيسية</span></a>
  <a href="<?= e($u('/services')) ?>"<?= $cur === '/services' ? ' aria-current="page"' : '' ?>><?= $I['shield'] ?><span>الخدمات</span></a>
  <?= $K['quiz']('اطلب', 'a-tab-go') ?>
  <a href="<?= e($wa) ?>" target="_blank" rel="noopener"><?= $I['wa'] ?><span>واتساب</span></a>
  <a href="<?= e($tel) ?>"><?= $I['tel'] ?><span>اتصال</span></a>
</nav>
<?= $K['tail'] ?>
</body></html>
