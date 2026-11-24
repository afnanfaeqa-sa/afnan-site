<?php
/* Theme «journey» (care9) — the whole page is a vertical journey line; each block is a station on it with a numbered
   node. Lavender/violet, Readex Pro, centred hero with a ring photo. */
if (!isset($rep, $data, $I)) { http_response_code(404); exit; }
require __DIR__ . '/_kit.php';
$pic = $K['pic'];
$station = function (int $n, string $label, string $title, string $body, string $cls = '') : string {
    return '<section class="j-st ' . $cls . '"><span class="j-node" aria-hidden="true">' . $n . '</span><div class="j-st-in rv"><p class="j-lbl">' . e($label) . '</p><h2>' . e($title) . '</h2>' . $body . '</div></section>';
};
$grid = function () use ($sections, $u, $K): string {
    $h = '<div class="j-grid">';
    foreach ($sections as $s) { $h .= '<a class="j-card" href="' . e($u('/services/' . $s['slug'])) . '">' . $K['secPic']($s) . '<b>' . e($s['name']) . '</b><small>' . e($s['short']) . '</small></a>'; }
    return $h . '</div>';
};
$ph = fn(string $t, string $lead, array $trail): string => '<header class="j-ph"><div class="j-w">' . $crumbs($trail) . '<h1>' . e($t) . '</h1><p>' . e($lead) . '</p></div></header>';
$close = '<section class="j-end rv"><span class="j-flag" aria-hidden="true">' . $I['check'] . '</span><h2>نهاية الرحلة: رعاية مستقرة في البيت</h2><p>ابدأها الآن برسالة.</p><div class="j-acts">' . $K['quiz']('اطلب اشتراك', 'j-btn') . $K['tel']('j-btn j-btn-o') . '</div></section>';

echo $K['head']('journey', '#f6f3ff', $K['fonts']('family=Readex+Pro:wght@300;400;500;600;700'));
?>
<body class="j p-<?= e($page) ?>">
<header class="j-hdr"><div class="j-w j-hdr-in">
  <a class="j-logo" href="<?= e($u('/')) ?>"><img src="/img/mark.png" alt="" width="180" height="155"><b>أفنان الفائقة</b></a>
  <nav class="j-nav" aria-label="القائمة الرئيسية"><?php foreach ($nav as [$h, $t]): ?><a href="<?= e($u($h)) ?>"<?= $cur === $h ? ' aria-current="page"' : '' ?>><?= e($t) ?></a><?php endforeach; ?></nav>
  <?= $K['quiz']('ابدأ', 'j-btn j-hbtn') ?>
  <?= $K['menuBtn']('j-menu') ?>
</div></header>

<main>
<?php if ($page === 'home'): ?>
<section class="j-hero j-w">
  <span class="j-ring rv"><?= $pic('walk', 'ممرضة تساعد سيدة كبيرة في السن على المشي', '', false) ?></span>
  <h1 class="rv">رحلة الرعاية<br><span>تبدأ من بيتك</span></h1>
  <p class="rv"><?= e($K['tagline']) ?></p>
  <div class="j-acts rv"><?= $K['quiz']('ابدأ الرحلة', 'j-btn') ?><?= $K['wa']('واتساب', 'j-btn j-btn-o') ?></div>
</section>

<div class="j-line j-w">
<?= $station(1, 'المحطة الأولى', 'اختر الخدمة', $grid()) ?>
<?php
$pl = '<div class="j-plans">';
foreach ($K['plans'] as [$v, $t, $s, $d]) { $pl .= '<div class="j-plan"><h3>' . e($t) . '</h3><small>' . e($s) . '</small><p>' . e($d) . '</p>' . $K['quiz']('ابدأ ' . $v, 'j-btn j-btn-s', '', $v) . '</div>'; }
echo $station(2, 'المحطة الثانية', 'حدد مدة الاشتراك', $pl . '</div>');
$st = '<ol class="j-steps">';
foreach ($K['steps'] as [$t, $d]) { $st .= '<li><b>' . e($t) . '</b><span>' . e($d) . '</span></li>'; }
echo $station(3, 'المحطة الثالثة', 'نرتّب كل شيء معك', $st . '</ol>');
$wy = '<div class="j-why">';
foreach ($K['why'] as [$ic, $t, $d]) { $wy .= '<div><span>' . $I[$ic] . '</span><b>' . e($t) . '</b><small>' . e($d) . '</small></div>'; }
echo $station(4, 'المحطة الرابعة', 'ونبقى معك طوال الاشتراك', $wy . '</div>');
echo $station(5, 'قبل ما تبدأ', 'أسئلة تتكرر', $K['faq'](array_slice($data['faq'], 0, 5), 'k-faq j-faq') . '<p><a class="j-lnk" href="' . e($u('/faq')) . '">كل الأسئلة</a></p>');
?>
<?= $close ?>
</div>

<?php elseif ($page === 'services'): ?>
<?= $ph('خدماتنا', $K['lead']['services'], [[null, 'خدماتنا']]) ?>
<div class="j-line j-w">
<?php foreach ($sections as $i => $s):
  $b = '<div class="j-items">';
  foreach ($s['items'] as $it) { $b .= '<div><b>' . e($it['title']) . '</b><p>' . e($it['description']) . '</p></div>'; }
  $b .= '</div><p><a class="j-lnk" href="' . e($u('/services/' . $s['slug'])) . '">تفاصيل ' . e($s['name']) . '</a></p>';
  echo $station($i + 1, $s['short'], $s['name'], $b);
