#!/usr/bin/env python3
"""
Builds the Afnan Al-Faeqa website into dist/ from:
  templates/  page templates (home page + legal texts)
  content/    everything editable from the dashboard (/admin)
  static/     styles, scripts, images
  admin/      the dashboard itself

Cloudflare Pages settings:  build command  ->  python3 build.py
                            output folder  ->  dist
Uses only the Python standard library.
"""
import glob, html, json, os, re, shutil

ROOT = os.path.dirname(os.path.abspath(__file__))
P = lambda *a: os.path.join(ROOT, *a)
DIST = P("dist")
esc = lambda s: html.escape(str(s or ""), quote=True)

def load(name):
    with open(P("content", name), encoding="utf8") as f:
        return json.load(f)

def clean_slug(s, fallback):
    s = re.sub(r"[^a-z0-9-]+", "-", str(s or "").strip().lower()).strip("-")
    return s or fallback

def img(path):
    """Images picked in the dashboard are saved as /img/uploads/..; pages live at the root, so drop the leading slash."""
    return str(path or "").lstrip("/")

# ------------------------------------------------------------------ content
settings = load("settings.json")
WA = re.sub(r"\D", "", str(settings.get("whatsapp", "966555185244"))) or "966555185244"
PHONE = settings.get("phone_display") or "+" + WA
STAFF = settings.get("staff_count", 250)
PREVIEW = bool(settings.get("preview_mode", True))
SITE_URL = (settings.get("site_url") or "").rstrip("/")

SECTIONS = [  # fixed service sections (id, name, short description)
    ("med", "الخدمات الطبية", "الطبيب والاستشارات"),
    ("nurse", "التمريض المنزلي", "ممرضة مقيمة ورعاية مستمرة"),
    ("elder", "كبار السن", "رعاية يومية ومرافقة"),
    ("kids", "الأم والمولود", "رعاية ما بعد الولادة"),
    ("rehab", "العلاج الطبيعي والتأهيل", "جلسات وبرامج تأهيل"),
    ("lab", "التحاليل والأشعة", "سحب عينات وأشعة في البيت"),
]
SECNAME = {k: n for k, n, _ in SECTIONS}

def folder(name):
    items = []
    for f in sorted(glob.glob(P("content", name, "*.json"))):
        with open(f, encoding="utf8") as fh:
            d = json.load(fh)
        if d.get("hidden"):
            continue
        d["_slug"] = clean_slug(d.get("url"), os.path.splitext(os.path.basename(f))[0])
        items.append(d)
    def order(d):
        try:
            return float(d.get("order"))
        except (TypeError, ValueError):
            return 999.0
    items.sort(key=lambda d: (order(d), d.get("title", "")))
    return items

SERVICES = [s for s in folder("services") if s.get("section") in SECNAME]
ARTICLES = folder("articles")
NURSES = folder("nurses") if settings.get("show_nurses") else []  # الممرضات مخفيّة؛ لإظهارها اجعل show_nurses=true في settings.json
CATS = [[k, n, d, [s["title"] for s in SERVICES if s["section"] == k]] for k, n, d in SECTIONS]
CATS = [c for c in CATS if c[3]]
SLUG = {s["title"]: s["_slug"] for s in SERVICES}

# ------------------------------------------------------------------ tiny markdown (articles)
def inline(t):
    t = esc(t)
    t = re.sub(r"\*\*(.+?)\*\*", r"<b>\1</b>", t)
    t = re.sub(r"\[([^\]]+)\]\((https?://[^)\s]+|[\w./#-]+)\)", r'<a href="\2">\1</a>', t)
    return t

def markdown(md):
    out = []
    for block in re.split(r"\n\s*\n", str(md or "").strip()):
        lines = [l.rstrip() for l in block.strip().splitlines() if l.strip()]
        if not lines:
            continue
        if re.match(r"^#{1,4}\s", lines[0]) and len(lines) == 1:
            out.append("<h3>" + inline(re.sub(r"^#{1,4}\s+", "", lines[0])) + "</h3>")
        elif all(re.match(r"^\s*[-*]\s+", l) for l in lines):
            out.append("<ul>" + "".join("<li>" + inline(re.sub(r"^\s*[-*]\s+", "", l)) + "</li>" for l in lines) + "</ul>")
        else:
            if re.match(r"^#{1,4}\s", lines[0]):
                out.append("<h3>" + inline(re.sub(r"^#{1,4}\s+", "", lines[0])) + "</h3>")
                lines = lines[1:]
            if lines:
                out.append("<p>" + inline(" ".join(lines)) + "</p>")
    return "".join(out)

# ------------------------------------------------------------------ home page pieces
def render_slides():
    out = []
    for i, s in enumerate(load("slides.json").get("slides", [])):
        tag = "h1" if i == 0 else "h2"
        title = esc(s.get("title_before")) + (f' <em>{esc(s.get("highlight"))}</em>' if s.get("highlight") else "") + (" " + esc(s.get("title_after")) if s.get("title_after") else "")
        b2 = f'<a class="btn btn-outline" href="{esc(s.get("button2_link") or "#services")}">{esc(s.get("button2_text"))}</a>' if s.get("button2_text") else ""
        out.append(f'''    <div class="slide{" on" if i == 0 else ""}">
      <img src="{esc(img(s.get("image")))}" alt="{esc(s.get("alt"))}" width="1600" height="900"{' fetchpriority="high"' if i == 0 else ""}>
      <div class="wrap"><div class="slide-txt">
        <span class="tag">{esc(s.get("tag"))}</span>
        <{tag}>{title}</{tag}>
        <p>{esc(s.get("text"))}</p>
        <div class="btns"><a class="btn btn-green" href="#book">{esc(s.get("button_text") or "اطلب الرعاية الآن")}</a>{b2}</div>
      </div></div>
    </div>''')
    return "\n".join(out)

def render_featured():
    return "\n".join(
        f'    <a class="fcard rv" href="{esc(f.get("link"))}"><div class="im"><img src="{esc(img(f.get("image")))}" alt="{esc(f.get("alt"))}" width="1600" height="900" loading="lazy"></div><div class="bd"><h3>{esc(f.get("title"))}</h3><p>{esc(f.get("text"))}</p><span class="go">تفاصيل الخدمة ←</span></div></a>'
        for f in load("home.json").get("featured", []))

def render_reviews():
    return "\n".join(
        f'    <figure class="rev"><span class="q">”</span><blockquote>{esc(r.get("text"))}</blockquote><figcaption><span>{esc(r.get("name"))}</span></figcaption></figure>'
        for r in load("reviews.json").get("reviews", []))

def render_faq():
    return "\n    ".join(
        f'<details{" open" if i == 0 else ""}><summary>{esc(q.get("q"))}</summary><p>{esc(q.get("a"))}</p></details>'
        for i, q in enumerate(load("faq.json").get("faq", [])))

