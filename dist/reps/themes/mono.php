<?php
/* Theme «mono» (care11) — minimal: black on white, one green accent, very large thin type, hairlines, a lot of space,
   almost no photos (one per page), services as a plain index that reveals a photo on hover. */
if (!isset($rep, $data, $I)) { http_response_code(404); exit; }
require __DIR__ . '/_kit.php';
$pic = $K['pic'];
$list = function () use ($sections, $u, $K): string {
    $h = '<ul class="m-list">';
    foreach ($sections as $i => $s) { $h .= '<li class="rv"><a href="' . e($u('/services/' . $s['slug'])) . '"><span class="m-i">' . sprintf('%02d', $i + 1) . '</span><span class="m-n">' . e($s['name']) . '</span><span class="m-s">' . e($s['short']) . '</span><span class="m-p">' . $K['secPic']($s) . '</span></a></li>'; }
    return $h . '</ul>';
};
$ph = fn(string $t, string $lead, array $trail): string => '<header class="m-w m-ph">' . $crumbs($trail) . '<h1>' . e($t) . '</h1><p>' . e($lead) . '</p></header>';
$row = fn(string $label, string $body): string => '<section class="m-w m-row rv"><h2 class="m-lab">' . e($label) . '</h2><div>' . $body . '</div></section>';
$close = '<section class="m-w m-close rv"><p class="m-big">تحتاج رعاية في البيت؟</p><div class="m-acts">' . $K['quiz']('اطلب اشتراك', 'm-btn') . $K['wa']('واتساب', 'm-lnk') . $K['tel']('m-lnk') . '</div></section>';

echo $K['head']('mono', '#ffffff', $K['fonts']('family=Noto+Sans+Arabic:wght@200;300;400;600'));
?>
<body class="m p-<?= e($page) ?>">
<header class="m-hdr m-w">
  <a class="m-logo" href="<?= e($u('/')) ?>">أفنان الفائقة<i>.</i></a>
  <nav class="m-nav" aria-label="القائمة الرئيسية"><?php foreach ($nav as [$h, $t]): ?><a href="<?= e($u($h)) ?>"<?= $cur === $h ? ' aria-current="page"' : '' ?>><?= e($t) ?></a><?php endforeach; ?></nav>
  <?= $K['menuBtn']('m-menu') ?>
</header>

<main>
<?php if ($page === 'home'): ?>
<section class="m-w m-hero">
  <h1 class="rv">رعاية طبية<br>في البيت<i>.</i></h1>
  <div class="m-hero-b rv"><p><?= e($K['tagline']) ?></p><div class="m-acts"><?= $K['quiz']('اطلب اشتراك', 'm-btn') ?><?= $K['wa']('واتساب', 'm-lnk') ?></div></div>
</section>
<figure class="m-w m-fig rv"><?= $pic('majlis', 'ممرضة مع مريض في مجلس بيته', '', false) ?></figure>

<?= $row('الخدمات', $list()) ?>
<?php
$p = '<div class="m-two">';
foreach ($K['plans'] as [$v, $t, $s, $d]) { $p .= '<div><h3>' . e($t) . '</h3><p class="m-mut">' . e($s) . '</p><p>' . e($d) . '</p>' . $K['quiz']('ابدأ ' . $v, 'm-lnk', '', $v) . '</div>'; }
echo $row('الاشتراك', $p . '</div>');
$w = '<dl class="m-dl">';
foreach ($K['why'] as [$ic, $t, $d]) { $w .= '<div><dt>' . e($t) . '</dt><dd>' . e($d) . '</dd></div>'; }
echo $row('لماذا نحن', $w . '</dl>');
$s = '<ol class="m-steps">';
foreach ($K['steps'] as [$t, $d]) { $s .= '<li><b>' . e($t) . '</b> <span>' . e($d) . '</span></li>'; }
echo $row('الخطوات', $s . '</ol>');
echo $row('أسئلة', $K['faq'](array_slice($data['faq'], 0, 5), 'k-faq m-faq') . '<p><a class="m-lnk" href="' . e($u('/faq')) . '">كل الأسئلة</a></p>');
?>
<p class="m-w m-stat rv"><bdi><?= $staff ?>+</bdi> <span>كادر صحي مرخّص · على مدار الساعة · جميع مناطق المملكة</span></p>
<?= $close ?>

<?php elseif ($page === 'services'): ?>
<?= $ph('خدماتنا', $K['lead']['services'], [[null, 'خدماتنا']]) ?>
<?= $row('الأقسام', $list()) ?>
<?php foreach ($sections as $s):
  $b = '<dl class="m-dl m-dl1">';
  foreach ($s['items'] as $it) { $b .= '<div><dt>' . e($it['title']) . '</dt><dd>' . e($it['description']) . '</dd></div>'; }
  echo '<div id="' . e($s['slug']) . '">' . $row($s['name'], $b . '</dl><p><a class="m-lnk" href="' . e($u('/services/' . $s['slug'])) . '">التفاصيل</a></p>') . '</div>';