endforeach; ?>
<?= $close ?>
</div>

<?php elseif ($page === 'section'): ?>
<header class="j-ph"><div class="j-w"><?= $crumbs([['/services', 'خدماتنا'], [null, $sec['name']]]) ?><span class="j-ring j-ring-s"><?= $K['secPic']($sec, '', false) ?></span><h1><?= e($sec['name']) ?></h1><p><?= e($K['secLead']($sec)) ?></p>
  <div class="j-acts"><?= $K['quiz']('اسأل عن ' . $sec['name'], 'j-btn', $sec['name']) ?><?= $K['tel']('j-btn j-btn-o') ?></div></div></header>
<div class="j-line j-w">
<?php foreach ($sec['items'] as $i => $it):
  $b = '<p>' . e($it['description']) . '</p>';
  if ($it['includes']) { $b .= '<h3>تشمل</h3><ul class="j-ticks">'; foreach ($it['includes'] as $x) { $b .= '<li>' . $I['check'] . e($x) . '</li>'; } $b .= '</ul>'; }
  if ($it['suitable_for']) { $b .= '<h3>مناسبة لـ</h3><p>' . e(implode('، ', $it['suitable_for'])) . '</p>'; }
  echo $station($i + 1, $sec['name'], $it['title'], $b);
endforeach;
$o = '<div class="j-tags">'; foreach ($sections as $s) { if ($s['slug'] === $sec['slug']) continue; $o .= '<a href="' . e($u('/services/' . $s['slug'])) . '">' . e($s['name']) . '</a>'; }
echo $station(count($sec['items']) + 1, 'تصفّح', 'أقسام أخرى', $o . '</div>'); ?>
<?= $close ?>
</div>

<?php elseif ($page === 'about'): ?>
<?= $ph('من نحن', $K['lead']['about'], [[null, 'من نحن']]) ?>
<div class="j-line j-w">
<?php foreach ($K['about'] as $i => [$t, $p]) { echo $station($i + 1, 'عن المركز', $t, '<p>' . e($p) . '</p>'); }
echo $station(4, 'رؤيتنا', 'مع رؤية المملكة 2030', '<div class="j-vis"><img src="/img/vision-2030.png" alt="شعار رؤية المملكة 2030" width="560" height="375" loading="lazy"><p>' . e($K['vision']) . '</p></div>'); ?>
<?= $close ?>
</div>

<?php elseif ($page === 'faq'): ?>
<?= $ph('الأسئلة الشائعة', $K['lead']['faq'], [[null, 'الأسئلة الشائعة']]) ?>
<div class="j-line j-w"><?= $station(1, 'الأسئلة', 'كل ما يسأل عنه العملاء', $K['faq']($data['faq'], 'k-faq j-faq')) ?><?= $close ?></div>

<?php elseif ($page === 'contact'): ?>
<?= $ph('تواصل معنا', $K['lead']['contact'], [[null, 'تواصل معنا']]) ?>
<div class="j-line j-w">
<?= $station(1, 'واتساب', 'اكتب لنا عن الحالة', '<p>نرد عليك على نفس الرقم.</p>' . $K['wa']('راسلنا على واتساب', 'j-btn')) ?>
<?= $station(2, 'اتصال', 'أو كلّمنا مباشرة', '<p>على مدار الساعة، طوال أيام الأسبوع، من جميع مناطق المملكة.</p>' . $K['tel']('j-btn j-btn-o')) ?>
<?= $station(3, 'الاشتراك', 'من شهر واحد حتى سنة كاملة', '<p>نحدد معك المدة المناسبة بعد تقييم الحالة.</p>') ?>
</div>

<?php else: ?>
<?= $ph('الصفحة غير موجودة', $K['lead']['notfound'], [[null, 'غير موجودة']]) ?>
<section class="j-w j-pad"><a class="j-btn" href="<?= e($u('/')) ?>"><span>العودة للرئيسية</span></a></section>
<?php endif; ?>
</main>

<footer class="j-ft"><div class="j-w">
  <a class="j-logo" href="<?= e($u('/')) ?>"><img src="/img/mark.png" alt="" width="180" height="155" loading="lazy"><b>أفنان الفائقة</b></a>
  <div class="j-ft-g">
    <nav aria-label="الموقع"><?php foreach ($nav as [$h, $t]): ?><a href="<?= e($u($h)) ?>"><?= e($t) ?></a><?php endforeach; ?></nav>
    <nav aria-label="الخدمات"><?php foreach ($sections as $s): ?><a href="<?= e($u('/services/' . $s['slug'])) ?>"><?= e($s['name']) ?></a><?php endforeach; ?></nav>
    <div><?= $K['tel']('j-fl') ?><?= $K['wa']('واتساب', 'j-fl') ?></div>
  </div>
  <?= $K['pay'] ?>
  <p class="j-copy">© <?= date('Y') ?> <?= e(BRAND) ?> للخدمات الطبية المنزلية</p>
</div></footer>
<?= $K['tail'] ?>
</body></html>