def articles_json():
    arr = [{"id": a["_slug"], "img": img(a.get("image")), "cat": a.get("label", ""), "mins": a.get("minutes", 3),
            "svc": a.get("service", ""), "title": a.get("title", ""), "ex": a.get("excerpt", ""), "body": markdown(a.get("body"))}
           for a in ARTICLES]
    return json.dumps(arr, ensure_ascii=False).replace("</", "<\\/")

with open(P("templates", "index.html"), encoding="utf8") as f:
    home = f.read()
home = (home.replace("{{SLIDES}}", render_slides())
            .replace("{{FEATURED}}", render_featured())
            .replace("{{REVIEWS}}", render_reviews())
            .replace("{{FAQ}}", render_faq())
            .replace("{{ARTICLES_JSON}}", articles_json())
            .replace("{{STAFF}}", esc(STAFF))
            .replace("{{SVC_COUNT}}", str(len(SERVICES))))

# shared header / footer / tab bar, taken from the home page
def cut(a, b, incl_b=True):
    i = home.index(a); j = home.index(b, i) + (len(b) if incl_b else 0); return home[i:j]
def remap(h):
    for k in ["programs", "partners", "guide", "about", "faq", "reviews"]:
        h = h.replace(f'href="#{k}"', f'href="index.html#{k}"')
    return h.replace('href="#top" aria-current="page"', 'href="index.html"').replace('href="#top"', 'href="index.html"')
HEADER = remap(cut('<header class="head"', "</header>"))
FOOTER = remap(cut('<footer class="foot">', "</footer>"))
WAF = cut('<a class="wa-float"', "</a>")
TAB = remap(cut('<nav class="tabbar"', '<script src="data.js">', False).strip()).replace('class="tab on"', 'class="tab"')

ROBOTS = '<meta name="robots" content="noindex">\n' if PREVIEW else ""
GTM_ID = settings.get("gtm_id", "")
GTM_HEAD = (f"<!-- Google Tag Manager -->\n<script>(function(w,d,s,l,i){{w[l]=w[l]||[];w[l].push({{'gtm.start':new Date().getTime(),event:'gtm.js'}});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);}})(window,document,'script','dataLayer','{GTM_ID}');</script>\n<!-- End Google Tag Manager -->\n") if GTM_ID else ""
GTM_BODY = (f'<noscript><iframe src="https://www.googletagmanager.com/ns.html?id={GTM_ID}" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>') if GTM_ID else ""
def canon(path):
    return f'<link rel="canonical" href="{SITE_URL}/{"" if path == "index.html" else path}">\n' if SITE_URL and not PREVIEW else ""
FONTS = '''<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Alexandria:wght@500;600;700;800&family=IBM+Plex+Sans+Arabic:wght@400;500;600&display=swap">'''

def head(title, desc, svc="", extra="", image="img/elderly-walk.jpg", path=""):
    return f'''<!doctype html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
{GTM_HEAD}{ROBOTS}{canon(path) if path else ""}<title>{esc(title)}</title>
<meta name="description" content="{esc(desc)}">
<meta property="og:title" content="{esc(title)}">
<meta property="og:description" content="{esc(desc)}">
<meta property="og:image" content="{esc(image)}">
<meta property="og:locale" content="ar_SA">
<link rel="icon" href="img/mark.png">
{FONTS}
<link rel="stylesheet" href="styles.css">
<script src="rep.js"></script>
<style>:root{{padding-top:env(safe-area-inset-top,0px);padding-bottom:env(safe-area-inset-bottom,0px)}} body{{margin:0}}</style>
{extra}
</head>
<body data-svc="{esc(svc)}">
{GTM_BODY}
{HEADER}
'''
TAIL = f'''
{FOOTER}

{WAF}

{TAB}

<script src="data.js"></script>
<script src="site.js"></script>
<script src="chat.js"></script>
</body>
</html>
'''
TICK = '<svg viewBox="0 0 24 24"><path d="m5 12.5 4.2 4L19 7" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>'

# ------------------------------------------------------------------ write
if os.path.exists(DIST):
    shutil.rmtree(DIST)
shutil.copytree(P("static"), DIST)
shutil.copytree(P("admin"), os.path.join(DIST, "admin"))
if not settings.get("show_nurses"):  # keep nurse photos off the public server while the section is hidden
    shutil.rmtree(os.path.join(DIST, "img", "nurses"), ignore_errors=True)
def write(name, text):
    with open(os.path.join(DIST, name), "w", encoding="utf8") as f:
        f.write(text)

write("data.js", "window.SITE_CATS=" + json.dumps(CATS, ensure_ascii=False) + ";\nwindow.SITE_SLUG=" + json.dumps(SLUG, ensure_ascii=False) + ";\n")

# home page: the template has no document wrapper, add it here
i = home.index("<header")
_org = {"@context": "https://schema.org", "@type": "MedicalBusiness", "@id": f"{SITE_URL}/#org",
        "name": "مركز أفنان الفائقة للخدمات الطبية المنزلية",
        "legalName": "شركة همة أفنان الفائقة للرعاية الطبية",
        "alternateName": ["مركز أفنان الفائقة", "مركز همّة أفنان الفائقة للخدمات الطبية", "Afnan Home Medical Care"],
        "founder": {"@type": "Person", "name": "قتيبة محمد خير الخالد", "jobTitle": "رئيس مجلس الإدارة والمؤسس", "image": f"{SITE_URL}/img/qutaiba-alkhaled.jpg"},
        "numberOfEmployees": {"@type": "QuantitativeValue", "minValue": 250},
        "url": f"{SITE_URL}/", "logo": f"{SITE_URL}/img/logo.png", "image": f"{SITE_URL}/img/elderly-walk.jpg",
        "description": "تمريض منزلي، رعاية كبار السن، علاج طبيعي، تحاليل منزلية ورعاية الأم والمولود باشتراك من شهر حتى سنة، على مدار الساعة في جميع مناطق المملكة.",
        "telephone": "+" + WA,
        "address": {"@type": "PostalAddress", "addressLocality": "الرياض", "addressCountry": "SA"},
        "areaServed": {"@type": "Country", "name": "المملكة العربية السعودية"},
        "openingHoursSpecification": {"@type": "OpeningHoursSpecification",
            "dayOfWeek": ["Saturday", "Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday"], "opens": "00:00", "closes": "23:59"},
        "sameAs": settings.get("same_as") or ["https://www.wikidata.org/wiki/Q142198346", "https://www.instagram.com/afnan_alfaeqa/", "https://x.com/Afnan_alfaeqa"],
        "hasOfferCatalog": {"@type": "OfferCatalog", "name": "خدمات الرعاية المنزلية", "itemListElement": [
            {"@type": "Offer", "itemOffered": {"@type": "Service", "name": s["title"], "url": f"{SITE_URL}/{s['_slug']}.html"}} for s in SERVICES]}}