endforeach; ?>
<?= $close ?>

<?php elseif ($page === 'section'): ?>
<header class="m-w m-ph"><?= $crumbs([['/services', 'خدماتنا'], [null, $sec['name']]]) ?><h1><?= e($sec['name']) ?><i>.</i></h1><p><?= e($K['secLead']($sec)) ?></p>
  <div class="m-acts"><?= $K['quiz']('اسأل عن ' . $sec['name'], 'm-btn', $sec['name']) ?><?= $K['tel']('m-lnk') ?></div></header>
<figure class="m-w m-fig"><?= $K['secPic']($sec, '', false) ?></figure>
<?php foreach ($sec['items'] as $it):
  $b = '<p class="m-lead">' . e($it['description']) . '</p>';
  if ($it['includes']) { $b .= '<h3>تشمل</h3><ul class="m-ul">'; foreach ($it['includes'] as $x) { $b .= '<li>' . e($x) . '</li>'; } $b .= '</ul>'; }
  if ($it['suitable_for']) { $b .= '<h3>مناسبة لـ</h3><p>' . e(implode('، ', $it['suitable_for'])) . '</p>'; }
  echo $row($it['title'], $b);
endforeach;
$o = '<p class="m-tags">'; foreach ($sections as $s) { if ($s['slug'] === $sec['slug']) continue; $o .= '<a class="m-lnk" href="' . e($u('/services/' . $s['slug'])) . '">' . e($s['name']) . '</a>'; }
echo $row('أقسام أخرى', $o . '</p>'); ?>
<?= $close ?>

<?php elseif ($page === 'about'): ?>
<?= $ph('من نحن', $K['lead']['about'], [[null, 'من نحن']]) ?>
<?php foreach ($K['about'] as [$t, $p]) { echo $row($t, '<p class="m-lead">' . e($p) . '</p>'); }
echo $row('رؤية 2030', '<div class="m-vis"><img src="/img/vision-2030.png" alt="شعار رؤية المملكة 2030" width="560" height="375" loading="lazy"><p class="m-lead">' . e($K['vision']) . '</p></div>'); ?>
<?= $close ?>

<?php elseif ($page === 'faq'): ?>
<?= $ph('الأسئلة الشائعة', $K['lead']['faq'], [[null, 'الأسئلة الشائعة']]) ?>
<?= $row('الأسئلة', $K['faq']($data['faq'], 'k-faq m-faq')) ?>
<?= $close ?>

<?php elseif ($page === 'contact'): ?>
<?= $ph('تواصل معنا', $K['lead']['contact'], [[null, 'تواصل معنا']]) ?>
<?= $row('واتساب', '<p class="m-big m-big-s"><a href="' . e($wa) . '" target="_blank" rel="noopener"><bdi dir="ltr">' . e($shown) . '</bdi></a></p><p class="m-mut">اكتب لنا عن الحالة ونرد عليك.</p>') ?>
<?= $row('اتصال', '<p class="m-big m-big-s"><a href="' . e($tel) . '"><bdi dir="ltr">' . e($shown) . '</bdi></a></p><p class="m-mut">على مدار الساعة، طوال أيام الأسبوع.</p>') ?>
<?= $row('التغطية', '<p class="m-lead">جميع مناطق المملكة العربية السعودية. الاشتراك من شهر واحد حتى سنة كاملة.</p>') ?>

<?php else: ?>
<?= $ph('الصفحة غير موجودة', $K['lead']['notfound'], [[null, 'غير موجودة']]) ?>
<section class="m-w m-pad"><a class="m-btn" href="<?= e($u('/')) ?>"><span>العودة للرئيسية</span></a></section>
<?php endif; ?>
</main>

<footer class="m-w m-ft">
  <div class="m-ft-g">
    <a class="m-logo" href="<?= e($u('/')) ?>">أفنان الفائقة<i>.</i></a>
    <nav aria-label="الموقع"><?php foreach ($nav as [$h, $t]): ?><a href="<?= e($u($h)) ?>"><?= e($t) ?></a><?php endforeach; ?></nav>
    <nav aria-label="الخدمات"><?php foreach ($sections as $s): ?><a href="<?= e($u('/services/' . $s['slug'])) ?>"><?= e($s['name']) ?></a><?php endforeach; ?></nav>
    <div><?= $K['tel']('m-fl') ?><?= $K['wa']('واتساب', 'm-fl') ?></div>
  </div>
  <?= $K['pay'] ?>
  <p class="m-mut">© <?= date('Y') ?> <?= e(BRAND) ?> للخدمات الطبية المنزلية</p>
</footer>
<?= $K['tail'] ?>
</body></html>
