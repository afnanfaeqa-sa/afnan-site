/* Conversational booking: a WhatsApp-style assistant that asks a few quick questions,
   then hands the visitor a ready-made WhatsApp message. Opened by any "#book" link,
   by [data-book], or by window.openBooking(serviceName). */
(function(){
  const WA_NUM="966555185244";
  const CATS=window.SITE_CATS||[];
  const PROGRAMS=["برنامج رعاية كبار السن","برنامج التأهيل الحركي","برنامج متابعة ما بعد الجراحة","برنامج متابعة الأمراض المزمنة"];
  const CITIES=["الرياض","جدة","مكة المكرمة","المدينة المنورة","الدمام","الخبر","الأحساء","أبها","القصيم","الطائف"];
  const esc=s=>String(s).replace(/[&<>"]/g,c=>({"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;"}[c]));

  document.body.insertAdjacentHTML("beforeend",`
  <div class="chat" id="chat" hidden>
    <div class="chat-bg" data-chat-close></div>
    <section class="chat-panel" role="dialog" aria-modal="true" aria-labelledby="chatTitle">
      <header class="chat-head">
        <span class="chat-av"><img src="img/mark.png" alt="" width="34" height="30"></span>
        <div class="chat-id"><b id="chatTitle">مساعد الحجز</b><small><i></i>أفنان الفائقة · متاحون الآن</small></div>
        <button class="chat-x" type="button" data-chat-close aria-label="إغلاق"><svg viewBox="0 0 24 24"><path d="M6 6l12 12M18 6 6 18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></button>
      </header>
      <div class="chat-body" id="chatBody" aria-live="polite"></div>
      <footer class="chat-foot" id="chatFoot"></footer>
    </section>
  </div>`);
  const box=document.getElementById("chat"),body=document.getElementById("chatBody"),foot=document.getElementById("chatFoot");
  let st={},lastFocus=null,busy=false;
  const reduce=matchMedia("(prefers-reduced-motion: reduce)").matches;

  function scroll(){body.scrollTop=body.scrollHeight}
  function bot(html){
    return new Promise(res=>{
      const t=document.createElement("div");t.className="msg bot typing";t.innerHTML="<span></span><span></span><span></span>";
      body.appendChild(t);scroll();
      setTimeout(()=>{t.className="msg bot";t.innerHTML=html;scroll();res()},reduce?60:650);
    });
  }
  function me(text){const d=document.createElement("div");d.className="msg me";d.textContent=text;body.appendChild(d);scroll()}
  function chips(list,onPick,extra){
    foot.innerHTML=`<div class="chips">${list.map((c,i)=>`<button type="button" class="qr" data-i="${i}">${esc(c)}</button>`).join("")}</div>`+(extra||"");
    foot.querySelectorAll(".qr").forEach(b=>b.onclick=()=>{if(busy)return;busy=true;foot.innerHTML="";me(list[+b.dataset.i]);onPick(list[+b.dataset.i]).finally(()=>busy=false)});
    const f=foot.querySelector(".qr");if(f&&!matchMedia("(pointer:coarse)").matches)f.focus({preventScroll:true});
  }
  function input(ph,type,onSend,valid,err){
    foot.innerHTML=`<form class="chat-input" novalidate><input type="${type}" ${type==="tel"?'inputmode="tel" dir="ltr" autocomplete="tel"':'autocomplete="name"'} placeholder="${ph}" aria-label="${ph}"><button type="submit" aria-label="إرسال"><svg viewBox="0 0 24 24"><path d="M20 12 4 4l3 8-3 8 16-8Z M7 12h7" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linejoin="round" stroke-linecap="round" transform="scale(-1,1) translate(-24,0)"/></svg></button></form><p class="chat-err" role="alert"></p>`;
    const f=foot.querySelector("form"),i=f.querySelector("input");i.focus({preventScroll:true});
    f.onsubmit=e=>{e.preventDefault();const v=i.value.trim();if(!valid(v)){foot.querySelector(".chat-err").textContent=err;return}foot.innerHTML="";me(v);onSend(v)};
  }

  const steps={
    async start(){
      body.innerHTML="";foot.innerHTML="";
      await bot("أهلًا وسهلًا في <b>أفنان الفائقة</b>. خلّني أرتّب طلبك بخطوات سريعة.");
      if(st.svc){await bot(`ممتاز، بنرتّب لك <b>${esc(st.svc)}</b>.`);return steps.who()}
      await bot("وش نوع الرعاية اللي تحتاجها؟");
      chips([...CATS.map(c=>c[1]),"برامج الرعاية","مو متأكد"],async v=>{
        if(v==="مو متأكد"){st.svc="استشارة لاختيار الخدمة المناسبة";await bot("ولا يهمك، فريقنا بيساعدك تختار بعد ما يفهم الحالة.");return steps.who()}
        const list=v==="برامج الرعاية"?PROGRAMS:(CATS.find(c=>c[1]===v)||[])[3]||[];
        if(list.length===1){st.svc=list[0];return steps.who()}
        await bot("تمام. أي خدمة بالتحديد؟");
        chips(list,async s=>{st.svc=s;return steps.who()});
      });
    },
    async who(){
      await bot("الرعاية لمين؟");
      chips(["لي أنا","لأحد الوالدين","للأم أو المولود","لفرد آخر من الأسرة"],async v=>{st.who=v;return steps.city()});
    },
    async city(){
      await bot("في أي مدينة؟ نغطي جميع مناطق المملكة.");
      chips([...CITIES,"مدينة أخرى"],async v=>{
        if(v==="مدينة أخرى"){await bot("اكتب اسم مدينتك.");return input("اسم المدينة","text",v=>{st.city=v;steps.when()},v=>v.length>1,"اكتب اسم المدينة.")}
        st.city=v;return steps.when();
      });
    },
    async when(){
      await bot("كم مدة الاشتراك اللي تحتاجها؟");
      chips(["شهر","شهرين","٣ شهور","٦ شهور","سنة","أحدد مع الفريق"],async v=>{st.when=v;return steps.pref()});
    },
    async pref(){
      await bot("تفضّل ممرض ولا ممرضة؟");
      chips(["بدون تفضيل","ممرضة","ممرض"],async v=>{st.pref=v;return steps.name()});
    },
    async name(){
      await bot("آخر خطوتين. وش اسمك الكريم؟");
      input("الاسم","text",v=>{st.name=v;steps.phone()},v=>v.length>1,"اكتب اسمك عشان نعرف بمن نتواصل.");
    },
    async phone(){
      await bot(`تشرفنا يا ${esc(st.name)}. وش رقم جوالك؟`);
      input("05xxxxxxxx","tel",v=>{st.phone=v;steps.done()},v=>/^[0-9+\s]{9,15}$/.test(v),"اكتب رقم جوال صحيح، مثل 05xxxxxxxx.");
    },
    async done(){
      const rows=[["الخدمة",st.svc],["المريض",st.who],["المدينة",st.city],["المدة",st.when],["تفضيل الكادر",st.pref],["الاسم",st.name],["الجوال",st.phone]];
      const msg="السلام عليكم، أرغب بطلب رعاية منزلية من مركز أفنان الفائقة.\n"+rows.map(([k,v])=>`${k}: ${v}`).join("\n");
      const link=`https://wa.me/${WA_NUM}?text=${encodeURIComponent(msg)}`;
      await bot(`طلبك جاهز:<dl class="sum">${rows.map(([k,v])=>`<dt>${k}</dt><dd>${esc(v)}</dd>`).join("")}</dl>اضغط الزر تحت ويوصلنا طلبك على واتساب، ونتواصل معك مباشرة.`);
      foot.innerHTML=`<a class="btn btn-green chat-send" href="${link}" target="_blank" rel="noopener"><svg viewBox="0 0 24 24"><path fill="currentColor" d="M12 2.2a9.7 9.7 0 0 0-8.3 14.8L2.4 21.8l4.9-1.3A9.7 9.7 0 1 0 12 2.2Zm4.4 11.7c-.2-.1-1.4-.7-1.7-.8-.2-.1-.4-.1-.5.1l-.8 1c-.1.2-.3.2-.5.1a6.6 6.6 0 0 1-3.3-2.9c-.2-.4.2-.4.7-1.3.1-.2 0-.3 0-.4l-.8-1.8c-.2-.5-.4-.4-.5-.4h-.5a.9.9 0 0 0-.7.3 2.8 2.8 0 0 0-.9 2.1 4.9 4.9 0 0 0 1 2.6 11.2 11.2 0 0 0 4.3 3.8c1.6.7 2.2.7 3 .6a2.6 2.6 0 0 0 1.7-1.2 2.1 2.1 0 0 0 .2-1.2c-.1-.1-.3-.2-.5-.3Z"/></svg>أرسل الطلب على واتساب</a><button type="button" class="chat-restart">تعديل الطلب</button>`;
      foot.querySelector(".chat-restart").onclick=()=>{const s=st.svc;st={};steps.start()};
    }
  };

  window.openBooking=function(svc,answers){
    st=Object.assign({},answers||{});if(svc)st.svc=svc;
    lastFocus=document.activeElement;box.hidden=false;document.body.style.overflow="hidden";document.body.classList.add("chat-open");
    if(answers&&answers.cat){ // started from a quick chip outside the panel
      body.innerHTML="";foot.innerHTML="";
      (async()=>{await bot("أهلًا وسهلًا في <b>أفنان الفائقة</b>. خلّني أرتّب طلبك بخطوات سريعة.");await bot("وش نوع الرعاية اللي تحتاجها؟");me(answers.cat);
        const list=answers.cat==="برامج الرعاية"?PROGRAMS:(CATS.find(c=>c[1]===answers.cat)||[])[3]||[];
        if(answers.cat==="مو متأكد"){st.svc="استشارة لاختيار الخدمة المناسبة";await bot("ولا يهمك، فريقنا بيساعدك تختار بعد ما يفهم الحالة.");return steps.who()}
        if(list.length===1){st.svc=list[0];return steps.who()}
        await bot("تمام. أي خدمة بالتحديد؟");chips(list,async s=>{st.svc=s;return steps.who()});
      })();
    } else steps.start();
    box.querySelector(".chat-x").focus({preventScroll:true});
  };
  function close(){if(box.hidden)return;box.hidden=true;document.body.classList.remove("chat-open");document.body.style.overflow="";if(lastFocus)lastFocus.focus({preventScroll:true})}
  box.addEventListener("click",e=>{if(e.target.closest("[data-chat-close]"))close()});
  document.addEventListener("keydown",e=>{if(e.key==="Escape")close()});
  document.addEventListener("click",e=>{
    const a=e.target.closest('a[href="#book"],a[href="index.html#book"],[data-book]');
    if(!a||a.closest(".chat"))return;
    e.preventDefault();
    if(typeof window.closeMenus==="function")window.closeMenus();
    window.openBooking(a.dataset.svc||document.body.dataset.svc||"");
  },true);

  /* floating assistant (desktop) + one-time nudge (phone tab bar) */
  const svcName=document.body.dataset.svc||"";
  const tipText=svcName?`تحب أرتّب لك طلب ${esc(svcName)}؟`:"تحتاج رعاية في البيت؟ أرتّب لك طلبك.";
  document.body.insertAdjacentHTML("beforeend",`<div class="afab" id="afab">
    <div class="afab-tip" id="afabTip" hidden role="status">
      <button type="button" class="afab-x" aria-label="إخفاء">×</button>
      <b>مساعد الحجز</b><p>${tipText}</p>
      <button type="button" class="btn btn-green afab-go" data-book data-svc="${esc(svcName)}">ابدأ الحجز</button>
    </div>
    <button type="button" class="afab-btn" data-book data-svc="${esc(svcName)}" aria-label="افتح مساعد الحجز"><img src="img/mark.png" alt="" width="40" height="35"><i></i></button>
  </div>`);
  const tip=document.getElementById("afabTip");
  const seenKey="afnanNudge";
  let done=false;try{done=sessionStorage.getItem(seenKey)==="1"}catch(_){}
  function nudge(){
    if(done)return;done=true;try{sessionStorage.setItem(seenKey,"1")}catch(_){}
    if(matchMedia("(min-width:1081px)").matches){tip.hidden=false}
    else{const f=document.querySelector(".tabbar .fab");if(f){f.classList.add("nudge");setTimeout(()=>f.classList.remove("nudge"),2600)}}
  }
  tip.querySelector(".afab-x").onclick=()=>{tip.hidden=true};
  document.addEventListener("click",e=>{if(e.target.closest("[data-book]"))tip.hidden=true});
  if(!done){
    const card=document.querySelector(".book-bar");
    if(card&&!svcName&&"IntersectionObserver" in window){
      const io=new IntersectionObserver(es=>{const r=es[0].boundingClientRect;if(!es[0].isIntersecting&&r.bottom<0){io.disconnect();nudge()}});io.observe(card);
    } else setTimeout(nudge,20000);
  }
  if(location.hash==="#book")setTimeout(()=>window.openBooking(document.body.dataset.svc||""),400);
})();
