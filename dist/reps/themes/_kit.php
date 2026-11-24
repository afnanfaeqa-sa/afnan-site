<?php
/*
 * Shared kit for the care2–care15 themes. Each theme has its own layout and CSS; this file only holds what must
 * behave the same on every site: the 3-question request sheet that opens WhatsApp on the site's own number with the
 * site code at the end, the mobile menu sheet, the reveal-on-scroll, the payment logos, and the shared texts
 * (so no theme invents a claim). Included at the top of a theme, after reps/index.php has set up $rep, $data, $I …
 */
if (!isset($rep, $data, $I, $wa, $tel, $shown, $sections, $u)) { http_response_code(404); exit; }

$K = [];
$K['code'] = (string) $rep['code'];
$K['arrow'] = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5m6-6-6 6 6 6"/></svg>';

/* photos: the center's own photo set (care1) + the main-site photos */
$K['pic'] = function (string $name, string $alt = '', string $cls = '', bool $lazy = true): string {
    $p = '/img/care1/';
    return '<img' . ($cls ? ' class="' . $cls . '"' : '') . ' src="' . $p . $name . '.webp" srcset="' . $p . $name . '-800.webp 800w, ' . $p . $name . '.webp 1376w" sizes="(min-width: 1024px) 50vw, 100vw" alt="' . e($alt) . '" width="1376" height="768"' . ($lazy ? ' loading="lazy" decoding="async"' : ' fetchpriority="high"') . '>';
};
$K['secImg'] = ['med' => 'doctor', 'nurse' => 'wound', 'elder' => 'walk', 'kids' => 'baby', 'rehab' => 'physio', 'lab' => 'lab'];
$K['secPic'] = fn(array $s, string $cls = '', bool $lazy = true): string => $K['pic']($K['secImg'][$s['key']] ?? 'team', '', $cls, $lazy);

/* buttons */
$K['quiz'] = fn(string $txt, string $cls, string $svc = '', string $dur = ''): string =>
    '<a class="' . $cls . '" href="' . e($wa) . '" target="_blank" rel="noopener" data-quiz' . ($svc ? ' data-svc="' . e($svc) . '"' : '') . ($dur ? ' data-dur="' . e($dur) . '"' : '') . '><span>' . e($txt) . '</span></a>';
$K['wa'] = fn(string $txt, string $cls): string => '<a class="' . $cls . '" href="' . e($wa) . '" target="_blank" rel="noopener">' . $I['wa'] . '<span>' . e($txt) . '</span></a>';
$K['tel'] = fn(string $cls, string $txt = ''): string => '<a class="' . $cls . '" href="' . e($tel) . '">' . $I['tel'] . '<span>' . ($txt !== '' ? e($txt) . ' ' : '') . '<bdi dir="ltr">' . e($shown) . '</bdi></span></a>';
$K['menuBtn'] = fn(string $cls): string => '<button class="' . $cls . '" type="button" data-menu aria-controls="k-menu" aria-label="القائمة">' . $I['menu'] . '</button>';

