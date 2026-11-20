/* shared behaviour for inner pages: header, call card, mobile tab bar + services sheet, booking card, reveal */
const WA="966555185244";
const wa=t=>`https://wa.me/${WA}?text=${encodeURIComponent(t)}`;
const I={
  med:'<path d="M9 3h6v6h6v6h-6v6H9v-6H3V9h6Z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>',
  nurse:'<path d="M12 20.5s-7.5-4.6-7.5-10.2A4.3 4.3 0 0 1 12 7.6a4.3 4.3 0 0 1 7.5 2.7c0 5.6-7.5 10.2-7.5 10.2Z" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="M7 12h2.5l1.5-2.5 2 5 1.5-2.5H17" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>',
  elder:'<circle cx="11" cy="4.5" r="2.2" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="M11 7.5v6l-3 7M11 13.5l3 7M7.5 10.5h7M17.5 9.5V21" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>',
  kids:'<circle cx="12" cy="9" r="5" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="M10 8.5h.01M14 8.5h.01M10 11c1 .9 3 .9 4 0M6.5 21c0-3 2.4-5 5.5-5s5.5 2 5.5 5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>',
  rehab:'<path d="M3.5 12h3.5l2-5 4 10 2-5h5.5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>',
  lab:'<path d="M9 3h6M10 3v6l-5 9a2 2 0 0 0 1.7 3h10.6a2 2 0 0 0 1.7-3l-5-9V3M7.2 15h9.6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>',
  food:'<path d="M12 21c-4.8 0-8-3.8-8-8 0-3 2-5 5-5 1.4 0 2 .6 3 .6S13.6 8 15 8c3 0 5 2 5 5 0 4.2-3.2 8-8 8ZM12 8.6c0-2.2 1-4 3-5.1" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>',
  car:'<path d="M2.5 16.5V8.5h11l4 3.5h4v4.5M2.5 16.5h19M8 10v3.4M6.3 11.7h3.4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><circle cx="7" cy="18.5" r="2" fill="#fff" stroke="currentColor" stroke-width="1.8"/><circle cx="17" cy="18.5" r="2" fill="#fff" stroke="currentColor" stroke-width="1.8"/>'
};
const ico=k=>`<svg viewBox="0 0 24 24" aria-hidden="true">${I[k]}</svg>`;
const CATS=window.SITE_CATS;
const page=n=>(window.SITE_SLUG[n]||"services")+".html";
const CITIES=["الرياض","جدة","مكة المكرمة","المدينة المنورة","الدمام","الخبر","الظهران","الأحساء","القصيم","حائل","تبوك","أبها","خميس مشيط","جازان","نجران","الباحة","الجوف","الحدود الشمالية","الطائف","ينبع","مدينة أخرى"];

/* mega menu */
document.getElementById("mega-cols").innerHTML=CATS.slice(0,4).map((c,i)=>`<div>${[c,CATS[i+4]].filter(Boolean).map(x=>`<h4>${ico(x[0])}${x[1]}</h4><ul>${x[3].map(s=>`<li><a href="${page(s)}">${s}</a></li>`).join("")}</ul>`).join("")}</div>`).join("")+`<div class="mega-promo"><img src="img/home-nursing.jpg" alt=""><div><b>تحتاج ممرض في البيت الليلة؟</b><span>متاحون على مدار الساعة</span><a class="btn btn-green" href="${document.getElementById("book")?"#book":"index.html#book"}" style="justify-self:start">احجز الآن</a></div></div>`;
const foot=document.getElementById("foot-svc"); if(foot) foot.innerHTML=CATS.slice(0,6).map(c=>`<li><a href="services.html#${c[0]}">${c[1]}</a></li>`).join("");
document.getElementById("sheetGrid").innerHTML=CATS.map(c=>`<a class="sitem" href="services.html#${c[0]}"><span class="ic">${ico(c[0])}</span><b>${c[1]}</b><small>${c[2]}</small></a>`).join("");

/* header */
const head=document.getElementById("head"),dd=document.getElementById("dd"),mega=document.getElementById("mega"),ddBtn=dd.querySelector("button"),sheet=document.getElementById("sheet");
function closeMenus(){dd.classList.remove("open");mega.hidden=true;ddBtn.setAttribute("aria-expanded","false");sheet.hidden=true;document.body.style.overflow=""}
ddBtn.onclick=()=>{const o=!dd.classList.contains("open");dd.classList.toggle("open",o);mega.hidden=!o;ddBtn.setAttribute("aria-expanded",o)};
document.addEventListener("click",e=>{if(!e.target.closest(".head")&&!e.target.closest(".sheet")&&!e.target.closest("#openSheet")){dd.classList.remove("open");mega.hidden=true;ddBtn.setAttribute("aria-expanded","false")}});
document.addEventListener("keydown",e=>{if(e.key==="Escape")closeMenus()});
const onScroll=()=>head.classList.toggle("scrolled",window.scrollY>24);
window.addEventListener("scroll",onScroll,{passive:true});onScroll();
document.getElementById("openSheet").onclick=()=>{sheet.hidden=false;document.body.style.overflow="hidden"};
sheet.addEventListener("click",e=>{if(e.target.closest("[data-close]"))closeMenus()});

