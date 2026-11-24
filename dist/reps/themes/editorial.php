<?php
/* Theme «editorial» (care2) — a printed health magazine: cream paper, Naskh serif headlines, rules and columns,
   numbered index of services, pull quotes. */
if (!isset($rep, $data, $I)) { http_response_code(404); exit; }
require __DIR__ . '/_kit.php';
$pic = $K['pic'];
$num = fn(int $n): string => strtr((string) $n, ['0' => '٠', '1' => '١', '2' => '٢', '3' => '٣', '4' => '٤', '5' => '٥', '6' => '٦', '7' => '٧', '8' => '٨', '9' => '٩']);
$ph = function (string $kicker, string $title, string $lead) use ($crumbs): string {
    return '<header class="e-ph"><div class="e-w">' . $crumbs([[null, $kicker]]) . '<p class="e-kick">' . e($kicker) . '</p><h1>' . e($title) . '</h1><p class="e-dek">' . e($lead) . '</p></div></header>';
};
$index = function () use ($sections, $u, $num): string {
    $h = '<ol class="e-idx">';
    foreach ($sections as $i => $s) {
        $h .= '<li class="rv"><a href="' . e($u('/services/' . $s['slug'])) . '"><span class="e-n">' . $num($i + 1) . '</span><span class="e-t"><b>' . e($s['name']) . '</b><em>' . e($s['short']) . '</em></span><span class="e-go">اقرأ</span></a></li>';
    }
    return $h . '</ol>';
};
$close = '<section class="e-close"><div class="e-w"><p class="e-kick">الخطوة التالية</p><h2>اكتب لنا عن الحالة، ونقترح عليك الخطة المناسبة</h2>'
    . '<div class="e-acts">' . $K['quiz']('اطلب اشتراك', 'e-btn') . $K['tel']('e-link', 'أو اتصل') . '</div></div></section>';

echo $K['head']('editorial', '#f6f1e7', $K['fonts']('family=Amiri:wght@400;700&family=IBM+Plex+Sans+Arabic:wght@400;500;600'));
?>
<body class="e p-<?= e($page) ?>">
<header class="e-mast">
  <div class="e-w e-mast-top"><span>رعاية طبية منزلية</span><span>جميع مناطق المملكة</span></div>
  <div class="e-w e-mast-mid">
    <?= $K['menuBtn']('e-menu') ?>
    <a class="e-word" href="<?= e($u('/')) ?>">أفنان <span>الفائقة</span></a>
    <?= $K['tel']('e-mtel') ?>
  </div>
  <nav class="e-w e-nav" aria-label="القائمة الرئيسية"><?php foreach ($nav as [$h, $t]): ?><a href="<?= e($u($h)) ?>"<?= $cur === $h ? ' aria-current="page"' : '' ?>><?= e($t) ?></a><?php endforeach; ?></nav>
</header>

<main>
<?php if ($page === 'home'): ?>
<section class="e-hero e-w">
  <div class="e-hero-txt">
    <p class="e-kick">ملف العدد · الرعاية المنزلية</p>
    <h1 class="rv">الرعاية الأفضل<br>تبدأ من البيت</h1>
    <p class="e-dek e-drop rv">في البيت يتعافى المريض بين أهله، ويطمئن كبير السن في مكانه. نقدّم التمريض ورعاية كبار السن والطب المنزلي والعلاج الطبيعي باشتراك من شهر حتى سنة، بفريق ثابت يتابع الحالة على مدار الساعة.</p>
    <div class="e-acts rv"><?= $K['quiz']('اطلب اشتراك', 'e-btn') ?><?= $K['wa']('واتساب', 'e-link') ?></div>
  </div>
  <figure class="e-fig rv"><?= $pic('walk', 'ممرضة تساعد سيدة كبيرة في السن على المشي داخل بيتها', '', false) ?><figcaption>رعاية كبار السن في بيوتهم، بنفس الفريق طوال الاشتراك.</figcaption></figure>
</section>

<section class="e-w e-sec">
  <div class="e-shead"><p class="e-kick">الفهرس</p><h2>ستة أقسام للرعاية في البيت</h2></div>
  <?= $index() ?>
</section>

