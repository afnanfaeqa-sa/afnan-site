<?php
/* Theme «split» (care8) — split screen: on wide screens a full-height photo panel stays fixed on one side while the
   content scrolls beside it; olive and sand, El Messiri, list rows with thumbnails. */
if (!isset($rep, $data, $I)) { http_response_code(404); exit; }
require __DIR__ . '/_kit.php';
$pic = $K['pic'];
$rows = function () use ($sections, $u, $K, $I): string {
    $h = '<div class="l-rows">';
    foreach ($sections as $s) {
        $h .= '<a class="l-row rv" href="' . e($u('/services/' . $s['slug'])) . '"><span class="l-th">' . $K['secPic']($s) . '</span><span><b>' . e($s['name']) . '</b><small>' . e($s['short']) . '</small></span>' . $I['arrow'] . '</a>';
    }
    return $h . '</div>';
};
$asidePhoto = 'majlis';
$asideTitle = 'رعاية في البيت';
if ($page === 'section' && $sec) { $asidePhoto = $K['secImg'][$sec['key']] ?? 'team'; $asideTitle = $sec['name']; }
elseif ($page === 'about') { $asidePhoto = 'team'; $asideTitle = 'من نحن'; }
elseif ($page === 'contact') { $asidePhoto = 'doctor'; $asideTitle = 'تواصل معنا'; }
elseif ($page === 'services') { $asidePhoto = 'wound'; $asideTitle = 'خدماتنا'; }
$close = '<section class="l-sec l-close rv"><h2>نبدأ اليوم؟</h2><p>اكتب لنا عن الحالة، ونقترح عليك الخدمة ومدة الاشتراك.</p><div class="l-acts">' . $K['quiz']('اطلب اشتراك', 'l-btn') . $K['tel']('l-btn l-btn-o') . '</div></section>';
$ph = fn(string $t, string $lead, array $trail): string => '<header class="l-ph">' . $crumbs($trail) . '<h1>' . e($t) . '</h1><p>' . e($lead) . '</p></header>';

echo $K['head']('split', '#3f4a2b', $K['fonts']('family=El+Messiri:wght@400;500;600;700'));
?>
<body class="l p-<?= e($page) ?>">
<div class="l-frame">
  <aside class="l-aside">
    <?= $pic($asidePhoto, '', 'l-bg', false) ?>
    <div class="l-aside-in">
      <a class="l-logo" href="<?= e($u('/')) ?>"><img src="/img/mark.png" alt="" width="180" height="155"><b>أفنان الفائقة</b></a>
      <div class="l-aside-b"><p><?= e($asideTitle) ?></p><?= $K['wa']('واتساب', 'l-btn l-btn-l') ?><?= $K['tel']('l-atel') ?></div>
    </div>
  </aside>

  <div class="l-main">
    <header class="l-hdr">
      <a class="l-logo l-logo-m" href="<?= e($u('/')) ?>"><img src="/img/mark.png" alt="" width="180" height="155"><b>أفنان الفائقة</b></a>
      <nav class="l-nav" aria-label="القائمة الرئيسية"><?php foreach ($nav as [$h, $t]): ?><a href="<?= e($u($h)) ?>"<?= $cur === $h ? ' aria-current="page"' : '' ?>><?= e($t) ?></a><?php endforeach; ?></nav>
      <?= $K['menuBtn']('l-menu') ?>
    </header>

    <main>
<?php if ($page === 'home'): ?>
      <section class="l-hero">
        <p class="l-eye">مركز أفنان الفائقة للخدمات الطبية المنزلية</p>
        <h1 class="rv">كل ما تحتاجه الرعاية،<br>في مكانها الطبيعي: البيت.</h1>
        <p class="l-lead rv"><?= e($K['tagline']) ?></p>
        <div class="l-acts rv"><?= $K['quiz']('اطلب اشتراك', 'l-btn') ?><?= $K['wa']('واتساب', 'l-btn l-btn-o') ?></div>
        <div class="l-heroimg rv"><?= $pic('walk', 'ممرضة تساعد سيدة كبيرة في السن على المشي', '', false) ?></div>
      </section>

      <section class="l-sec"><h2 class="l-h">الخدمات</h2><?= $rows() ?></section>

      <section class="l-sec">
        <h2 class="l-h">الاشتراك</h2>
        <div class="l-plans"><?php foreach ($K['plans'] as [$v, $t, $s, $d]): ?><div class="l-plan rv"><div><h3><?= e($t) ?></h3><small><?= e($s) ?></small><p><?= e($d) ?></p></div><?= $K['quiz']('ابدأ ' . $v, 'l-btn l-btn-s', '', $v) ?></div><?php endforeach; ?></div>
      </section>

      <section class="l-sec l-olive">
        <h2 class="l-h">لماذا أفنان</h2>
        <dl class="l-dl"><?php foreach ($K['why'] as [$ic, $t, $d]): ?><div class="rv"><dt><?= $I[$ic] ?><?= e($t) ?></dt><dd><?= e($d) ?></dd></div><?php endforeach; ?></dl>
      </section>

      <section class="l-sec">
        <h2 class="l-h">من أول رسالة</h2>
        <ol class="l-steps"><?php foreach ($K['steps'] as [$t, $d]): ?><li class="rv"><b><?= e($t) ?></b><span><?= e($d) ?></span></li><?php endforeach; ?></ol>
      </section>

      <section class="l-sec">
        <h2 class="l-h">أسئلة تتكرر</h2>
        <?= $K['faq'](array_slice($data['faq'], 0, 5), 'k-faq l-faq') ?>
        <p><a class="l-lnk" href="<?= e($u('/faq')) ?>">كل الأسئلة</a></p>
      </section>
      <?= $close ?>