ORG_LD = ('<script type="application/ld+json">' + json.dumps(_org, ensure_ascii=False) + "</script>\n") if SITE_URL and not PREVIEW else ""
write("index.html", f'''<!doctype html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
{GTM_HEAD}{ROBOTS}{canon("index.html")}<meta name="description" content="مركز أفنان الفائقة للخدمات الطبية المنزلية: تمريض منزلي، رعاية كبار السن، علاج طبيعي، تحاليل منزلية، ورعاية الأم والمولود. باشتراك من شهر حتى سنة، على مدار الساعة في جميع مناطق المملكة.">
<meta property="og:title" content="مركز أفنان الفائقة | رعاية طبية متكاملة في راحة بيتك">
<meta property="og:description" content="تمريض منزلي، رعاية كبار السن، علاج طبيعي وتحاليل منزلية، على مدار الساعة في جميع مناطق المملكة.">
<meta property="og:image" content="{SITE_URL}/img/elderly-walk.jpg">
<meta property="og:type" content="website">
<meta property="og:locale" content="ar_SA">
{ORG_LD}{home[:i].strip()}
<style>body{{margin:0}} img{{max-width:100%}}</style>
</head>
<body>
{GTM_BODY}
{home[i:]}
<script src="afnan-popup.js" defer></script>
</body>
</html>
''')

def scard(s):
    return (f'<a class="scard rv" href="{s["_slug"]}.html" data-c="{s["section"]}"><div class="im"><img src="{esc(img(s.get("image")))}" alt="" width="1600" height="900" loading="lazy"></div>'
            f'<div class="bd"><span class="c">{SECNAME[s["section"]]}</span><h3>{esc(s["title"])}</h3><p>{esc(s.get("description"))}</p><span class="go">تفاصيل الخدمة ←</span></div></a>')

for s in SERVICES:
    t, slug, cat = s["title"], s["_slug"], s["section"]
    d = s.get("description", "")
    simg = img(s.get("image"))
    tt = t if ("منزل" in t or "النقل" in t) else t + " في المنزل"
    lead = d + " بكادر مرخّص، باشتراك من شهر واحد حتى سنة كاملة، وعلى مدار الساعة."
    desc = f"{tt} من مركز أفنان الفائقة: {d} خدمة على مدار الساعة في جميع مناطق المملكة. احجز عبر واتساب."
    ld = {"@context": "https://schema.org", "@type": "Service", "name": t, "serviceType": t, "description": d,
          "areaServed": {"@type": "Country", "name": "المملكة العربية السعودية"},
          "provider": {"@type": "MedicalBusiness", "name": "مركز أفنان الفائقة للخدمات الطبية", "telephone": "+" + WA}}
    extra = '<script type="application/ld+json">' + json.dumps(ld, ensure_ascii=False) + "</script>"
    rel = [x for x in SERVICES if x["section"] == cat and x["_slug"] != slug]
    rel = (rel + [x for x in SERVICES if x["section"] != cat])[:3]
    arts = [a for a in ARTICLES if a.get("related_section") == cat]
    arts = (arts + [a for a in ARTICLES if a not in arts])[:2]
    arts_html = ""
    if arts:
        arts_html = '<div class="block rv"><h2>من دليلك الصحي</h2><div class="rel-arts">' + "".join(
            f'<a class="rel-art" href="index.html#{a["_slug"]}"><img src="{esc(img(a.get("image")))}" alt="" width="1600" height="900" loading="lazy"><div><small>{esc(a.get("label"))}</small><b>{esc(a.get("title"))}</b></div></a>'
            for a in arts) + "</div></div>"
    inc = "".join(f'<div class="inc"><span class="ck">{TICK}</span><span>{esc(x)}</span></div>' for x in s.get("includes", []))
    who = "".join(f"<span>{esc(x)}</span>" for x in s.get("suitable_for", []))
    body = f'''<main class="page-top">
<section class="svc-hero"><div class="wrap">
  <div class="txt">
    <nav class="crumbs" aria-label="مسار الصفحة"><a href="index.html">الرئيسية</a><span>/</span><a href="services.html">الخدمات</a><span>/</span><a href="services.html#{cat}">{SECNAME[cat]}</a></nav>
    <span class="tag">{SECNAME[cat]}</span>
    <h1>{esc(tt)}</h1>
    <p class="lead">{esc(lead)}</p>
    <div class="facts"><span>{TICK}كادر مرخّص</span><span>{TICK}متاح 24/7</span><span>{TICK}جميع مناطق المملكة</span><span>{TICK}ممرض أو ممرضة</span><span>{TICK}اشتراك من شهر لسنة</span></div>
    <div class="btns"><a class="btn btn-green" href="#book">احجز {esc(t)}</a><a class="btn btn-outline" id="heroWa" href="#" target="_blank" rel="noopener">استفسر على واتساب</a></div>
  </div>
  <div class="svc-photo"><img src="{esc(simg)}" alt="{esc(t)} من مركز أفنان الفائقة" width="1600" height="900" fetchpriority="high"><div class="float"><span class="live" style="display:inline-flex"><i></i>متاحون الآن</span></div></div>
</div></section>

<section class="sec" style="padding-top:40px"><div class="wrap svc-layout">
  <div class="svc-main">
    {f'<div class="block rv"><h2>ماذا تشمل الخدمة</h2><div class="inc-grid">{inc}</div></div>' if inc else ""}
    {f'<div class="block rv"><h2>لمن تناسب هذه الخدمة</h2><div class="who">{who}</div></div>' if who else ""}
    <div class="block rv"><h2>كيف تتم الخدمة</h2><ol class="flow">
      <li><b>أرسل طلبك</b><span>من نموذج الحجز أو واتساب أو الاتصال.</span></li>
      <li><b>نراجع الحالة</b><span>يتواصل معك فريقنا ليفهم احتياج المريض.</span></li>
      <li><b>نحدد المدة</b><span>تختار مدة الاشتراك: من شهر واحد حتى سنة كاملة.</span></li>
      <li><b>نبدأ الرعاية</b><span>يبدأ الكادر الرعاية في بيتكم حسب الخطة المتفق عليها.</span></li>
    </ol></div>
    <div class="block rv"><h2>أسئلة عن {esc(t)}</h2>
      <details open><summary>هل تتوفر الخدمة في مدينتي؟</summary><p>نعم، نرسل كوادرنا إلى جميع مناطق المملكة الـ 13. اختر مدينتك في نموذج الحجز وسيرتّب فريقنا الخدمة.</p></details>
      <details><summary>هل تقدمون زيارات منفردة؟</summary><p>لا، خدماتنا باشتراك فقط، من شهر واحد حتى سنة كاملة، عشان يكون للحالة خطة رعاية واضحة وفريق ثابت يتابعها.</p></details>
      <details><summary>هل أقدر أختار ممرض أو ممرضة؟</summary><p>نعم، لدينا ممرضون وممرضات، ويمكنك تحديد تفضيلك عند التواصل معنا.</p></details>
      <details><summary>هل أحتاج تقريرًا أو وصفة طبية؟</summary><p>بعض الإجراءات تحتاج تقريرًا أو وصفة من الطبيب المعالج. سيوضح لك فريقنا المطلوب عند مراجعة الحالة.</p></details>
      <details><summary>كيف يتم الدفع؟</summary><p>يتم السداد خلال 24 ساعة من تقديم الخدمة، عبر مدى أو فيزا أو Apple Pay أو التقسيط بتابي وتمارا.</p></details>
    </div>
    {arts_html}
  </div>
  <aside class="side-book assist" id="book">
    <div style="display:flex;gap:12px;align-items:center"><span class="chat-av"><img src="img/mark.png" alt="" width="34" height="30"></span><div style="display:grid;gap:2px"><h3>احجز {esc(t)}</h3><span class="online"><i></i>مساعد الحجز · متصل الآن</span></div></div>
    <div class="starter-q"><span class="bubble">أهلًا! نرتّب لك {esc(t)}؟</span></div>
    <button class="btn btn-green" type="button" data-book data-svc="{esc(t)}">ابدأ الحجز</button>
    <a class="btn btn-outline" id="sideWa" href="#" target="_blank" rel="noopener">أو راسلنا مباشرة على واتساب</a>
    <div class="mini"><div><span>التوفر</span><b>على مدار الساعة</b></div><div><span>التغطية</span><b>جميع مناطق المملكة</b></div><div><span>المدة</span><b>من شهر حتى سنة</b></div></div>
  </aside>
</div></section>

<section class="sec mist"><div class="wrap">
  <div class="sec-head"><div><h2>خدمات مرتبطة</h2></div><a class="more-link" href="services.html">كل الخدمات ←</a></div>
  <div class="svc-cards">{"".join(scard(x) for x in rel)}</div>
</div></section>

<section class="sec"><div class="wrap">
  <div class="cta rv"><img src="{esc(simg)}" alt="" width="1600" height="900" loading="lazy">
    <div class="in"><h2>أفنان الفائقة.. رعايتهم في راحة بيتهم</h2><p>كلّمنا الآن، ونرتّب لك {esc(t)} في أقرب وقت.</p>
    <div class="btns"><a class="btn btn-white" href="#book">احجز الآن</a><a class="btn btn-navy" id="ctaWa" href="#" target="_blank" rel="noopener">راسلنا على واتساب</a></div></div>
  </div>
</div></section>
</main>
'''
    write(slug + ".html", head(f"{tt} | مركز أفنان الفائقة", desc, t, extra, simg, slug + ".html") + body + TAIL)

