/* Instagram-style stories for phones: rings under the header, full-screen viewer with auto-advance */
(function(){
  const row=document.getElementById("stories"); if(!row) return;
  const S=[
    {label:"خدماتنا",cover:"home-nursing",frames:[
      ["home-nursing","تمريض منزلي","ممرضون وممرضات مرخّصون، على مدار الساعة","تفاصيل الخدمة","home-nursing.html"],
      ["physio","علاج طبيعي في بيتك","جلسات تأهيل بعد العمليات والإصابات","تفاصيل الخدمة","home-physiotherapy.html"],
      ["lab-tests","تحاليل من البيت","نسحب العينة في البيت ونسلّمك النتائج","تفاصيل الخدمة","home-lab-tests.html"],
      ["mother-baby","رعاية الأم والمولود","دعم في الأسابيع الأولى بعد الولادة","كل الخدمات","services.html"]]},
    {label:"كبار السن",cover:"elderly-care",frames:[
      ["elderly-care","رعاية تحفظ كرامتهم","مرافقة يومية ومساعدة في الأنشطة ومتابعة الأدوية","رعاية كبار السن","elderly-care.html"],
      ["elderly-walk","بيت آمن من السقوط","تغييرات بسيطة في البيت تحمي كبار السن","اقرأ المقال","#falls"]]},
    {label:"نصائح",cover:"diabetes",frames:[
      ["diabetes","قياس السكر صح","اغسل يديك وجففها قبل القياس، وسجّل وقت كل قراءة","اقرأ المقال","#diabetes"],
      ["wound-care","جرح العملية","احمرار يزيد أو سخونة أو إفرازات؟ تواصل مع طبيبك","اقرأ المقال","#wound"],
      ["stroke-rehab","بعد الجلطة","تمارين يومية بسيطة أهم من جلسة طويلة متقطعة","اقرأ المقال","#stroke"]]},
    {label:"فريقنا",cover:"team",frames:[
      ["team","+150 كادر صحي","مرخّصون ومدرّبون على الرعاية المنزلية","اطلب الرعاية","#book"],
      ["sterile-kit","أدوات معقّمة","نجهّز كل شيء قبل ما تبدأ الرعاية","اطلب الرعاية","#book"]]},
    {label:"للشركات",cover:"partners",frames:[
      ["partners","شريككم بعد خروج المريض","للمستشفيات والعيادات وشركات التأمين","تعرّف أكثر","#partners"]]}
  ];
  const DUR=5000, reduce=matchMedia("(prefers-reduced-motion: reduce)").matches;
  let seen=new Set(); try{seen=new Set(JSON.parse(sessionStorage.getItem("afnanSeen")||"[]"))}catch(_){}
  row.innerHTML=S.map((s,i)=>`<button type="button" class="ring${seen.has(i)?" seen":""}" data-s="${i}"><span class="ring-img"><img src="img/${s.cover}.jpg" alt="" width="64" height="64"></span><span class="ring-l">${s.label}</span></button>`).join("");

  document.body.insertAdjacentHTML("beforeend",`<div class="story" id="story" hidden role="dialog" aria-modal="true" aria-label="قصص أفنان">
    <div class="story-bars" id="stBars"></div>
    <div class="story-top"><span class="story-av"><img src="img/mark.png" alt="" width="26" height="23"></span><b id="stLabel"></b><button type="button" class="story-x" aria-label="إغلاق"><svg viewBox="0 0 24 24"><path d="M6 6l12 12M18 6 6 18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg></button></div>
    <img class="story-img" id="stImg" alt="">
    <div class="story-tap prev" aria-hidden="true"></div><div class="story-tap next" aria-hidden="true"></div>
    <div class="story-txt"><h3 id="stT"></h3><p id="stS"></p><a class="btn btn-green" id="stCta" href="#"></a></div>
  </div>`);
  const v=document.getElementById("story"),bars=document.getElementById("stBars"),img=document.getElementById("stImg");
  let si=0,fi=0,t0=0,elapsed=0,paused=false,raf=0,downAt=0,lastFocus=null;

  function render(){
    const s=S[si],f=s.frames[fi];
    bars.innerHTML=s.frames.map((_,k)=>`<span><i style="transform:scaleX(${k<fi?1:0})"></i></span>`).join("");
    img.src=`img/${f[0]}.jpg`;img.alt=f[1];
    img.classList.remove("in");void img.offsetWidth;img.classList.add("in");
    document.getElementById("stLabel").textContent=s.label;
    document.getElementById("stT").textContent=f[1];document.getElementById("stS").textContent=f[2];
    const c=document.getElementById("stCta");c.textContent=f[3];c.setAttribute("href",f[4]);
    elapsed=0;t0=performance.now();
    seen.add(si);try{sessionStorage.setItem("afnanSeen",JSON.stringify([...seen]))}catch(_){}
    row.querySelector(`[data-s="${si}"]`).classList.add("seen");
  }
  function tick(now){
    if(!paused){elapsed+=now-t0}
    t0=now;
    const p=Math.min(elapsed/DUR,1),bar=bars.children[fi]&&bars.children[fi].firstChild;
    if(bar)bar.style.transform=`scaleX(${p})`;
    if(p>=1&&!reduce){next();}
    raf=requestAnimationFrame(tick);
  }
  function next(){const s=S[si];if(fi<s.frames.length-1){fi++;render()}else if(si<S.length-1){si++;fi=0;render()}else close()}
  function prev(){if(fi>0){fi--;render()}else if(si>0){si--;fi=S[si].frames.length-1;render()}else{render()}}
  function open(i){lastFocus=document.activeElement;si=i;fi=0;v.hidden=false;document.body.style.overflow="hidden";paused=false;render();cancelAnimationFrame(raf);t0=performance.now();raf=requestAnimationFrame(tick);v.querySelector(".story-x").focus({preventScroll:true})}
  function close(){if(v.hidden)return;v.hidden=true;cancelAnimationFrame(raf);document.body.style.overflow="";if(lastFocus)lastFocus.focus({preventScroll:true})}

  row.addEventListener("click",e=>{const b=e.target.closest(".ring");if(b)open(+b.dataset.s)});
  v.querySelector(".story-x").onclick=close;
  v.querySelectorAll(".story-tap").forEach(z=>{
    z.addEventListener("pointerdown",()=>{downAt=performance.now();paused=true});
    z.addEventListener("pointerup",()=>{paused=false;if(performance.now()-downAt<250){z.classList.contains("next")?next():prev()}});
    z.addEventListener("pointerleave",()=>{paused=false});
  });
  document.addEventListener("keydown",e=>{if(v.hidden)return;if(e.key==="Escape")close();if(e.key==="ArrowLeft")next();if(e.key==="ArrowRight")prev()});
  document.getElementById("stCta").addEventListener("click",e=>{
    const h=e.currentTarget.getAttribute("href");
    if(h.startsWith("#")){e.preventDefault();e.stopPropagation();close();
      const id=h.slice(1);
      if(id==="book"){window.openBooking&&window.openBooking("")}
      else if(typeof openArt==="function"&&ARTICLES.some(a=>a.id===id)){openArt(id)}
      else{const el=document.getElementById(id);if(el)el.scrollIntoView({behavior:reduce?"auto":"smooth"})}
    } else close();
  });
})();