/* shared texts — the same claims as the main site, nothing more */
$K['brand'] = BRAND;
$K['tagline'] = 'رعاية طبية منزلية باشتراك من شهر حتى سنة، في جميع مناطق المملكة.';
$K['why'] = [
    ['clock', 'متابعة على مدار الساعة', 'بالاتصال أو واتساب لأي تعديل أو طارئ.'],
    ['team', 'فريق ثابت', 'نفس الكادر يتابع الحالة طوال الاشتراك.'],
    ['shield', 'كادر مرخّص', 'أكثر من ' . $staff . ' من الكوادر الصحية المرخّصة.'],
    ['pin', 'جميع مناطق المملكة', 'نصل إليكم أينما كنتم.'],
];
$K['steps'] = [
    ['تواصلون معنا', 'اتصال أو واتساب، ونسمع عن الحالة واحتياجها.'],
    ['نقيّم الحالة', 'الفريق الطبي يحدد الخدمة والكادر المناسب.'],
    ['خطة الاشتراك', 'نتفق على المدة، من شهر حتى سنة، ويبدأ الكادر.'],
    ['متابعة مستمرة', 'نفس الفريق يتابع الحالة طوال الاشتراك.'],
];
$K['plans'] = [
    ['شهري', 'اشتراك شهري', 'من شهر إلى ثلاثة أشهر', 'للتعافي بعد العمليات والإصابات، والحالات اللي تحتاج متابعة لفترة محددة.'],
    ['سنوي', 'اشتراك سنوي', 'رعاية مستمرة طول السنة', 'لكبار السن والأمراض المزمنة، بخطة رعاية تُراجع كل فترة ونفس الفريق طول السنة.'],
];
$K['about'] = [
    ['نبذة عن المركز', BRAND . ' للخدمات الطبية المنزلية يقدّم رعاية صحية في البيت: تمريض، ورعاية كبار السن، وطب منزلي، وعلاج طبيعي، وتحاليل وأشعة، ورعاية الأم والمولود.'],
    ['الكادر والتغطية', 'يضم المركز أكثر من ' . $staff . ' من الكوادر الصحية المرخّصة، ويقدّم خدماته في مختلف مناطق المملكة على مدار الساعة.'],
    ['طريقة العمل', 'نعمل بنظام الاشتراك فقط، من شهر واحد حتى سنة كاملة، بخطة رعاية واضحة وكادر ثابت يتابع الحالة. ولا نقدّم زيارات منفردة.'],
];
$K['vision'] = 'نساهم في مستهدفات رؤية المملكة 2030 في القطاع الصحي، بنقل الرعاية إلى بيت المريض ورفع جودة الحياة لأهلنا وكبارنا.';
$K['lead'] = [
    'services' => 'رعاية طبية منزلية في ستة أقسام، كلها باشتراك من شهر حتى سنة.',
    'about' => 'مقدّم خاص لخدمات الرعاية الصحية المنزلية في المملكة العربية السعودية، ومقرّه مدينة الرياض.',
    'faq' => 'إجابات مختصرة عن الحجز والاشتراك والتغطية والدفع.',
    'contact' => 'نرد عليك على واتساب أو بالاتصال، على مدار الساعة.',
    'notfound' => 'الرابط غير صحيح أو تغيّر.',
];
$K['secLead'] = fn(array $s): string => $s['short'] . '، باشتراك من شهر حتى سنة، في جميع مناطق المملكة.';
$K['pay'] = (function (): string {
    $h = '<ul class="k-pay" aria-label="طرق الدفع">';
    foreach (['mada' => 'مدى', 'visa' => 'Visa', 'applepay' => 'Apple Pay', 'stcpay' => 'STC Pay', 'tabby' => 'تابي', 'tamara' => 'تمارا'] as $f => $alt) {
        $h .= '<li><img src="/img/pay/' . $f . '.png" alt="' . e($alt) . '" height="20" loading="lazy"></li>';
    }
    return $h . '</ul>';
})();
$K['faq'] = function (array $items, string $cls = 'k-faq'): string {
    $h = '<div class="' . $cls . '">';
    foreach ($items as $f) { $h .= '<details><summary>' . e($f['q']) . '<i aria-hidden="true"></i></summary><p>' . e($f['a']) . '</p></details>'; }
    return $h . '</div>';
};
$K['fonts'] = fn(string $families): string => '<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link rel="stylesheet" href="https://fonts.googleapis.com/css2?' . $families . '&display=swap">';
$K['head'] = function (string $theme, string $themeColor, string $fonts) use ($docTitle, $px): string {
    $v = (string) @filemtime(__DIR__ . '/' . $theme . '.css') . (string) @filemtime(__DIR__ . '/_kit.css');
    return '<!doctype html><html lang="ar" dir="rtl"><head><meta charset="utf-8"><script>document.documentElement.className+=" js"</script>'
        . '<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"><meta name="robots" content="noindex, nofollow">'
        . '<title>' . e($docTitle) . '</title><meta name="description" content="' . e(BRAND) . ' للخدمات الطبية المنزلية: تمريض ورعاية كبار السن وطب منزلي وعلاج طبيعي وتحاليل، باشتراك من شهر حتى سنة، في جميع مناطق المملكة.">'
        . '<meta name="theme-color" content="' . e($themeColor) . '"><link rel="icon" href="/img/mark.png">' . $fonts
        . '<link rel="stylesheet" href="/themes/_kit.css?v=' . e($v) . '"><link rel="stylesheet" href="/themes/' . $theme . '.css?v=' . e($v) . '">' . $px . '</head>';
};