<section class="e-band"><div class="e-w"><blockquote class="rv">«نعمل بنظام الاشتراك فقط، من شهر واحد حتى سنة كاملة، بخطة رعاية واضحة وكادر ثابت يتابع الحالة.»</blockquote></div></section>

<section class="e-w e-sec">
  <div class="e-shead"><p class="e-kick">الاشتراك</p><h2>شهري أو سنوي</h2></div>
  <div class="e-cols2">
    <?php foreach ($K['plans'] as [$v, $t, $s, $d]): ?>
    <article class="e-plan rv"><h3><?= e($t) ?></h3><p class="e-sm"><?= e($s) ?></p><p><?= e($d) ?></p><?= $K['quiz']('ابدأ ' . $v, 'e-link', '', $v) ?></article>
    <?php endforeach; ?>
  </div>
</section>

<section class="e-w e-sec">
  <div class="e-shead"><p class="e-kick">لماذا أفنان</p><h2>أربعة أسباب</h2></div>
  <div class="e-cols4"><?php foreach ($K['why'] as $i => [$ic, $t, $d]): ?><article class="rv"><span class="e-big"><?= $num($i + 1) ?></span><h3><?= e($t) ?></h3><p><?= e($d) ?></p></article><?php endforeach; ?></div>
</section>

<section class="e-w e-sec e-split">
  <figure class="e-fig rv"><?= $pic('team', 'فريق مركز أفنان الطبي') ?><figcaption>أكثر من <?= $staff ?> من الكوادر الصحية المرخّصة.</figcaption></figure>
  <div>
    <div class="e-shead"><p class="e-kick">كيف نبدأ</p><h2>من أول رسالة إلى فريق ثابت</h2></div>
    <ol class="e-steps"><?php foreach ($K['steps'] as $i => [$t, $d]): ?><li class="rv"><b><?= e($t) ?></b><span><?= e($d) ?></span></li><?php endforeach; ?></ol>
  </div>
</section>

<section class="e-w e-sec">
  <div class="e-shead"><p class="e-kick">أسئلة القرّاء</p><h2>أسئلة تتكرر</h2></div>
  <?= $K['faq'](array_slice($data['faq'], 0, 5), 'k-faq e-faq') ?>
  <p class="e-more"><a href="<?= e($u('/faq')) ?>">كل الأسئلة</a></p>
</section>
<?= $close ?>

<?php elseif ($page === 'services'): ?>
<?= $ph('خدماتنا', 'خدماتنا', $K['lead']['services']) ?>
<section class="e-w e-sec"><?= $index() ?></section>
<?php foreach ($sections as $s): ?>
<section class="e-w e-sec e-group" id="<?= e($s['slug']) ?>">
  <div class="e-shead"><h2><?= e($s['name']) ?></h2></div>
  <div class="e-cols2"><?php foreach ($s['items'] as $it): ?><article class="rv"><h3><?= e($it['title']) ?></h3><p><?= e($it['description']) ?></p></article><?php endforeach; ?></div>
  <p class="e-more"><a href="<?= e($u('/services/' . $s['slug'])) ?>">تفاصيل <?= e($s['name']) ?></a></p>
</section>
<?php endforeach; ?>
<?= $close ?>

<?php elseif ($page === 'section'): ?>
<header class="e-ph"><div class="e-w"><?= $crumbs([['/services', 'خدماتنا'], [null, $sec['name']]]) ?><p class="e-kick">قسم</p><h1><?= e($sec['name']) ?></h1><p class="e-dek"><?= e($K['secLead']($sec)) ?></p>
  <div class="e-acts"><?= $K['quiz']('اسأل عن ' . $sec['name'], 'e-btn', $sec['name']) ?><?= $K['tel']('e-link') ?></div></div></header>