/* call card */
const callBtn=document.getElementById("callBtn"),callPop=document.getElementById("callPop");
callBtn.onclick=e=>{e.stopPropagation();const o=callPop.hidden;callPop.hidden=!o;callBtn.setAttribute("aria-expanded",o)};
document.addEventListener("click",e=>{if(!callPop.hidden&&!e.target.closest("#callPop")&&!e.target.closest("#callBtn")){callPop.hidden=true;callBtn.setAttribute("aria-expanded","false")}});
document.getElementById("copyNum").onclick=async function(){const t=this;try{await navigator.clipboard.writeText("+966555185244");t.textContent="تم النسخ"}catch(_){const r=document.createRange();r.selectNodeContents(callPop.querySelector(".num"));const sl=getSelection();sl.removeAllRanges();sl.addRange(r);t.textContent="الرقم محدد، انسخه"}setTimeout(()=>t.textContent="نسخ الرقم",2200)};

/* whatsapp links */
const svcName=document.body.dataset.svc||"";
const hello=svcName?`السلام عليكم، أرغب بالاستفسار عن خدمة ${svcName} من مركز أفنان الفائقة.`:"السلام عليكم، أرغب بالاستفسار عن خدمات مركز أفنان الفائقة.";
["popWa","tabWa","waFloat","ctaWa","heroWa","sideWa"].forEach(id=>{const a=document.getElementById(id);if(a)a.href=wa(hello)});

/* send booking links home when this page has no booking card */
if(!document.getElementById("book"))document.querySelectorAll('a[href="#book"]').forEach(a=>a.setAttribute("href","index.html#book"));

/* booking card */
const sel=document.getElementById("f-svc");
if(sel){
  sel.innerHTML='<option value="">اختر الخدمة</option>'+CATS.map(c=>`<optgroup label="${c[1]}">${c[3].map(s=>`<option>${s}</option>`).join("")}</optgroup>`).join("");
  if(svcName) sel.value=svcName;
  document.getElementById("f-city").innerHTML='<option value="">اختر المدينة</option>'+CITIES.map(c=>`<option>${c}</option>`).join("");
  document.getElementById("bookForm").addEventListener("submit",e=>{
    e.preventDefault();
    const m=document.getElementById("bookMsg"),svc=sel.value,city=document.getElementById("f-city").value,ph=document.getElementById("f-phone").value.trim();
    m.className="book-msg err";
    if(!svc) return m.textContent="اختر الخدمة المطلوبة.";
    if(!city) return m.textContent="اختر المدينة حتى نرتّب وصول الكادر.";
    if(!/^[0-9+\s]{9,15}$/.test(ph)) return m.textContent="اكتب رقم جوال صحيح، مثل 05xxxxxxxx.";
    const link=wa(`السلام عليكم، أرغب بطلب رعاية منزلية من مركز أفنان الفائقة.\nالخدمة: ${svc}\nالمدينة: ${city}\nرقم الجوال: ${ph}`);
    m.className="book-msg ok";
    m.innerHTML=`طلبك جاهز. <a href="${link}" target="_blank" rel="noopener" style="font-weight:700;color:var(--green-dk)">اضغط هنا لإرساله على واتساب ←</a>`;
  });
}

/* services hub filter */
const hub=document.getElementById("hubGrid");
if(hub){
  const chips=document.getElementById("hubChips"),cards=[...hub.children];
  function show(id){
    if(!CATS.some(c=>c[0]===id)) id="all";
    chips.querySelectorAll(".chip").forEach(c=>c.setAttribute("aria-pressed",c.dataset.c===id));
    cards.forEach(c=>c.hidden=!(id==="all"||c.dataset.c===id));
  }
  chips.addEventListener("click",e=>{const c=e.target.closest(".chip");if(c){show(c.dataset.c);try{history.replaceState(null,"",c.dataset.c==="all"?"#":"#"+c.dataset.c)}catch(_){}}});
  show(location.hash.slice(1)||"all");
  window.addEventListener("hashchange",()=>show(location.hash.slice(1)));
}

/* reveal */
if("IntersectionObserver" in window && !matchMedia("(prefers-reduced-motion: reduce)").matches){
  document.documentElement.classList.add("js-ready");
  const io=new IntersectionObserver(es=>es.forEach(en=>{if(!en.isIntersecting)return;const el=en.target;io.unobserve(el);const sib=[...el.parentElement.children].filter(x=>x.classList.contains("rv"));el.style.transitionDelay=Math.min(sib.indexOf(el),4)*90+"ms";el.classList.add("in")}),{threshold:.12,rootMargin:"0px 0px -30px 0px"});
  document.querySelectorAll(".rv").forEach(el=>io.observe(el));
  setTimeout(()=>document.querySelectorAll(".rv:not(.in)").forEach(el=>{if(el.getBoundingClientRect().top<innerHeight)el.classList.add("in")}),1000);
}