chips = '<button type="button" class="chip" data-c="all" aria-pressed="true">كل الخدمات</button>' + "".join(
    f'<button type="button" class="chip" data-c="{c[0]}" aria-pressed="false">{c[1]}</button>' for c in CATS)
write("services.html", head("خدماتنا | مركز أفنان الفائقة", "جميع خدمات الرعاية الطبية المنزلية من مركز أفنان الفائقة: تمريض منزلي، رعاية كبار السن، علاج طبيعي، تحاليل منزلية، ورعاية الأم والمولود.", path="services.html") + f'''<main class="page-top">
<section class="sec" style="padding-top:20px"><div class="wrap">
  <div class="hub-head">
    <nav class="crumbs" aria-label="مسار الصفحة"><a href="index.html">الرئيسية</a><span>/</span><span>الخدمات</span></nav>
    <h1>خدماتنا الطبية المنزلية</h1>
    <p>{len(SERVICES)} خدمة ينفّذها كادر صحي مرخّص بأدوات معقّمة، على مدار الساعة وفي جميع مناطق المملكة. اختر القسم، ثم افتح الخدمة لتعرف تفاصيلها وتحجزها.</p>
  </div>
  <div class="chips-row" id="hubChips" role="group" aria-label="تصفية حسب القسم">{chips}</div>
  <div class="svc-cards" id="hubGrid">{"".join(scard(x) for x in SERVICES)}</div>
</div></section>
<section class="sec" style="padding-top:0"><div class="wrap">
  <div class="cta"><img src="img/elderly-care.jpg" alt="" width="1600" height="900" loading="lazy">
    <div class="in"><h2>مش متأكد إيش تحتاج؟</h2><p>راسلنا على واتساب ووصف لنا حالة المريض، ونرشّح لك الخدمة المناسبة.</p>
    <div class="btns"><a class="btn btn-white" id="ctaWa" href="#" target="_blank" rel="noopener">راسلنا على واتساب</a><a class="btn btn-navy" href="index.html#book">احجز من الرئيسية</a></div></div>
  </div>
</div></section>
</main>
''' + TAIL)

for f in sorted(glob.glob(P("templates", "legal", "*.html"))):
    with open(f, encoding="utf8") as fh:
        raw = fh.read()
    title = re.search(r"<!-- title: (.*?) -->", raw).group(1)
    desc = re.search(r"<!-- description: (.*?) -->", raw).group(1)
    content = re.sub(r"<!--.*?-->\n?", "", raw).strip()
    write(os.path.basename(f), head(f"{title} | مركز أفنان الفائقة", desc, path=os.path.basename(f)) + f'''<main class="page-top">
<section class="sec" style="padding-top:20px"><div class="wrap legal">
  <nav class="crumbs" aria-label="مسار الصفحة"><a href="index.html">الرئيسية</a><span>/</span><span>{title}</span></nav>
  <h1>{title}</h1>
  <p class="updated">آخر تحديث: أكتوبر 2026</p>
  <div class="legal-body">{content}</div>
</div></section>
</main>
''' + TAIL)

