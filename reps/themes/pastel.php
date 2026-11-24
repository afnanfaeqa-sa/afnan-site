<?php
/* Theme «pastel» (care5) — soft and friendly: peach/mint/lavender, rounded blobs, circular photos, colourful
   tiles, chat-bubble FAQ, zig-zag steps. */
if (!isset($rep, $data, $I)) { http_response_code(404); exit; }
require __DIR__ . '/_kit.php';
$pic = $K['pic'];
$tones = ['p1', 'p2', 'p3', 'p4', 'p5', 'p6'];
$tiles = function () use ($sections, $u, $K, $tones): string {
    $h = '<div class="p-tiles">';
    foreach ($sections as $i => $s) {
        $h .= '<a class="p-tile ' . $tones[$i % 6] . ' rv" href="' . e($u('/services/' . $s['slug'])) . '"><span class="p-circ">' . $K['secPic']($s) . '</span><h3>' . e($s['name']) . '</h3><p>' . e($s['short']) . '</p></a>';
    }
    return $h . '</div>';
};
$ph = fn(string $title, string $lead, array $trail): string => '<header class="p-ph"><div class="p-w">' . $crumbs($trail) . '<h1>' . e($title) . '</h1><p>' . e($lead) . '</p></div></header>';
$bubbles = function (array $items): string {
    $h = '<div class="p-chat">';
    foreach ($items as $f) { $h .= '<details class="rv"><summary><span>' . e($f['q']) . '</span></summary><p>' . e($f['a']) . '</p></details>'; }
    return $h . '</div>';
};
$close = '<section class="p-w p-sec"><div class="p-close rv"><h2>نشيل عنكم هم الرعاية</h2><p>كلمونا عن الحالة، ونقترح لكم الخطة المناسبة.</p><div class="p-acts">' . $K['quiz']('اطلب اشتراك', 'p-btn') . $K['tel']('p-btn p-btn-l') . '</div></div></section>';

echo $K['head']('pastel', '#fff4ee', $K['fonts']('family=Baloo+Bhaijaan+2:wght@400;500;600;700;800'));
?>
<body class="p p-<?= e($page) ?>">
<header class="p-hdr"><div class="p-w p-hdr-in">
  <a class="p-logo" href="<?= e($u('/')) ?>"><span><img src="/img/mark.png" alt="" width="180" height="155"></span><b>أفنان الفائقة</b></a>
  <nav class="p-nav" aria-label="القائمة الرئيسية"><?php foreach ($nav as [$h, $t]): ?><a href="<?= e($u($h)) ?>"<?= $cur === $h ? ' aria-current="page"' : '' ?>><?= e($t) ?></a><?php endforeach; ?></nav>
  <?= $K['quiz']('اشترك', 'p-btn p-hbtn') ?>
  <?= $K['menuBtn']('p-menu') ?>
</div></header>

<main>
<?php if ($page === 'home'): ?>
<section class="p-w p-hero">
  <div class="p-hero-t">
    <span class="p-badge">رعاية منزلية باشتراك</span>
    <h1 class="rv">نهتم بأحبابكم<br>كأنهم <mark>أهلنا</mark></h1>
    <p class="rv">تمريض، ورعاية كبار السن، ورعاية الأم والمولود، وعلاج طبيعي في البيت. باشتراك من شهر حتى سنة، في جميع مناطق المملكة.</p>
    <div class="p-acts rv"><?= $K['quiz']('اطلب اشتراك', 'p-btn') ?><?= $K['wa']('واتساب', 'p-btn p-btn-l') ?></div>
  </div>
  <div class="p-collage rv" aria-hidden="true">
    <span class="p-c1"><?= $pic('baby', '', '', false) ?></span>
    <span class="p-c2"><?= $pic('walk') ?></span>
    <span class="p-c3"><?= $pic('physio') ?></span>
    <i class="p-b1"></i><i class="p-b2"></i>
  </div>
</section>

<section class="p-w p-sec">
  <div class="p-sh"><h2>بماذا نخدمكم؟</h2><p>ستة أقسام، اختاروا اللي يناسب حالتكم.</p></div>
  <?= $tiles() ?>
