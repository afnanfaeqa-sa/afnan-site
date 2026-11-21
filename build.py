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
import glob, hashlib, html, json, os, re, shutil

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
CALL_NUM = "+" + re.sub(r"\D", "", PHONE)  # the call line (schema "telephone"); WhatsApp may point elsewhere
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
        "founder": {"@type": "Person", "name": "قتيبة بن محمد الخالدي", "url": f"{SITE_URL}/qutaiba-al-khalidi.html", "jobTitle": "رئيس مجلس الإدارة والمؤسس", "image": f"{SITE_URL}/img/qutaiba-alkhaled.jpg"},
        "numberOfEmployees": {"@type": "QuantitativeValue", "minValue": 250},
        "url": f"{SITE_URL}/", "logo": f"{SITE_URL}/img/logo.png", "image": f"{SITE_URL}/img/elderly-walk.jpg",
        "description": "تمريض منزلي، رعاية كبار السن، علاج طبيعي، تحاليل منزلية ورعاية الأم والمولود باشتراك من شهر حتى سنة، على مدار الساعة في جميع مناطق المملكة.",
        "telephone": CALL_NUM,
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
          "provider": {"@type": "MedicalBusiness", "name": "مركز أفنان الفائقة للخدمات الطبية", "telephone": CALL_NUM}}
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
      <img src="img/qutaiba-alkhaled.jpg" alt="قتيبة بن محمد الخالدي، رئيس مجلس إدارة مركز أفنان الفائقة" width="240" height="240" loading="lazy">
      <p>أسّس المركز الأستاذ قتيبة بن محمد الخالدي، ويرأس مجلس إدارة شركة همة أفنان الفائقة للرعاية الطبية. وهو حاصل على بكالوريوس في التجارة وإدارة الأعمال، ويعمل في مجال الاستثمار في قطاع الرعاية الصحية منذ أكثر من 25 عامًا. ويملك عددًا من الشركات العاملة في الخدمات الطبية وتوريد الأجهزة الطبية والخدمات اللوجستية في المملكة العربية السعودية والمملكة المتحدة وسوريا. <a href="qutaiba-al-khalidi.html">الملف القيادي كاملًا</a></p>
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

# ------------------------------------------------------------------ chairman's leadership profile (same content as afnanhomecare.sa/qutaiba-al-khalidi)
# Photos and PDFs are served from afnanhomecare.sa, the center's other site, where they are published.
LDR = "qutaiba-al-khalidi.html"
_ah = "https://afnanhomecare.sa"
_arr = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 6l-6 6 6 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>'
_ldr_desc = "قتيبة بن محمد الخالدي، المؤسس ورئيس مجلس الإدارة والرئيس التنفيذي لشركة همة أفنان القابضة، والرئيس التنفيذي لمركز أفنان الفائقة للخدمات الطبية."
_ldr_ld = ('<script type="application/ld+json">' + json.dumps({"@context": "https://schema.org", "@type": "ProfilePage", "mainEntity": {
    "@type": "Person", "name": "قتيبة بن محمد الخالدي", "alternateName": "Qutaiba bin Mohammed Al-Khalidi", "birthDate": "1986",
    "nationality": "سوري", "jobTitle": "المؤسس ورئيس مجلس الإدارة والرئيس التنفيذي لشركة همة أفنان القابضة",
    "image": f"{_ah}/leadership/manager-homeland-portrait.jpg",
    "worksFor": {"@type": "MedicalBusiness", "name": "مركز أفنان الفائقة للخدمات الطبية", "url": f"{SITE_URL}/"},
    "sameAs": ["https://x.com/Alafnan112", "https://www.linkedin.com/in/%D9%82%D8%AA%D9%8A%D8%A8%D8%A9-%D8%A7%D9%84%D8%AE%D8%A7%D9%84%D8%AF-62b399367",
               "https://www.tiktok.com/@kuteba.alkhaldy", "https://manhom.com/%D8%B4%D8%AE%D8%B5%D9%8A%D8%A7%D8%AA/%D9%82%D8%AA%D9%8A%D8%A8%D8%A9-%D8%A7%D9%84%D8%AE%D8%A7%D9%84%D8%AF/"]}},
    ensure_ascii=False) + "</script>\n") if SITE_URL and not PREVIEW else ""