# ------------------------------------------------------------------ about (reference page: factual, neutral wording)
ABOUT_UPDATED = "2026-11-17"
ABOUT_UPDATED_AR = "17 نوفمبر 2026"
_about_desc = "معلومات أساسية عن مركز أفنان الفائقة للخدمات الطبية المنزلية: الاسم الرسمي، قصة الاسم، الإدارة، الكادر والتغطية، والخدمات."
write("about.html", head("من نحن | مركز أفنان الفائقة للخدمات الطبية المنزلية", _about_desc, path="about.html", extra=ORG_LD) + f"""<main class="page-top">
<section class="sec" style="padding-top:20px"><div class="wrap legal about-page">
  <nav class="crumbs" aria-label="مسار الصفحة"><a href="index.html">الرئيسية</a><span>/</span><span>من نحن</span></nav>
  <h1>من نحن</h1>
  <p class="updated">آخر تحديث: <time datetime="{ABOUT_UPDATED}">{ABOUT_UPDATED_AR}</time></p>
  <div class="legal-body">
    <h2>نبذة عن المركز</h2>
    <p>مركز أفنان الفائقة للخدمات الطبية المنزلية هو مقدّم خاص لخدمات الرعاية الصحية المنزلية في المملكة العربية السعودية، ومقرّه مدينة الرياض.</p>
    <h2>الاسم الرسمي</h2>
    <p>الاسم المسجّل للمركز هو «شركة همة أفنان الفائقة للرعاية الطبية»، ويُعرف تجاريًا باسم «مركز أفنان الفائقة»، ويستخدم كذلك اسم «مركز همّة أفنان الفائقة للخدمات الطبية». الاسم الإنجليزي: <span lang="en" dir="ltr" style="white-space:nowrap">Afnan Home Medical Care</span>.</p>
    <h2>قصة الاسم</h2>
    <p>كلمة «همّة» في اسم المركز مستوحاة من عبارة صاحب السمو الملكي الأمير محمد بن سلمان، ولي العهد، «همة السعوديين مثل جبل طويق»، التي قالها في مؤتمر مبادرة مستقبل الاستثمار بالرياض عام 2018. واختير الاسم تعبيرًا عن الالتزام بخدمة المرضى في بيوتهم بعزيمة ثابتة.</p>
    <h2>الإدارة</h2>
    <div class="about-person">
      <img src="img/qutaiba-alkhaled.jpg" alt="قتيبة محمد خير الخالد، رئيس مجلس إدارة مركز أفنان الفائقة" width="240" height="240" loading="lazy">
      <p>أسّس المركز الأستاذ قتيبة محمد خير الخالد، ويرأس مجلس إدارة شركة همة أفنان الفائقة للرعاية الطبية. وهو حاصل على بكالوريوس في التجارة وإدارة الأعمال، ويعمل في مجال الاستثمار في قطاع الرعاية الصحية منذ أكثر من 25 عامًا. ويملك عددًا من الشركات العاملة في الخدمات الطبية وتوريد الأجهزة الطبية والخدمات اللوجستية في المملكة العربية السعودية والمملكة المتحدة وسوريا.</p>
    </div>
    <h2>الكادر والتغطية</h2>
    <p>يضم المركز أكثر من {esc(STAFF)} من الكوادر الصحية المرخّصة، ويقدّم خدماته في مختلف مناطق المملكة على مدار الساعة.</p>
    <h2>الخدمات</h2>
    <p>يقدّم المركز خدماته ضمن الأقسام التالية:</p>
    <ul>
      <li>الطب المنزلي</li>
      <li>التمريض المنزلي</li>
      <li>رعاية كبار السن</li>
      <li>رعاية الأم والمولود</li>
      <li>العلاج الطبيعي وإعادة التأهيل</li>
      <li>التحاليل والأشعة المنزلية</li>
    </ul>
    <p>تفاصيل كل خدمة في <a href="services.html">صفحة الخدمات</a>.</p>
  </div>
</div></section>
</main>
""" + TAIL)

# ------------------------------------------------------------------ nurses
def exp_txt(n):
    if n.get("exp_label"): return n["exp_label"]
    y = n.get("experience_years")
    return f"+{y} سنوات خبرة" if y else ""

def ncard(n):
    tag = '<span class="n-av on">متاحة الآن</span>' if n.get("available") else ""
    return (f'<a class="ncard rv" href="nurse-{n["_slug"]}.html"><span class="nc-av"><img src="{esc(img(n.get("avatar") or n.get("card_photo") or n.get("photo")))}" alt="{esc(n["title"])}، {esc(n.get("role"))} من مركز أفنان الفائقة" width="360" height="360" loading="lazy"></span>'
            f'<div class="bd"><h3>{esc(n["title"])}</h3><span class="n-role">{esc(n.get("role"))}</span>{tag}'
            f'<div class="n-meta"><span>{esc(n.get("nationality"))}</span>{("<span>" + esc(exp_txt(n)) + "</span>") if exp_txt(n) else ""}</div>'
            f'<span class="go">الملف الكامل ←</span></div></a>')

if NURSES:
    write("nurses.html", head("ممرضاتنا ومقدّمات الرعاية | مركز أفنان الفائقة", "تعرّفوا على ممرضات ومقدّمات الرعاية في مركز أفنان الفائقة قبل التعاقد: الخبرات، المهارات، اللغات، والشهادات.", path="nurses.html") + f"""<main class="page-top">
<section class="sec" style="padding-top:20px"><div class="wrap">
  <div class="hub-head">
    <nav class="crumbs" aria-label="مسار الصفحة"><a href="index.html">الرئيسية</a><span>/</span><span>ممرضاتنا</span></nav>
    <h1>تعرّفوا على اللي بيرعى أهلكم</h1>
    <p>قبل ما تتعاقدون، شوفوا خبرة كل ممرضة ومقدّمة رعاية، ولغاتها، ومهاراتها. وكل الشهادات موثّقة لدى المركز.</p>
  </div>
  <div class="nurse-grid">{"".join(ncard(n) for n in NURSES)}</div>
</div></section>
<section class="sec" style="padding-top:0"><div class="wrap">
  <div class="cta"><img src="img/elderly-care.jpg" alt="" width="1600" height="900" loading="lazy">
    <div class="in"><h2>تحتاجون ممرضة مقيمة؟</h2><p>قولوا لنا عن الحالة، ونرشّح لكم الأنسب من فريقنا.</p>
    <div class="btns"><a class="btn btn-white" href="#book">اطلب الرعاية</a><a class="btn btn-navy" id="ctaWa" href="#" target="_blank" rel="noopener">راسلنا على واتساب</a></div></div>
  </div>
</div></section>
</main>
""" + TAIL)