</section>

<section class="p-w p-sec">
  <div class="p-sh"><h2>شهري ولا سنوي؟</h2></div>
  <div class="p-tickets"><?php foreach ($K['plans'] as $i => [$v, $t, $s, $d]): ?>
    <article class="p-ticket <?= $i ? 'p3' : 'p2' ?> rv"><h3><?= e($t) ?></h3><b><?= e($s) ?></b><p><?= e($d) ?></p><?= $K['quiz']('أبي ' . $v, 'p-btn', '', $v) ?></article>
  <?php endforeach; ?></div>
</section>

<section class="p-wave"><div class="p-w p-sec">
  <div class="p-sh"><h2>ليش تختارون أفنان؟</h2></div>
  <div class="p-why"><?php foreach ($K['why'] as $i => [$ic, $t, $d]): ?><div class="rv"><span class="<?= $tones[$i] ?>"><?= $I[$ic] ?></span><h3><?= e($t) ?></h3><p><?= e($d) ?></p></div><?php endforeach; ?></div>
</div></section>

<section class="p-w p-sec">
  <div class="p-sh"><h2>خطوات بسيطة</h2></div>
  <ol class="p-zig"><?php foreach ($K['steps'] as $i => [$t, $d]): ?><li class="<?= $tones[$i + 1] ?> rv"><span><?= $i + 1 ?></span><div><h3><?= e($t) ?></h3><p><?= e($d) ?></p></div></li><?php endforeach; ?></ol>
</section>

<section class="p-w p-sec p-narrow">
  <div class="p-sh"><h2>تسألون ونجاوب</h2></div>
  <?= $bubbles(array_slice($data['faq'], 0, 5)) ?>
  <p class="p-center"><a class="p-link" href="<?= e($u('/faq')) ?>">كل الأسئلة</a></p>
</section>
<?= $close ?>

<?php elseif ($page === 'services'): ?>
<?= $ph('خدماتنا', $K['lead']['services'], [[null, 'خدماتنا']]) ?>
<section class="p-w p-sec"><?= $tiles() ?></section>
<?php foreach ($sections as $i => $s): ?>
<section class="p-w p-sec p-grp" id="<?= e($s['slug']) ?>"><div class="p-box <?= $tones[$i % 6] ?>"><h2><?= e($s['name']) ?></h2>
  <ul><?php foreach ($s['items'] as $it): ?><li><b><?= e($it['title']) ?></b> — <?= e($it['description']) ?></li><?php endforeach; ?></ul>
  <a class="p-link" href="<?= e($u('/services/' . $s['slug'])) ?>">التفاصيل</a></div></section>
<?php endforeach; ?>
<?= $close ?>

<?php elseif ($page === 'section'): ?>
<header class="p-ph p-ph-s"><div class="p-w p-ph-g"><div><?= $crumbs([['/services', 'خدماتنا'], [null, $sec['name']]]) ?><h1><?= e($sec['name']) ?></h1><p><?= e($K['secLead']($sec)) ?></p>
  <div class="p-acts"><?= $K['quiz']('اسأل عن ' . $sec['name'], 'p-btn', $sec['name']) ?><?= $K['tel']('p-btn p-btn-l') ?></div></div><span class="p-big-c"><?= $K['secPic']($sec, '', false) ?></span></div></header>
<section class="p-w p-sec p-narrow">
  <?php foreach ($sec['items'] as $i => $it): ?><article class="p-box <?= $tones[$i % 6] ?> rv"><h2><?= e($it['title']) ?></h2><p><?= e($it['description']) ?></p>
    <?php if ($it['includes']): ?><h3>تشمل</h3><ul class="p-ticks"><?php foreach ($it['includes'] as $x): ?><li><?= $I['check'] ?><?= e($x) ?></li><?php endforeach; ?></ul><?php endif; ?>
    <?php if ($it['suitable_for']): ?><h3>مناسبة لـ</h3><p><?= e(implode('، ', $it['suitable_for'])) ?></p><?php endif; ?></article><?php endforeach; ?>
  <div class="p-sh"><h2>أقسام أخرى</h2></div>
  <div class="p-pills"><?php foreach ($sections as $s): if ($s['slug'] === $sec['slug']) continue; ?><a href="<?= e($u('/services/' . $s['slug'])) ?>"><?= e($s['name']) ?></a><?php endforeach; ?></div>