_cos = [("شركة همة أفنان القابضة", "المؤسس ورئيس مجلس الإدارة · المملكة المتحدة"), ("مركز أفنان الفائقة للخدمات الطبية", "المالك والمدير العام التنفيذي"),
        ("شركة همة أفنان للخدمات الطبية", "المالك والمؤسس"), ("شركة رعاية أفنان للخدمات اللوجستية", "المالك والمؤسس"),
        ("شركة أفنان الطبية التجارية", "المالك والمؤسس"), ("مركز أفنان للرعاية الصحية", "يضم مركز أفنان للخدمات المنزلية ومركز رعايتي الطبية")]
_media = [("qutaiba-saudi-appointments.jpg", "نخبة التعيينات السعودية", "يجمع بين الرؤية الاستثمارية والخبرة التنفيذية في إدارة الشركات والمجموعات الطبية وتوسيع أنشطتها.", "https://www.instagram.com/theellitesaudi", "منشور نخبة التعيينات السعودية عن قتيبة الخالدي ودوره في قيادة الأعمال والرعاية الطبية"),
          ("qutaiba-manhom-feature.jpg", "من هم — إحدى منصات مجرة", "المالك والمدير العام التنفيذي في مركز أفنان الفائقة للخدمات الطبية.", "https://manhom.com/%D8%B4%D8%AE%D8%B5%D9%8A%D8%A7%D8%AA/%D9%82%D8%AA%D9%8A%D8%A8%D8%A9-%D8%A7%D9%84%D8%AE%D8%A7%D9%84%D8%AF/", "بطاقة تعريفية من منصة من هم عن قتيبة الخالدي ومركز أفنان الفائقة للخدمات الطبية"),
          ("qutaiba-saudi-leaders.jpg", "قادة سعوديون — منصة X", "شخصية قيادية بارزة تتمتع بخبرة واسعة وريادة مثبتة في إدارة وتوجيه الاستثمارات الصحية وتطوير المجموعات الطبية القابضة.", "https://x.com/QadahSaudi", "لقطة لمنشور قادة سعوديون على منصة X يتناول السيرة المهنية لقتيبة الخالدي")]
_well = [("drilling", "الحفر", "حفر البئر الارتوازية حتى الوصول إلى طبقة الماء.", "جهاز الحفر في موقع البئر بجوار لوحة المشروع"),
         ("building", "البناء", "بناء غرفة البئر وحوض الماء وتجهيز الموقع.", "عامل يبني غرفة البئر من الطوب"),
         ("water-flowing", "التشغيل", "الدهان وتركيب لوحة الإهداء، ثم تدفّق الماء.", "الماء يتدفق من صنبور البئر وفوقه لوحة الإهداء"),
         ("opening-crowd", "الافتتاح", "افتتاح البئر بحضور الأهالي والدعاء لشهداء الوطن.", "الأهالي مصطفّون عند البئر يوم الافتتاح")]