<?php elseif ($page === 'services'): ?>
      <?= $ph('خدماتنا', $K['lead']['services'], [[null, 'خدماتنا']]) ?>
      <section class="l-sec"><?= $rows() ?></section>
      <?php foreach ($sections as $s): ?>
      <section class="l-sec" id="<?= e($s['slug']) ?>"><h2 class="l-h"><?= e($s['name']) ?></h2>
        <dl class="l-dl l-dl-plain"><?php foreach ($s['items'] as $it): ?><div class="rv"><dt><?= e($it['title']) ?></dt><dd><?= e($it['description']) ?></dd></div><?php endforeach; ?></dl>
        <p><a class="l-lnk" href="<?= e($u('/services/' . $s['slug'])) ?>">التفاصيل</a></p></section>
      <?php endforeach; ?>
      <?= $close ?>

<?php elseif ($page === 'section'): ?>
      <header class="l-ph"><?= $crumbs([['/services', 'خدماتنا'], [null, $sec['name']]]) ?><h1><?= e($sec['name']) ?></h1><p><?= e($K['secLead']($sec)) ?></p>
        <div class="l-acts"><?= $K['quiz']('اسأل عن ' . $sec['name'], 'l-btn', $sec['name']) ?><?= $K['tel']('l-btn l-btn-o') ?></div>
        <div class="l-heroimg l-only-m"><?= $K['secPic']($sec, '', false) ?></div></header>
      <?php foreach ($sec['items'] as $it): ?><section class="l-sec l-art rv"><h2><?= e($it['title']) ?></h2><p><?= e($it['description']) ?></p>
        <?php if ($it['includes']): ?><h3>تشمل</h3><ul class="l-ticks"><?php foreach ($it['includes'] as $x): ?><li><?= $I['check'] ?><?= e($x) ?></li><?php endforeach; ?></ul><?php endif; ?>
        <?php if ($it['suitable_for']): ?><h3>مناسبة لـ</h3><p><?= e(implode('، ', $it['suitable_for'])) ?></p><?php endif; ?></section><?php endforeach; ?>
      <section class="l-sec"><h2 class="l-h">أقسام أخرى</h2><?= str_replace('l-rows', 'l-rows l-rows-s', $rows()) ?></section>
      <?= $close ?>

<?php elseif ($page === 'about'): ?>
      <?= $ph('من نحن', $K['lead']['about'], [[null, 'من نحن']]) ?>
      <?php foreach ($K['about'] as [$t, $p]): ?><section class="l-sec l-art rv"><h2><?= e($t) ?></h2><p><?= e($p) ?></p></section><?php endforeach; ?>
      <section class="l-sec l-olive l-vis rv"><img src="/img/vision-2030.png" alt="شعار رؤية المملكة 2030" width="560" height="375" loading="lazy"><div><h2>مع رؤية المملكة 2030</h2><p><?= e($K['vision']) ?></p></div></section>
      <?= $close ?>

<?php elseif ($page === 'faq'): ?>
      <?= $ph('الأسئلة الشائعة', $K['lead']['faq'], [[null, 'الأسئلة الشائعة']]) ?>
      <section class="l-sec"><?= $K['faq']($data['faq'], 'k-faq l-faq') ?></section>
      <?= $close ?>

<?php elseif ($page === 'contact'): ?>
      <?= $ph('تواصل معنا', $K['lead']['contact'], [[null, 'تواصل معنا']]) ?>
      <section class="l-sec"><div class="l-plans">
        <div class="l-plan"><div><h3>واتساب</h3><p>اكتب لنا عن الحالة ونرد عليك.</p></div><?= $K['wa']('راسلنا', 'l-btn l-btn-s') ?></div>
        <div class="l-plan"><div><h3>اتصال</h3><p>على مدار الساعة، طوال أيام الأسبوع.</p></div><?= $K['tel']('l-btn l-btn-s l-btn-o') ?></div>
      </div></section>
      <section class="l-sec l-olive"><dl class="l-dl"><div><dt><?= $I['clock'] ?>ساعات العمل</dt><dd>على مدار الساعة</dd></div><div><dt><?= $I['pin'] ?>التغطية</dt><dd>جميع مناطق المملكة العربية السعودية</dd></div><div><dt><?= $I['cal'] ?>الاشتراك</dt><dd>من شهر واحد حتى سنة كاملة</dd></div></dl></section>

<?php else: ?>
      <?= $ph('الصفحة غير موجودة', $K['lead']['notfound'], [[null, 'غير موجودة']]) ?>
      <section class="l-sec"><a class="l-btn" href="<?= e($u('/')) ?>"><span>العودة للرئيسية</span></a></section>
<?php endif; ?>
    </main>

    <footer class="l-ft">
      <div class="l-ft-g">
        <nav aria-label="الموقع"><?php foreach ($nav as [$h, $t]): ?><a href="<?= e($u($h)) ?>"><?= e($t) ?></a><?php endforeach; ?></nav>
        <nav aria-label="الخدمات"><?php foreach ($sections as $s): ?><a href="<?= e($u('/services/' . $s['slug'])) ?>"><?= e($s['name']) ?></a><?php endforeach; ?></nav>
      </div>
      <?= $K['pay'] ?>
      <p>© <?= date('Y') ?> <?= e(BRAND) ?> للخدمات الطبية المنزلية · <?= e($K['tagline']) ?></p>
    </footer>
  </div>
</div>
<?= $K['tail'] ?>
</body></html>