</section>
<?= $close ?>

<?php elseif ($page === 'about'): ?>
<?= $ph('من نحن', $K['lead']['about'], [[null, 'من نحن']]) ?>
<section class="p-w p-sec p-narrow"><?php foreach ($K['about'] as $i => [$t, $p]): ?><div class="p-box <?= $tones[$i + 2] ?> rv"><h2><?= e($t) ?></h2><p><?= e($p) ?></p></div><?php endforeach; ?>
  <div class="p-box p-vis rv"><img src="/img/vision-2030.png" alt="شعار رؤية المملكة 2030" width="560" height="375" loading="lazy"><div><h2>مع رؤية المملكة 2030</h2><p><?= e($K['vision']) ?></p></div></div></section>
<?= $close ?>

<?php elseif ($page === 'faq'): ?>
<?= $ph('الأسئلة الشائعة', $K['lead']['faq'], [[null, 'الأسئلة الشائعة']]) ?>
<section class="p-w p-sec p-narrow"><?= $bubbles($data['faq']) ?></section>
<?= $close ?>

<?php elseif ($page === 'contact'): ?>
<?= $ph('تواصل معنا', $K['lead']['contact'], [[null, 'تواصل معنا']]) ?>
<section class="p-w p-sec p-narrow">
  <div class="p-tickets">
    <a class="p-ticket p2 p-ct" href="<?= e($wa) ?>" target="_blank" rel="noopener"><?= $I['wa'] ?><h3>واتساب</h3><p>اكتبوا لنا عن الحالة</p><b><bdi dir="ltr"><?= e($shown) ?></bdi></b></a>
    <a class="p-ticket p4 p-ct" href="<?= e($tel) ?>"><?= $I['tel'] ?><h3>اتصال</h3><p>كلمونا مباشرة</p><b><bdi dir="ltr"><?= e($shown) ?></bdi></b></a>
  </div>
  <div class="p-why p-why3"><div><span class="p1"><?= $I['clock'] ?></span><h3>على مدار الساعة</h3><p>طوال أيام الأسبوع</p></div><div><span class="p3"><?= $I['pin'] ?></span><h3>جميع المناطق</h3><p>في كل المملكة</p></div><div><span class="p5"><?= $I['cal'] ?></span><h3>الاشتراك</h3><p>من شهر حتى سنة</p></div></div>
</section>

<?php else: ?>
<?= $ph('الصفحة غير موجودة', $K['lead']['notfound'], [[null, 'غير موجودة']]) ?>
<section class="p-w p-sec"><a class="p-btn" href="<?= e($u('/')) ?>"><span>العودة للرئيسية</span></a></section>
<?php endif; ?>
</main>

<footer class="p-ft"><div class="p-w">
  <a class="p-logo" href="<?= e($u('/')) ?>"><span><img src="/img/mark.png" alt="" width="180" height="155" loading="lazy"></span><b>أفنان الفائقة</b></a>
  <p><?= e(BRAND) ?> للخدمات الطبية المنزلية. <?= e($K['tagline']) ?></p>
  <nav class="p-pills" aria-label="الخدمات"><?php foreach ($sections as $s): ?><a href="<?= e($u('/services/' . $s['slug'])) ?>"><?= e($s['name']) ?></a><?php endforeach; ?></nav>
  <nav class="p-fnav" aria-label="الموقع"><?php foreach ($nav as [$h, $t]): ?><a href="<?= e($u($h)) ?>"><?= e($t) ?></a><?php endforeach; ?><?= $K['tel']('') ?></nav>
  <?= $K['pay'] ?>
  <p class="p-copy">© <?= date('Y') ?> <?= e(BRAND) ?></p>
</div></footer>
<?= $K['tail'] ?>
</body></html>