for n in NURSES:
    nm, slug = n["title"], n["_slug"]
    skills = "".join(f"<span>{esc(x)}</span>" for x in n.get("skills", []))
    langs = "، ".join(esc(x) for x in n.get("languages", []))
    exp = "".join(f'<div class="xp"><b>{esc(e.get("role"))}</b><small>{esc(e.get("place"))}</small><ul>' + "".join(f"<li>{esc(p)}</li>" for p in e.get("points", [])) + "</ul></div>" for e in n.get("experience", []))
    quals = "".join(f'<div class="inc"><span class="ck">{TICK}</span><span>{esc(q)}</span></div>' for q in n.get("qualifications", []))
    dip = (f'<a class="dip" href="{esc(img(n["diploma"]))}" target="_blank" rel="noopener"><img src="{esc(img(n["diploma"]))}" alt="شهادة دبلوم {esc(nm)}" width="900" height="1395" loading="lazy"><span>اضغط لتكبير الشهادة</span></a>' if n.get("diploma") else "")
    avail = '<span class="live" style="display:inline-flex"><i></i>متاحة الآن</span>' if n.get("available") else '<span class="live off" style="display:inline-flex">اسألونا عن موعد التوفّر</span>'
    req = f"رعاية مقيمة مع {nm}"
    rest = [x for x in NURSES if x is not n][:3]
    others = ('<section class="sec mist"><div class="wrap"><div class="sec-head"><div><h2>تعرّفوا على باقي الفريق</h2></div><a class="more-link" href="nurses.html">كل الممرضات ←</a></div><div class="nurse-grid">' + "".join(ncard(x) for x in rest) + '</div></div></section>') if rest else ""
    body = f"""<main class="page-top">
<section class="svc-hero nurse-hero"><div class="wrap">
  <div class="txt">
    <nav class="crumbs" aria-label="مسار الصفحة"><a href="index.html">الرئيسية</a><span>/</span><a href="nurses.html">ممرضاتنا</a><span>/</span><span>{esc(nm)}</span></nav>
    <span class="tag">{esc(n.get("role"))}</span>
    <h1>{esc(nm)}</h1>
    <p class="lead">{esc(n.get("summary"))}</p>
    <div class="facts"><span>{TICK}{esc(n.get("nationality"))}</span>{("<span>" + TICK + esc(exp_txt(n)) + "</span>") if exp_txt(n) else ""}{("<span>" + TICK + langs + "</span>") if langs else ""}<span>{TICK}شهادات موثّقة لدى المركز</span></div>
    <div class="btns"><a class="btn btn-green" href="#book" data-svc="{esc(req)}">اطلب {esc(nm)}</a><a class="btn btn-outline" id="heroWa" href="#" target="_blank" rel="noopener">اسأل عنها على واتساب</a></div>
  </div>
  <div class="nurse-id"><span class="nurse-av"><img src="{esc(img(n.get("avatar") or n.get("photo")))}" alt="{esc(nm)}، {esc(n.get("role"))} من مركز أفنان الفائقة" width="360" height="360" fetchpriority="high"></span>{avail}</div>
</div></section>

<section class="sec" style="padding-top:40px"><div class="wrap svc-layout">
  <div class="svc-main">
    <div class="block rv"><h2>المهارات</h2><div class="who">{skills}</div></div>
    {("<div class=\"block rv\"><h2>الخبرات</h2><div class=\"xps\">" + exp + "</div></div>") if exp else ""}
    <div class="block rv"><h2>الشهادات</h2><div class="inc-grid">{quals}</div>{dip}</div>
  </div>
  <aside class="side-book assist" id="book">
    <div style="display:flex;gap:12px;align-items:center"><span class="chat-av"><img src="img/mark.png" alt="" width="34" height="30"></span><div style="display:grid;gap:2px"><h3>اطلب {esc(nm)}</h3><span class="online"><i></i>مساعد الحجز · متصل الآن</span></div></div>
    <div class="starter-q"><span class="bubble">نرتّب لك رعاية مقيمة مع {esc(nm)}؟</span></div>
    <button class="btn btn-green" type="button" data-book data-svc="{esc(req)}">ابدأ الحجز</button>
    <a class="btn btn-outline" id="sideWa" href="#" target="_blank" rel="noopener">أو راسلنا مباشرة على واتساب</a>
    <div class="mini"><div><span>الجنسية</span><b>{esc(n.get("nationality"))}</b></div>{("<div><span>الخبرة</span><b>" + esc(exp_txt(n).replace(" خبرة","")) + "</b></div>") if exp_txt(n) else ""}<div><span>المدة</span><b>من شهر حتى سنة</b></div></div>
  </aside>
</div></section>

{others}
</main>
"""
    ld = {"@context": "https://schema.org", "@type": "Person", "name": nm, "jobTitle": n.get("role"), "worksFor": {"@type": "MedicalBusiness", "name": "مركز أفنان الفائقة للخدمات الطبية", "telephone": "+" + WA}, "knowsLanguage": n.get("languages", [])}
    extra = '<script type="application/ld+json">' + json.dumps(ld, ensure_ascii=False) + "</script>"
    write(f"nurse-{slug}.html", head(f"{nm}، {n.get('role')} | مركز أفنان الفائقة", f"{nm}، {n.get('role')} {n.get('nationality')} {(' ' + exp_txt(n)) if exp_txt(n) else ''} في مركز أفنان الفائقة. " + str(n.get("summary", ""))[:90], req, extra, img(n.get("photo")), f"nurse-{slug}.html") + body + TAIL)

# ------------------------------------------------------------------ authorised reps (content/reps.json)
# One self-contained page per rep at /<slug>/ (noindex, not in sitemap, linked from nowhere),
# and rep.js on every page swaps the center's numbers for the rep who brought the visitor.
REPS = json.load(open(P("content", "reps.json"), encoding="utf8")).get("reps", [])
with open(P("templates", "rep.js"), encoding="utf8") as fh:
    write("rep.js", fh.read().replace("__REPS__", json.dumps([{k: r[k] for k in ("code", "name", "phone")} for r in REPS], ensure_ascii=False)))
REP_SECTIONS = [("med", "الطب المنزلي"), ("nurse", "التمريض المنزلي"), ("elder", "رعاية كبار السن"),
                ("kids", "الأم والمولود"), ("rehab", "العلاج الطبيعي والتأهيل"), ("lab", "التحاليل والأشعة")]