/* the two sheets + the script; every theme prints $K['tail'] right before </body> */
$K['tail'] = (function () use ($nav, $cur, $u, $sections, $tel, $shown, $wa, $waBase, $rep, $I, $track): string {
    $m = '<div class="k-sh" id="k-menu" role="dialog" aria-modal="true" aria-label="القائمة" hidden><div class="k-p"><div class="k-top"><b>القائمة</b><button type="button" data-close>إغلاق</button></div><div class="k-ans">';
    foreach ($nav as [$h, $t]) { $m .= '<a class="k-an" href="' . e($u($h)) . '"' . ($cur === $h ? ' aria-current="page"' : '') . '>' . e($t) . '</a>'; }
    $m .= '<a class="k-an" href="' . e($tel) . '"><span>اتصل بنا</span><bdi dir="ltr">' . e($shown) . '</bdi></a></div></div></div>';
    $q = '<div class="k-sh" id="k-quiz" role="dialog" aria-modal="true" aria-labelledby="k-qT" hidden><div class="k-p">'
        . '<div class="k-top"><button type="button" id="k-qBack">رجوع</button><b id="k-qT">طلب اشتراك</b><button type="button" data-close>إغلاق</button></div><div class="k-bar"><i id="k-qBar"></i></div>'
        . '<div class="k-q" data-q="0"><h4>وش الخدمة اللي تحتاجها؟</h4><div class="k-ans" data-k="svc">';
    foreach ($sections as $s) { $q .= '<button class="k-an" type="button">' . e($s['name']) . '</button>'; }
    $q .= '</div></div><div class="k-q" data-q="1"><h4>الرعاية لمين؟</h4><div class="k-ans" data-k="who">';
    foreach (['لي أنا', 'لأبوي أو أمي', 'لزوجي أو زوجتي', 'لطفلي', 'لشخص ثاني'] as $t) { $q .= '<button class="k-an" type="button">' . e($t) . '</button>'; }
    $q .= '</div></div><div class="k-q" data-q="2"><h4>كم مدة الاشتراك؟</h4><div class="k-ans" data-k="dur">'
        . '<button class="k-an" type="button" data-v="شهري"><span>شهري<small>من شهر إلى ثلاثة أشهر</small></span></button>'
        . '<button class="k-an" type="button" data-v="سنوي"><span>سنوي<small>رعاية مستمرة طول السنة</small></span></button>'
        . '<button class="k-an" type="button" data-v="مو متأكد"><span>مو متأكد<small>نساعدك نحدد الأنسب</small></span></button></div></div>'
        . '<div class="k-q" data-q="3"><h4>آخر خطوة</h4><div class="k-fld"><label for="k-qCity">المدينة</label><select id="k-qCity">';
    foreach (['الرياض', 'جدة', 'مكة المكرمة', 'المدينة المنورة', 'الدمام', 'الخبر', 'القصيم', 'أبها', 'تبوك', 'حائل', 'جازان', 'نجران', 'الباحة', 'الجوف', 'عرعر', 'مدينة أخرى'] as $c) { $q .= '<option>' . e($c) . '</option>'; }
    $q .= '</select></div><div class="k-sum" id="k-qSum"></div><a class="k-send" id="k-qSend" href="' . e($wa) . '" target="_blank" rel="noopener">' . $I['wa'] . '<span>أرسل الطلب على واتساب</span></a>'
        . '<p class="k-note">يفتح واتساب برسالة جاهزة فيها اختياراتك، ويرد عليك الفريق الطبي.</p></div></div></div>';
    $js = '<script>(function(){var $=function(s,r){return(r||document).querySelector(s)},$$=function(s,r){return[].slice.call((r||document).querySelectorAll(s))};var RM=matchMedia("(prefers-reduced-motion: reduce)").matches;'
        . 'var PHONE=' . json_encode((string) $rep['phone']) . ',CODE=' . json_encode((string) $rep['code']) . ',BASE=' . json_encode($waBase, JSON_UNESCAPED_UNICODE) . ';'
        . 'function openSh(el){el.hidden=false;requestAnimationFrame(function(){el.classList.add("open")});document.body.style.overflow="hidden"}function closeSh(el){el.classList.remove("open");document.body.style.overflow="";setTimeout(function(){el.hidden=true},320)}'
        . '$$(".k-sh").forEach(function(el){el.addEventListener("click",function(e){if(e.target===el||e.target.closest("[data-close]"))closeSh(el)})});addEventListener("keydown",function(e){if(e.key==="Escape")$$(".k-sh").forEach(function(el){if(!el.hidden)closeSh(el)})});'
        . '$$("[data-menu]").forEach(function(b){b.addEventListener("click",function(){openSh($("#k-menu"))})});'
        . 'var quiz=$("#k-quiz"),qs=$$(".k-q",quiz),ans={svc:"",who:"",dur:""},step=0;'
        . 'function msg(){var l=[BASE];if(ans.svc)l.push("الخدمة: "+ans.svc);if(ans.who)l.push("الرعاية: "+ans.who);if(ans.dur)l.push("المدة: "+ans.dur);l.push("المدينة: "+$("#k-qCity").value);return l.join("\n")+"\n("+CODE+")"}'
        . 'function upd(){$("#k-qSend").href="https://wa.me/"+PHONE+"?text="+encodeURIComponent(msg())}'
        . 'function go(n){step=Math.max(0,Math.min(3,n));qs.forEach(function(q,i){q.classList.toggle("on",i===step)});$("#k-qBar").style.transform="scaleX("+((step+1)/4)+")";$("#k-qBack").style.visibility=step?"visible":"hidden";if(step===3){var c=function(s){return String(s).replace(/[<>&]/g,"")};$("#k-qSum").innerHTML="<span>الخدمة: <b>"+c(ans.svc||"—")+"</b></span><span>الرعاية: <b>"+c(ans.who||"—")+"</b></span><span>المدة: <b>"+c(ans.dur||"—")+"</b></span>";upd()}}'
        . '$$(".k-ans[data-k]",quiz).forEach(function(g){$$(".k-an",g).forEach(function(b){b.addEventListener("click",function(){$$(".k-an",g).forEach(function(x){x.setAttribute("aria-pressed",x===b?"true":"false")});ans[g.dataset.k]=b.dataset.v||b.textContent.trim();var nx=step+1;if(g.dataset.k==="who"&&ans.dur)nx=3;setTimeout(function(){go(nx)},RM?0:200)})})});'
        . '$("#k-qCity").addEventListener("change",upd);$("#k-qBack").addEventListener("click",function(){go(step-1)});'
        . 'function start(svc,dur){ans={svc:"",who:"",dur:""};$$(".k-an",quiz).forEach(function(x){x.setAttribute("aria-pressed","false")});var s=0;if(svc){ans.svc=svc;var b=$$("[data-k=svc] .k-an",quiz).filter(function(x){return x.textContent.trim()===svc})[0];b&&b.setAttribute("aria-pressed","true");s=1}if(dur){ans.dur=dur;var d=$$("[data-k=dur] .k-an",quiz).filter(function(x){return x.dataset.v===dur})[0];d&&d.setAttribute("aria-pressed","true")}go(s);openSh(quiz)}'
        . '$$("[data-quiz]").forEach(function(el){el.addEventListener("click",function(e){e.preventDefault();start(el.dataset.svc,el.dataset.dur)})});'
        . 'var els=$$(".rv");if(!("IntersectionObserver" in window)||RM){els.forEach(function(e){e.classList.add("in")})}else{var io=new IntersectionObserver(function(es){es.forEach(function(x){if(x.isIntersecting){x.target.classList.add("in");io.unobserve(x.target)}})},{rootMargin:"0px 0px -6% 0px",threshold:.08});els.forEach(function(e){io.observe(e)})}'
        . 'var fl=$(".k-float");if(fl){var chk=function(){fl.classList.toggle("show",scrollY>innerHeight*.5)};addEventListener("scroll",chk,{passive:true});chk()}'
        . '})();</script>';
    $float = '<a class="k-float" href="' . e($wa) . '" target="_blank" rel="noopener" aria-label="راسلنا على واتساب">' . $I['wa'] . '</a>';
    return $float . $m . $q . $js . $track;
})();