_ext = 'target="_blank" rel="noopener"'
_star = '<svg class="ldr-star" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 1.5l2.9 7 7.6.6-5.8 4.9 1.8 7.4L12 17.5l-6.5 3.9 1.8-7.4-5.8-4.9 7.6-.6z" fill="currentColor"/></svg>'
_oct = '<svg class="ldr-oct" viewBox="0 0 40 40" aria-hidden="true"><path d="M20 2l5.3 12.7L38 20l-12.7 5.3L20 38l-5.3-12.7L2 20l12.7-5.3z" fill="none" stroke="currentColor" stroke-width="1.6"/><rect x="9" y="9" width="22" height="22" transform="rotate(45 20 20)" fill="none" stroke="currentColor" stroke-width="1.6"/><circle cx="20" cy="20" r="3" fill="currentColor"/></svg>'
_h = lambda t, id="": f'<div class="ldr-title"{f" id={chr(34)}{id}{chr(34)}" if id else ""}>{_oct}<h2>{t}</h2><span class="ldr-rule" aria-hidden="true"></span></div>'
_najdi = '<div class="ldr-najdi" aria-hidden="true"></div>'
_sadu = '<div class="ldr-sadu" aria-hidden="true"></div>'
write(LDR, head("قتيبة بن محمد الخالدي | المؤسس ورئيس مجلس الإدارة – مركز أفنان الفائقة", _ldr_desc, path=LDR,
                extra=_ldr_ld + '<meta property="og:type" content="profile">\n<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Aref+Ruqaa:wght@700&display=swap">',
                image=f"{_ah}/leadership/manager-homeland-portrait.jpg") + f"""<main class="page-top ldr">
<section class="ldr-hero">
  {_najdi}
  <div class="wrap ldr-hero-in">
    <div class="ldr-hero-txt">
      <nav class="crumbs" aria-label="مسار الصفحة"><a href="index.html">الرئيسية</a><span>/</span><a href="about.html">من نحن</a><span>/</span><span>الملف القيادي</span></nav>
      <p class="ldr-kicker">{_star} الملف القيادي</p>
      <h1>قتيبة بن محمد الخالدي</h1>
      <p class="ldr-en" lang="en" dir="ltr">Qutaiba bin Mohammed Al-Khalidi</p>
      <div class="ldr-roles">
        <p>المؤسس ورئيس مجلس الإدارة والرئيس التنفيذي لشركة همة أفنان القابضة</p>
        <p>الرئيس التنفيذي لمركز أفنان الفائقة للخدمات الطبية</p>
      </div>
      <p class="ldr-intro">أسّس قتيبة الخالدي شركة همة أفنان القابضة، ويقود مركز أفنان الفائقة للرعاية الصحية المنزلية في المملكة العربية السعودية، ويعمل على تسهيل وصول الأسر إلى التمريض والتأهيل ورعاية كبار السن في منازلهم.</p>
      <div class="ldr-btns">
        <a class="ldr-btn ldr-btn-gold" href="{_ah}/leadership/qutaiba-al-khalidi-profile-ar.pdf" {_ext}>تحميل الملف القيادي (PDF)</a>
        <a class="ldr-btn ldr-btn-line" href="#works">أعماله ومواقفه</a>
      </div>
      <ul class="ldr-social" aria-label="حساباته">
        <li><a href="https://x.com/Alafnan112" {_ext}><b>X</b><span dir="ltr">@Alafnan112</span></a></li>
        <li><a href="https://www.linkedin.com/in/%D9%82%D8%AA%D9%8A%D8%A8%D8%A9-%D8%A7%D9%84%D8%AE%D8%A7%D9%84%D8%AF-62b399367" {_ext}><b>in</b><span>LinkedIn</span></a></li>
        <li><a href="https://www.tiktok.com/@kuteba.alkhaldy" {_ext}><b>♪</b><span dir="ltr">@kuteba.alkhaldy</span></a></li>
        <li><a href="https://manhom.com/%D8%B4%D8%AE%D8%B5%D9%8A%D8%A7%D8%AA/%D9%82%D8%AA%D9%8A%D8%A8%D8%A9-%D8%A7%D9%84%D8%AE%D8%A7%D9%84%D8%AF/" {_ext}><b>م</b><span>من هم</span></a></li>
      </ul>
    </div>
    <figure class="ldr-portrait">
      <div class="ldr-arch"><img src="{_ah}/leadership/manager-homeland-portrait.jpg" alt="قتيبة الخالدي إلى جوار العلم السعودي" width="980" height="1600" fetchpriority="high"></div>
      <figcaption>«نحن جنودٌ لهذا الوطن في ميدان الرعاية الصحية»</figcaption>
    </figure>
  </div>
  {_sadu}
</section>

<section class="ldr-facts-band"><div class="wrap">
  <dl class="ldr-facts">
    <div>{_oct}<dt>الخبرة</dt><dd>أكثر من 25 عامًا</dd></div>
    <div>{_oct}<dt>مواليد</dt><dd>1986</dd></div>
    <div>{_oct}<dt>الجنسية</dt><dd>سوري</dd></div>
    <div>{_oct}<dt>الإقامة</dt><dd>المملكة العربية السعودية</dd></div>
  </dl>
</div></section>

<section class="ldr-sec"><div class="wrap">
  {_h("سيرته")}
  <div class="ldr-bio">
    <p class="ldr-bio-lead">مستثمر ورائد أعمال، وقيادي صحي معتمد من وزارة الصحة السعودية، يمتلك خبرة تمتد لأكثر من 25 عامًا في قيادة الاستثمارات الرأسمالية في القطاع الصحي وتأسيس مشاريع طبية نوعية مستدامة.</p>
    <p>تشمل خبرته التخطيط الاستراتيجي للقوى العاملة الطبية، وهندسة الهياكل التنظيمية للشركات القابضة، وحوكمة الأداء، وهيكلة استراتيجيات الموارد البشرية للمجموعات الكبرى، وبناء الكفاءات التنفيذية والطبية لضمان أعلى معايير الجودة والامتثال الطبي.</p>
    <p>أسهم في إدارة وتوسيع العمليات الطبية في السعودية واليمن والسودان، ومواءمة الكوادر البشرية والطبية مع الأنظمة والتشريعات المحلية في كل دولة.</p>
  </div>

  {_h("الشركات التي أسّسها ويقودها")}
  <ul class="ldr-cos">{"".join(f"<li><span class='ldr-cos-n'>{i+1:02d}</span><b>{esc(n)}</b><span>{esc(r)}</span></li>" for i, (n, r) in enumerate(_cos))}</ul>

  {_h("الاعتمادات والشهادات")}
  <ul class="ldr-creds">
    <li>{_star}<span>قيادي صحي معتمد من وزارة الصحة السعودية</span></li>
    <li>{_star}<span>شهادة في القيادة الاستراتيجية وإدارة الأصول المتقدمة</span></li>
    <li>{_star}<span>شهادة كفاءة MSH للمهارات القيادية العليا: إدارة الأزمات الاقتصادية وسلاسل الإمداد الدولية والتخطيط المالي للمجموعات التجارية عابرة الحدود</span></li>
  </ul>
  <p class="ldr-src">المصدر: <a href="https://manhom.com/%D8%B4%D8%AE%D8%B5%D9%8A%D8%A7%D8%AA/%D9%82%D8%AA%D9%8A%D8%A8%D8%A9-%D8%A7%D9%84%D8%AE%D8%A7%D9%84%D8%AF/" {_ext}>ملفه الموثّق في منصة «من هم»</a></p>
</div></section>

<section class="ldr-sec ldr-band" id="works">
  {_najdi}
  <div class="wrap">
  {_h("من أعماله ومواقفه")}

  <article class="ldr-work" id="well-kyrgyzstan">
    <img class="ldr-work-cover" src="{_ah}/archive/kuts339/cover-opening.jpg" alt="أهالي القرية في قرغيزستان يقفون عند البئر يوم افتتاحها ويحملون علم المملكة العربية السعودية" width="1280" height="720" loading="lazy">
    <div class="ldr-work-b">
      <p class="ldr-meta"><span>عمل خيري</span>نوفمبر 2026 · 1448 هـ · منطقة تشوي، قرغيزستان</p>
      <h3>بئر ماء ارتوازي في قرغيزستان صدقةً جاريةً عن شهداء الوطن</h3>
      <p>اكتمل مشروع بئر ماء ارتوازي في منطقة تشوي بقرغيزستان (KUTS339)، قدّمه قتيبة بن محمد الخالدي وأسرته صدقةً جاريةً عن شهداء الوطن، وأشركوا في ثوابه الشعب السعودي كافة.</p>
      <details><summary>اقرأ التفاصيل</summary>
        <p>في قرية من قرى منطقة تشوي شمال قرغيزستان، صار الماء يصل اليوم إلى الأهالي من بئر ارتوازية جديدة. قدّمها قتيبة بن محمد الخالدي مع أفراد من أسرته صدقةً جاريةً عن شهداء الوطن، رحمهم الله، وأشركوا في ثوابها الشعب السعودي كافة، نساءً ورجالًا. نُفّذت البئر بالتعاون مع جمعية طريق السعادة، وبإشراف مركز أفنان الفائقة للخدمات الطبية، وهي الثالثة التي يحفرها مع الجمعية بعد بئرين سابقتين.</p>
        <blockquote class="ldr-plaque"><b>نص لوحة البئر</b><span>مشروع بئر ماء ارتوازي</span><span>صدقة جارية عن شهداء الوطن</span><span>– رحمهم الله –</span><span>أشركنا في ثوابها الشعب السعودي كافة نساءً ورجالًا</span><span>مقدّمة من</span><span>قتيبة بن محمد الخالدي</span><span>حسان الخالد · علي الخالد · أفنان الخالدي · توجان الخالدي</span><small>كما كُتب على اللوحة المثبتة على البئر.</small></blockquote>
        <h4>من أول ضربة حفر إلى أول قطرة ماء</h4>
        <ol class="ldr-steps">{"".join(f'<li><img src="{_ah}/archive/kuts339/{f}.jpg" alt="{esc(a)}" loading="lazy"><b><i>0{i+1}</i>{esc(t)}</b><span>{esc(d)}</span></li>' for i, (f, t, d, a) in enumerate(_well))}</ol>
        <div class="ldr-pics"><img src="{_ah}/archive/kuts339/first-water.jpg" alt="أطفال القرية عند البئر يوم الافتتاح" loading="lazy"><img src="{_ah}/archive/kuts339/opening-children.jpg" alt="الأهالي والأطفال عند حوض الماء" loading="lazy"></div>
        <blockquote class="ldr-quote">«لقد كان هذا الجود بمثابة الغيث لقريةٍ أنهكها العطش وأضناها عناء البحث عن قطرة ماء، واليوم، بفضل الله ثم بفيض عطائكم، انبثقت ينابيع الحياة لتغسل تعب السنين عن كواهل الأهالي، وتزرع الفرحة والطمأنينة في قلوب الأمهات والأطفال.»<cite>من رسالة شكر جمعية طريق السعادة إلى المتبرعين</cite></blockquote>
        <p class="ldr-hadith">قال رسول الله ﷺ: «إذا مات الإنسان انقطع عنه عمله إلا من ثلاثة: إلا من صدقةٍ جارية، أو علمٍ يُنتفع به، أو ولدٍ صالحٍ يدعو له» <small>رواه مسلم</small></p>
        <dl class="ldr-card-dl">
          <div><dt>نوع المشروع</dt><dd>بئر ماء ارتوازي</dd></div><div><dt>رقم المشروع</dt><dd>KUTS339</dd></div>
          <div><dt>الموقع</dt><dd>غرب العاصمة بشكيك، مقاطعة سوقلوق، منطقة تشوي، قرغيزستان</dd></div><div><dt>الإهداء</dt><dd>شهداء الوطن، رحمهم الله</dd></div>
          <div><dt>التنفيذ</dt><dd>جمعية طريق السعادة</dd></div><div><dt>الإشراف</dt><dd>مركز أفنان الفائقة للخدمات الطبية</dd></div>
          <div><dt>في السلسلة</dt><dd>البئر الثالثة مع الجمعية</dd></div>
        </dl>
        <p class="ldr-links"><a href="{_ah}/archive/kuts339/kuts339-well-report-ar.pdf" {_ext}>تقرير المشروع (PDF)</a><a href="https://youtu.be/wwFjTFbuaEw" {_ext}>فيديو التوثيق على يوتيوب</a><a href="https://maps.google.com/?q=43.1285,74.2683" {_ext}>موقع البئر على الخريطة</a></p>
      </details>
    </div>
  </article>

  <article class="ldr-work ldr-work-st" id="statement">
    <img class="ldr-work-cover" src="{_ah}/leadership/manager-statement-original.jpeg" alt="الكلمة المنشورة لقتيبة الخالدي بعنوان إمكاناتنا في خدمة الوطن" width="900" height="1600" loading="lazy">
    <div class="ldr-work-b">
      <p class="ldr-meta"><span>كلمة وموقف</span>أكتوبر 2026 · 1448 هـ</p>
      <h3>إمكاناتنا في خدمة الوطن: كلمة قتيبة الخالدي إلى القيادة والجهات الصحية</h3>
      <p>وضع قتيبة الخالدي نفسه وشركته وكامل الكادر الطبي والإداري وإمكانات الشركة ومرافقها في خدمة القيادة وحكومة المملكة والشعب السعودي، لدعم المنظومة الصحية والمساندة في الطوارئ.</p>
      <details><summary>اقرأ نص الكلمة كاملًا</summary>
        <p>مقام خادم الحرمين الشريفين الملك سلمان بن عبد العزيز آل سعود – حفظه الله –، وصاحب السمو الملكي الأمير محمد بن سلمان بن عبد العزيز آل سعود، ولي العهد رئيس مجلس الوزراء – حفظه الله –، أصحاب المعالي والسمو، مسؤولي الجهات الحكومية والصحية في وطننا الغالي، أبناء الشعب السعودي الكريم.</p>
        <p>انطلاقًا من قيم المواطنة والانتماء، وإيمانًا بالدور الحيوي للقطاع الخاص في دعم مسيرة النماء والازدهار التي تشهدها مملكتنا الغالية تحت ظل قيادتكم الرشيدة، ووفاءً لهذا الوطن المعطاء وشعبه الوفي:</p>
        <p>أتشرف بأن أضع نفسي، وشركتي، وكامل كادرنا الطبي والإداري، وكافة إمكانياتنا ومرافقنا، تحت تصرف القيادة الحكيمة، وحكومة المملكة، والشعب السعودي الكريم، في أي وقت وفي أي موقع يتطلبه الواجب الوطني. ونعلن جاهزيتنا التامة وتسخير كافة طاقاتنا وخبراتنا الطبية واللوجستية للمساهمة في:</p>
        <ul><li>دعم المنظومة الصحية الوطنية في كافة المبادرات والحملات الرسمية.</li><li>تقديم الرعاية الطبية والخدمات الإنسانية للمجتمع بالشراكة مع الجهات المعنية.</li><li>المساندة الفورية في حالات الطوارئ أو الأزمات، لا قدّر الله، تلبيةً لنداء الواجب.</li></ul>
        <p>نحن جنودٌ لهذا الوطن في ميدان الرعاية الصحية، ونعاهدكم على البقاء دائمًا على قدر المسؤولية، مستمدّين العون من الله، ثم من توجيهاتكم السديدة، لتحقيق مستهدفات رؤية المملكة 2030 في بناء مجتمع حيوي وصحي.</p>
        <p>حفظ الله قيادتنا، وأدام على وطننا وشعبنا أمنه وأمانه واستقراره.</p>
        <p class="ldr-sign">قتيبة الخالدي · مدير مجلس الإدارة</p>
        <dl class="ldr-card-dl">
          <div><dt>النوع</dt><dd>كلمة منشورة</dd></div><div><dt>موجّهة إلى</dt><dd>القيادة والجهات الحكومية والصحية والشعب السعودي</dd></div>
          <div><dt>الجهات</dt><dd>شركة همة أفنان القابضة، مركز أفنان للخدمات المنزلية، رعاية أفنان الفائقة للخدمات الطبية</dd></div>
        </dl>
        <p class="ldr-links"><a href="https://www.tiktok.com/@a3ean_shkhseat/video/7699568390788369685" {_ext}>تناول «شخصيات وأعيان» للكلمة</a></p>
      </details>
    </div>
  </article>

  <div class="ldr-community">
    <img src="img/vision-2030.png" alt="شعار رؤية المملكة 2030" width="560" height="375" loading="lazy">
    <div><h3>الرعاية المنزلية وخدمة المجتمع</h3>
    <p>يتناول الخالدي في كلمته المنشورة دور فرق أفنان في دعم المبادرات الصحية، وتقديم الرعاية والخدمات الإنسانية، والمساندة في حالات الطوارئ بالتنسيق مع الجهات المختصة.</p></div>
  </div>
  </div>
</section>

<section class="ldr-sec ldr-contact-band">
  {_sadu}
  <div class="wrap">
  <div class="ldr-title ldr-title-light">{_oct}<h2>للتواصل مع الإدارة</h2><span class="ldr-rule" aria-hidden="true"></span></div>
  <ul class="ldr-contact">
    <li><span>الجوال المباشر</span><a href="tel:+966550604554" dir="ltr">055 060 4554</a></li>
    <li><span>الرقم الموحد</span><a href="tel:920005284" dir="ltr">9200 05284</a></li>
    <li><span>المكتب</span><a href="tel:+966112988883" dir="ltr">011 298 8883</a></li>
    <li><span>البريد الإلكتروني للشركة</span><a href="mailto:info@afnanfaeqa.sa" dir="ltr">info@afnanfaeqa.sa</a></li>
  </ul>
  </div>
</section>

<section class="ldr-sec"><div class="wrap">
  {_h("حضور إعلامي")}
  <p class="ldr-lead">ما نشرته منصات التعريف بالقيادات والشخصيات البارزة عنه. اضغط على أي صورة لقراءة المنشور كاملًا.</p>
  <div class="ldr-media">{"".join(f'<figure><a href="{u}" {_ext}><img src="{_ah}/leadership/{img}" alt="{esc(a)}" loading="lazy"></a><figcaption><b>{esc(t)}</b><q>{esc(q)}</q><a href="{u}" {_ext}>زيارة المصدر {_arr}</a></figcaption></figure>' for img, t, q, u, a in _media)}</div>
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
    ld = {"@context": "https://schema.org", "@type": "Person", "name": nm, "jobTitle": n.get("role"), "worksFor": {"@type": "MedicalBusiness", "name": "مركز أفنان الفائقة للخدمات الطبية", "telephone": CALL_NUM}, "knowsLanguage": n.get("languages", [])}
    extra = '<script type="application/ld+json">' + json.dumps(ld, ensure_ascii=False) + "</script>"
    write(f"nurse-{slug}.html", head(f"{nm}، {n.get('role')} | مركز أفنان الفائقة", f"{nm}، {n.get('role')} {n.get('nationality')} {(' ' + exp_txt(n)) if exp_txt(n) else ''} في مركز أفنان الفائقة. " + str(n.get("summary", ""))[:90], req, extra, img(n.get("photo")), f"nurse-{slug}.html") + body + TAIL)

# ------------------------------------------------------------------ authorised reps (content/reps.json)
# reps/ is the document root of every rep subdomain (mahmoud.afnanfaeqasa.sa …): index.php serves a copy of the
# full main site (built at the end of this file into dist/reps/site) with the rep's number in place of the center's.
# Adding a rep = a subdomain pointed at public_html/reps + one entry in reps.json.
REPS = json.load(open(P("content", "reps.json"), encoding="utf8")).get("reps", [])
shutil.copytree(P("reps"), os.path.join(DIST, "reps"), ignore=shutil.ignore_patterns(".data"))
shutil.copy(P("content", "reps.json"), os.path.join(DIST, "reps", "reps.json"))

def _v(name):
    p = os.path.join(DIST, name)
    return hashlib.md5(open(p, "rb").read() + WA.encode()).hexdigest()[:8] if os.path.exists(p) else "0"  # WA: the number is swapped in after this, so it must bust the cache too
_vers = {n: _v(n) for n in ("styles.css", "site.js", "chat.js", "stories.js", "afnan-popup.js")}
for f in glob.glob(os.path.join(DIST, "*.html")):
    t = open(f, encoding="utf8").read()
    for n, v in _vers.items():
        t = t.replace(f'"{n}"', f'"{n}?v={v}"')
    open(f, "w", encoding="utf8").write(t)

# ------------------------------------------------------------------ contact number everywhere + robots/sitemap
for f in glob.glob(os.path.join(DIST, "*.html")) + glob.glob(os.path.join(DIST, "*.js")):
    with open(f, encoding="utf8") as fh:
        t = fh.read()
    # WhatsApp links follow settings.whatsapp; tel: links and the copied call number stay on the center line
    t2 = re.sub(r'(?<!tel:\+)(?<!"\+)966555185244', WA, t.replace("+966 55 518 5244", PHONE))
    if t2 != t:
        with open(f, "w", encoding="utf8") as fh:
            fh.write(t2)

pages = ["index.html", "services.html"] + [s["_slug"] + ".html" for s in SERVICES] + (["nurses.html"] + [f"nurse-{n['_slug']}.html" for n in NURSES] if NURSES else []) + ["about.html", "qutaiba-al-khalidi.html", "privacy.html", "terms.html"]
if PREVIEW or not SITE_URL:
    write("robots.txt", "User-agent: *\nDisallow: /\n" if PREVIEW else "User-agent: *\nAllow: /\n")
else:
    write("robots.txt", f"User-agent: *\nAllow: /\nDisallow: /admin/\nSitemap: {SITE_URL}/sitemap.xml\n")
    write("sitemap.xml", '<?xml version="1.0" encoding="UTF-8"?>\n<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n' +
          "".join(f"  <url><loc>{SITE_URL}/{'' if p == 'index.html' else p}</loc>" + (f"<lastmod>{ABOUT_UPDATED}</lastmod>" if p == "about.html" else "") + "</url>\n" for p in pages) + "</urlset>\n")

# ------------------------------------------------------------------ clean URLs (/page instead of /page.html; .htaccess serves and 301s)
for f in glob.glob(os.path.join(DIST, "*.html")) + [os.path.join(DIST, "sitemap.xml")]:
    if not os.path.exists(f):
        continue
    t = open(f, encoding="utf8").read()
    t2 = re.sub(r'href="index\.html(#[^"]*)?"', lambda m: f'href="/{m.group(1) or ""}"', t)
    t2 = re.sub(r'href="([a-z0-9-]+)\.html(#[^"]*)?"', lambda m: f'href="{m.group(1)}{m.group(2) or ""}"', t2)
    if SITE_URL:
        t2 = re.sub(re.escape(SITE_URL) + r'/([a-z0-9-]+)\.html', SITE_URL + r'/\1', t2)
    if t2 != t:
        open(f, "w", encoding="utf8").write(t2)

# ------------------------------------------------------------------ rep sites data (dist/reps/data.json)
# The rep design is its own PHP template (reps/index.php); it only takes content from here.
# Nothing from the main site's pages, scripts or tracking codes is copied.
REP_DIR = os.path.join(DIST, "reps")
REP_SECTIONS = {"med": "home-doctor", "nurse": "nursing", "elder": "elderly-care", "kids": "mother-baby", "rehab": "physiotherapy", "lab": "lab-radiology"}
_rep_imgs = {"img/logo.png", "img/mark.png", "img/elderly-walk.jpg", "img/team.jpg", "img/home-nursing.jpg", "img/vision-2030.png"}
_rep_sections = []
for k, nm, short in SECTIONS:
    items = [s for s in SERVICES if s["section"] == k]
    if not items:
        continue
    im = next((s["image"] for s in items if s.get("image")), "img/home-nursing.jpg")
    _rep_imgs.add(im)
    _rep_sections.append({"key": k, "slug": REP_SECTIONS[k], "name": nm, "short": short, "image": "/" + im,
                          "items": [{"title": s["title"], "description": s.get("description", ""), "includes": s.get("includes", []),
                                     "suitable_for": s.get("suitable_for", [])} for s in items]})
for im in sorted(_rep_imgs):
    os.makedirs(os.path.dirname(os.path.join(REP_DIR, im)), exist_ok=True)
    shutil.copy(os.path.join(DIST, im), os.path.join(REP_DIR, im))
shutil.copytree(os.path.join(DIST, "img", "pay"), os.path.join(REP_DIR, "img", "pay"), dirs_exist_ok=True)
_rep_data = {"staff": STAFF, "sections": _rep_sections, "faq": [dict(f, a="راسلنا على واتساب أو اتصل بنا، ويتواصل معك فريقنا لتأكيد التفاصيل ومدة الاشتراك.") if "نموذج الحجز" in f["a"] else f for f in load("faq.json").get("faq", [])],
             "v": hashlib.md5(open(P("reps", "rep.css"), "rb").read()).hexdigest()[:8]}
_dump = json.dumps(_rep_data, ensure_ascii=False)
# no person's name, center number or center tracking code may reach a rep site
_bad = re.compile(r"9665\d{8}|05\d{8}|920005284|0112988883|https?://(?:www\.)?afnanfaeqasa|قتيبة|الخالد|مخلص|سلمان|GTM-|AW-\d|G-[A-Z0-9]{6}|fbq|1096435373095195|gtag")
_theme_files = [(open(f, encoding="utf8").read(), os.path.relpath(f, P("reps"))) for f in sorted(glob.glob(P("reps", "themes", "*.*")))]
for _t, _n in [(_dump, "data.json"), (open(P("reps", "index.php"), encoding="utf8").read(), "index.php"), (open(P("reps", "rep.css"), encoding="utf8").read(), "rep.css")] + _theme_files:
    _hits = set(_bad.findall(re.sub(r"/\* ---------- pixels.*?/\* ---------- end pixels ---------- \*/", "", _t, flags=re.S)))
    assert not _hits, f"rep site: {_n} has {_hits}"
open(os.path.join(REP_DIR, "data.json"), "w", encoding="utf8").write(_dump)

print(f"Built {len(pages)} pages, {len(SERVICES)} services, {len(ARTICLES)} articles -> dist/")