WA_ICON = '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 2.2a9.7 9.7 0 0 0-8.3 14.8L2.4 21.8l4.9-1.3A9.7 9.7 0 1 0 12 2.2Zm4.4 11.7c-.2-.1-1.4-.7-1.7-.8-.2-.1-.4-.1-.5.1l-.8 1c-.1.2-.3.2-.5.1a6.6 6.6 0 0 1-3.3-2.9c-.2-.4.2-.4.7-1.3.1-.2 0-.3 0-.4l-.8-1.8c-.2-.5-.4-.4-.5-.4h-.5a.9.9 0 0 0-.7.3 2.8 2.8 0 0 0-.9 2.1 4.9 4.9 0 0 0 1 2.6 11.2 11.2 0 0 0 4.3 3.8c1.6.7 2.2.7 3 .6a2.6 2.6 0 0 0 1.7-1.2 2.1 2.1 0 0 0 .2-1.2c-.1-.1-.3-.2-.5-.3Z"/></svg>'
PHONE_ICON = '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.4 1.8.7 2.7a2 2 0 0 1-.5 2.1L8 9.8a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.7.7a2 2 0 0 1 1.7 2Z"/></svg>'
CHEV = '<svg class="chev" viewBox="0 0 24 24" aria-hidden="true"><path d="m6 9 6 6 6-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>'
REP_CSS = """
:root{--g:#0b3a27;--g2:#0f5a3c;--btn:#1f9d55;--gold:#c9a24e;--gold-lt:#e9cd8a;--cream:#f7f4ec;--ink:#10281d;--muted:#59685f;--line:#e5e7df;
  --display:"Alexandria","Segoe UI",Tahoma,sans-serif;--body:"IBM Plex Sans Arabic","Segoe UI",Tahoma,Arial,sans-serif;color-scheme:light;
  --stars:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='96' height='96' viewBox='0 0 96 96'%3E%3Cg fill='none' stroke='%23c9a24e' stroke-opacity='.16' stroke-width='1'%3E%3Cpath d='M48 22l7.6 18.4L74 48l-18.4 7.6L48 74l-7.6-18.4L22 48l18.4-7.6z'/%3E%3Cpath d='M29.6 29.6h36.8v36.8H29.6z' transform='rotate(45 48 48)'/%3E%3C/g%3E%3C/svg%3E")}
*{box-sizing:border-box}
html{-webkit-text-size-adjust:100%}
body{margin:0;background:var(--cream);color:var(--ink);font:16px/1.75 var(--body);padding-bottom:env(safe-area-inset-bottom,0px)}
a{color:inherit}
.w{max-width:560px;margin:0 auto;padding:0 max(18px,env(safe-area-inset-left,0px)) 0 max(18px,env(safe-area-inset-right,0px))}
.top{background:var(--stars),radial-gradient(500px 340px at 80% 0%,rgba(15,122,70,.55),transparent 65%),linear-gradient(170deg,#0e4b32,#0a3424 70%);color:#fff;padding:calc(18px + env(safe-area-inset-top,0px)) 0 34px;border-radius:0 0 30px 30px}
.logo{display:inline-grid;place-items:center;background:#fff;border-radius:16px;padding:6px 12px;box-shadow:0 10px 24px -12px rgba(0,0,0,.6)}
.logo img{height:52px;width:auto;display:block}
.who{display:grid;justify-items:center;text-align:center;gap:10px;margin-top:26px}
.ini{width:92px;height:92px;border-radius:50%;display:grid;place-items:center;background:var(--cream);color:var(--g);font:800 42px/1 var(--display);box-shadow:0 0 0 5px var(--gold),0 0 0 14px rgba(201,162,78,.15)}
h1{margin:12px 0 0;font:800 30px/1.3 var(--display)}
.badge{display:inline-flex;align-items:center;gap:7px;border:1px solid rgba(201,162,78,.75);color:var(--gold-lt);border-radius:999px;padding:5px 14px;font-weight:600;font-size:14.5px}
.badge svg{width:16px;height:16px}
.perks{list-style:none;margin:16px 0 0;padding:0;display:flex;flex-wrap:wrap;justify-content:center;gap:8px}
.perks li{display:flex;align-items:center;gap:6px;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.14);border-radius:999px;padding:5px 12px;font-size:14px;color:#e8f2ec}
.perks svg{width:15px;height:15px;color:var(--gold-lt)}
.acts{display:grid;gap:10px;margin-top:24px}
.btn{display:flex;align-items:center;justify-content:center;gap:10px;min-height:56px;border-radius:16px;font:700 18px var(--display);text-decoration:none;transition:transform .15s,filter .2s}
.btn:active{transform:scale(.98)}
.btn svg{width:24px;height:24px}
.btn-wa{background:var(--btn);color:#fff;box-shadow:0 14px 28px -14px rgba(31,157,85,.95)}
.btn-wa:hover{filter:brightness(1.07)}
.btn-call{background:transparent;color:#fff;border:1.5px solid rgba(255,255,255,.4);font-size:16.5px;min-height:50px}
.btn-call .n{direction:ltr;unicode-bidi:isolate;font-variant-numeric:tabular-nums;color:var(--gold-lt)}
section{padding:34px 0 0}
h2{font:800 22px/1.4 var(--display);color:var(--g);margin:0 0 14px;display:flex;align-items:center;gap:10px}
h2::before{content:"";width:22px;height:3px;border-radius:3px;background:var(--gold)}
.acc{background:#fff;border:1px solid var(--line);border-radius:18px;overflow:hidden}
.acc+.acc{margin-top:10px}
.acc>button{all:unset;box-sizing:border-box;width:100%;display:flex;align-items:center;justify-content:space-between;gap:12px;padding:16px 18px;cursor:pointer;font:700 17px var(--display);color:var(--ink)}
.acc>button:focus-visible{outline:3px solid var(--gold);outline-offset:-3px;border-radius:18px}
.acc .cnt{font:600 13px var(--body);color:var(--muted);margin-inline-start:auto}
.chev{width:20px;height:20px;flex:none;color:var(--g2);transition:transform .25s}
.acc[open]>button .chev,.acc.open>button .chev{transform:rotate(180deg)}
.panel{display:grid;grid-template-rows:0fr;transition:grid-template-rows .3s ease}
.acc.open .panel{grid-template-rows:1fr}
.panel>div{overflow:hidden}
.panel ul{list-style:none;margin:0;padding:0 18px 14px}
.panel li{padding:10px 0;border-top:1px solid var(--line)}
.panel li b{display:block;font-weight:600;color:var(--g)}
.panel li span{display:block;color:var(--muted);font-size:15px;line-height:1.7}
.panel p{margin:0;padding:0 18px 16px;color:var(--muted)}
.steps{list-style:none;margin:0;padding:0;display:grid;gap:10px;counter-reset:s}
.steps li{display:grid;grid-template-columns:40px 1fr;gap:12px;align-items:center;background:#fff;border:1px solid var(--line);border-radius:18px;padding:14px 16px;counter-increment:s}
.steps li::before{content:counter(s);width:40px;height:40px;border-radius:50%;display:grid;place-items:center;background:var(--g);color:var(--gold-lt);font:800 18px var(--display)}
.pay{display:flex;flex-wrap:wrap;gap:6px;padding:0 18px 16px}
.pay img{height:26px;width:auto;background:#fff;border:1px solid var(--line);border-radius:7px;padding:3px 6px}
.trust{margin:36px 0 0;background:var(--g);color:#e8f2ec;text-align:center;padding:20px 18px calc(96px + env(safe-area-inset-bottom,0px));font:600 14.5px/1.9 var(--body)}
.trust b{color:var(--gold-lt);font-weight:600}
.fwa{position:fixed;left:max(16px,env(safe-area-inset-left,0px));bottom:calc(16px + env(safe-area-inset-bottom,0px));z-index:10;width:60px;height:60px;border-radius:50%;display:grid;place-items:center;background:#25d366;color:#fff;box-shadow:0 14px 30px -10px rgba(0,0,0,.45)}
.fwa svg{width:32px;height:32px}
@media (prefers-reduced-motion:reduce){.panel,.chev,.btn{transition:none}}
"""
CHECK = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12.5 4.2 4L19 7" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/></svg>'
SHIELD = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3l7 3v5c0 4.5-3 8.2-7 10-4-1.8-7-5.5-7-10V6l7-3z" fill="none" stroke="currentColor" stroke-width="1.9"/><path d="m9 12 2 2 4-4" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"/></svg>'
def rep_page(r):
    local = "0" + r["phone"][3:]
    shown = f"{local[:3]} {local[3:6]} {local[6:]}"
    first = r["name"].split()[0]
    wa_link = f"https://wa.me/{r['phone']}?text=" + urllib.parse.quote(f"السلام عليكم، وصلتكم من صفحة {first}")
    def acc(i, title, inner, count=""):
        return (f'<div class="acc"><button type="button" aria-expanded="false" aria-controls="p{i}" id="b{i}">{esc(title)}'
                f'{f"<span class=cnt>{count}</span>" if count else ""}{CHEV}</button>'
                f'<div class="panel" id="p{i}" role="region" aria-labelledby="b{i}"><div>{inner}</div></div></div>')
    svc_html = ""
    for i, (k, nm) in enumerate(REP_SECTIONS):
        items = [s for s in SERVICES if s["section"] == k]
        if not items:
            continue
        lis = "".join(f'<li><b>{esc(s["title"])}</b><span>{esc(s.get("description", ""))}</span></li>' for s in items)
        svc_html += acc(f"s{i}", nm, f"<ul>{lis}</ul>", f"{len(items)} خدمات" if len(items) > 2 else f"{len(items)} خدمة" if len(items) == 1 else "خدمتان")
    pays = "".join(f'<img src="/img/pay/{p}.png" alt="{a}" loading="lazy">' for p, a in
                   [("mada", "مدى"), ("visa", "فيزا"), ("applepay", "Apple Pay"), ("stcpay", "STC Pay"), ("tabby", "تابي"), ("tamara", "تمارا")])
    faqs = [("مدة الاشتراك؟", "<p>من شهر لين سنة حسب الحالة، وما فيه زيارات منفردة.</p>"),
            ("أقدر أختار ممرض أو ممرضة؟", "<p>نعم.</p>"),
            ("وين تخدمون؟", "<p>جميع مناطق المملكة، على مدار ٢٤ ساعة.</p>"),
            ("طرق الدفع؟", f"<p>خلال ٢٤ ساعة من تقديم الخدمة: مدى، فيزا، Apple Pay، STC Pay، تابي، تمارا.</p><div class=\"pay\">{pays}</div>")]
    faq_html = "".join(acc(f"f{i}", q, a) for i, (q, a) in enumerate(faqs))
    title = f"{r['name']} · ممثل معتمد لمركز أفنان الفائقة"
    return f"""<!doctype html>
<html lang="ar" dir="rtl" data-rep="{esc(r['code'])}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="robots" content="noindex, follow">
<title>{esc(title)}</title>
<meta name="description" content="{esc(r['name'])}، ممثل معتمد لمركز أفنان الفائقة للخدمات الطبية المنزلية. تواصل معه مباشرة على واتساب.">
<meta name="theme-color" content="#0b3a27">
<link rel="icon" href="/img/mark.png">
{FONTS}
<script src="/rep.js"></script>
<style>{REP_CSS}</style>
</head>
<body>
<header class="top"><div class="w">
  <span class="logo"><img src="/img/logo.png" alt="مركز أفنان الفائقة للخدمات الطبية" width="350" height="360"></span>
  <div class="who">
    <span class="ini" aria-hidden="true">{esc(r['initial'])}</span>
    <h1>{esc(r['name'])}</h1>
    <span class="badge">{SHIELD}ممثل معتمد · مركز أفنان الفائقة</span>
    <ul class="perks"><li>{CHECK}نفس أسعار المركز</li><li>{CHECK}متابعة شخصية</li><li>{CHECK}رد خلال دقائق</li></ul>
  </div>
  <div class="acts">
    <a class="btn btn-wa" href="{wa_link}" target="_blank" rel="noopener">{WA_ICON}تواصل معي على واتساب</a>
    <a class="btn btn-call" href="tel:+{r['phone']}">{PHONE_ICON}اتصل بي <span class="n">{shown}</span></a>
  </div>
</div></header>
<main class="w">
  <section aria-labelledby="h-svc"><h2 id="h-svc">خدماتنا</h2>{svc_html}</section>
  <section aria-labelledby="h-how"><h2 id="h-how">كيف تبدأ الخدمة؟</h2>
    <ol class="steps"><li>تتواصل معي وتعطيني تفاصيل الحالة</li><li>الفريق الطبي يقيّم الحالة ويعرض الاشتراك المناسب</li><li>الكادر يبدأ الرعاية في بيتك</li></ol>
  </section>
  <section aria-labelledby="h-faq"><h2 id="h-faq">أسئلة شائعة</h2>{faq_html}</section>
</main>
<p class="trust">مركز مرخّص · أكثر من <b>{esc(STAFF)}</b> كادرًا صحيًا · على مدار الساعة</p>
<a class="fwa" href="{wa_link}" target="_blank" rel="noopener" aria-label="واتساب {esc(r['name'])}">{WA_ICON}</a>
<script>
document.querySelectorAll(".acc>button").forEach(function(b){{b.addEventListener("click",function(){{var a=b.parentNode,o=!a.classList.contains("open");a.classList.toggle("open",o);b.setAttribute("aria-expanded",o)}})}});
</script>
</body>
</html>
"""
import urllib.parse
for r in REPS:
    os.makedirs(os.path.join(DIST, r["slug"]), exist_ok=True)
    write(os.path.join(r["slug"], "index.html"), rep_page(r))