<section class="e-w e-sec">
  <figure class="e-fig e-wide"><?= $K['secPic']($sec, '', false) ?></figure>
  <?php foreach ($sec['items'] as $it): ?>
  <article class="e-art rv">
    <h2><?= e($it['title']) ?></h2><p class="e-drop"><?= e($it['description']) ?></p>
    <?php if ($it['includes']): ?><h3>تشمل</h3><ul class="e-ul"><?php foreach ($it['includes'] as $x): ?><li><?= e($x) ?></li><?php endforeach; ?></ul><?php endif; ?>
    <?php if ($it['suitable_for']): ?><h3>مناسبة لـ</h3><p><?= e(implode('، ', $it['suitable_for'])) ?></p><?php endif; ?>
  </article>
  <?php endforeach; ?>
  <div class="e-shead"><p class="e-kick">اقرأ أيضًا</p><h2>أقسام أخرى</h2></div>
  <ul class="e-tags"><?php foreach ($sections as $s): if ($s['slug'] === $sec['slug']) continue; ?><li><a href="<?= e($u('/services/' . $s['slug'])) ?>"><?= e($s['name']) ?></a></li><?php endforeach; ?></ul>
</section>
<?= $close ?>

<?php elseif ($page === 'about'): ?>
<?= $ph('من نحن', 'من نحن', $K['lead']['about']) ?>
<section class="e-w e-sec e-split">
  <div><?php foreach ($K['about'] as $i => [$t, $p]): ?><article class="e-art rv"><h2><?= e($t) ?></h2><p<?= $i ? '' : ' class="e-drop"' ?>><?= e($p) ?></p></article><?php endforeach; ?></div>
  <figure class="e-fig rv"><?= $pic('majlis', 'مجلس بيت سعودي') ?><figcaption>نصل إليكم في جميع مناطق المملكة.</figcaption></figure>
</section>
<section class="e-band"><div class="e-w e-vis"><img src="/img/vision-2030.png" alt="شعار رؤية المملكة 2030" width="560" height="375" loading="lazy"><blockquote class="rv"><?= e($K['vision']) ?></blockquote></div></section>
<?= $close ?>

<?php elseif ($page === 'faq'): ?>
<?= $ph('الأسئلة الشائعة', 'الأسئلة الشائعة', $K['lead']['faq']) ?>
<section class="e-w e-sec"><?= $K['faq']($data['faq'], 'k-faq e-faq') ?></section>
<?= $close ?>

<?php elseif ($page === 'contact'): ?>
<?= $ph('تواصل معنا', 'تواصل معنا', $K['lead']['contact']) ?>
<section class="e-w e-sec e-cols2">
  <article class="e-plan"><h3>واتساب</h3><p>اكتب لنا عن الحالة ونرد عليك.</p><?= $K['wa']('راسلنا', 'e-btn') ?></article>
  <article class="e-plan"><h3>اتصال</h3><p>كلّمنا مباشرة على مدار الساعة.</p><?= $K['tel']('e-btn e-btn-o') ?></article>
</section>
<section class="e-w e-sec"><div class="e-cols4"><article><h3>ساعات العمل</h3><p>على مدار الساعة، طوال أيام الأسبوع</p></article><article><h3>التغطية</h3><p>جميع مناطق المملكة العربية السعودية</p></article><article><h3>الاشتراك</h3><p>من شهر واحد حتى سنة كاملة</p></article></div></section>

<?php else: ?>
<?= $ph('غير موجودة', 'الصفحة غير موجودة', $K['lead']['notfound']) ?>
<section class="e-w e-sec"><a class="e-btn" href="<?= e($u('/')) ?>"><span>العودة للرئيسية</span></a></section>
<?php endif; ?>
</main>

<footer class="e-ft"><div class="e-w">
  <a class="e-word" href="<?= e($u('/')) ?>">أفنان <span>الفائقة</span></a>
  <p><?= e(BRAND) ?> للخدمات الطبية المنزلية. <?= e($K['tagline']) ?></p>
  <div class="e-ft-cols">
    <nav aria-label="الموقع"><?php foreach ($nav as [$h, $t]): ?><a href="<?= e($u($h)) ?>"><?= e($t) ?></a><?php endforeach; ?></nav>
    <nav aria-label="الخدمات"><?php foreach ($sections as $s): ?><a href="<?= e($u('/services/' . $s['slug'])) ?>"><?= e($s['name']) ?></a><?php endforeach; ?></nav>
    <div><?= $K['tel']('e-link') ?><?= $K['wa']('واتساب', 'e-link') ?></div>
  </div>
  <?= $K['pay'] ?>
  <p class="e-col">© <?= date('Y') ?> <?= e(BRAND) ?></p>
</div></footer>
<?= $K['tail'] ?>
</body></html>