import hashlib
def _v(name):
    p = os.path.join(DIST, name)
    return hashlib.md5(open(p, "rb").read()).hexdigest()[:8] if os.path.exists(p) else "0"
_vers = {n: _v(n) for n in ("styles.css", "site.js", "chat.js", "stories.js", "afnan-popup.js", "rep.js")}
for f in glob.glob(os.path.join(DIST, "*.html")):
    t = open(f, encoding="utf8").read()
    for n, v in _vers.items():
        t = t.replace(f'"{n}"', f'"{n}?v={v}"')
    open(f, "w", encoding="utf8").write(t)

# ------------------------------------------------------------------ contact number everywhere + robots/sitemap
for f in glob.glob(os.path.join(DIST, "*.html")) + glob.glob(os.path.join(DIST, "*.js")):
    with open(f, encoding="utf8") as fh:
        t = fh.read()
    t2 = t.replace("+966 55 518 5244", PHONE).replace("966555185244", WA)
    if t2 != t:
        with open(f, "w", encoding="utf8") as fh:
            fh.write(t2)

pages = ["index.html", "services.html"] + [s["_slug"] + ".html" for s in SERVICES] + (["nurses.html"] + [f"nurse-{n['_slug']}.html" for n in NURSES] if NURSES else []) + ["about.html", "privacy.html", "terms.html"]
if PREVIEW or not SITE_URL:
    write("robots.txt", "User-agent: *\nDisallow: /\n" if PREVIEW else "User-agent: *\nAllow: /\n")
else:
    write("robots.txt", f"User-agent: *\nAllow: /\nDisallow: /admin/\nSitemap: {SITE_URL}/sitemap.xml\n")
    write("sitemap.xml", '<?xml version="1.0" encoding="UTF-8"?>\n<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n' +
          "".join(f"  <url><loc>{SITE_URL}/{'' if p == 'index.html' else p}</loc>" + (f"<lastmod>{ABOUT_UPDATED}</lastmod>" if p == "about.html" else "") + "</url>\n" for p in pages) + "</urlset>\n")

print(f"Built {len(pages)} pages, {len(SERVICES)} services, {len(ARTICLES)} articles -> dist/")
