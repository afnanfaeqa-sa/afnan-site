/* Afnan opening popup: shows /afnan-popup.jpg on every page load. */
(function () {
  var IMG = '/afnan-popup.jpg';
  function show() {
    var css = document.createElement('style');
    css.textContent =
      '#afp{position:fixed;inset:0;z-index:2147483000;display:flex;align-items:center;justify-content:center;padding:16px;background:rgba(5,12,30,.72);opacity:0;transition:opacity .3s}' +
      '#afp.on{opacity:1}' +
      '#afp .box{position:relative;max-width:min(440px,92vw);max-height:90vh;transform:scale(.96);transition:transform .3s}' +
      '#afp.on .box{transform:none}' +
      '#afp img{display:block;width:100%;height:auto;max-height:90vh;object-fit:contain;border-radius:14px;box-shadow:0 30px 60px -20px rgba(0,0,0,.6)}' +
      '#afp .x{position:absolute;top:-12px;left:-12px;width:38px;height:38px;border-radius:50%;border:0;background:#fff;color:#0a1650;font:700 22px/38px Arial,sans-serif;cursor:pointer;box-shadow:0 6px 16px rgba(0,0,0,.35)}' +
      '@media (prefers-reduced-motion:reduce){#afp,#afp .box{transition:none}}';
    document.head.appendChild(css);

    var wrap = document.createElement('div');
    wrap.id = 'afp';
    wrap.setAttribute('role', 'dialog');
    wrap.setAttribute('aria-modal', 'true');
    wrap.setAttribute('aria-label', 'إمكاناتنا في خدمة الوطن');
    wrap.innerHTML =
      '<div class="box"><button class="x" type="button" aria-label="إغلاق">×</button>' +
      '<img src="' + IMG + '" alt="إمكاناتنا في خدمة الوطن - مركز أفنان الفائقة للخدمات الطبية"></div>';

    function close() {
      wrap.classList.remove('on');
      document.removeEventListener('keydown', onKey);
      setTimeout(function () { wrap.remove(); }, 300);
    }
    function onKey(e) { if (e.key === 'Escape') close(); }
    wrap.addEventListener('click', function (e) {
      if (e.target === wrap || e.target.classList.contains('x')) close();
    });
    document.addEventListener('keydown', onKey);

    var img = wrap.querySelector('img');
    img.onload = function () {
      document.body.appendChild(wrap);
      requestAnimationFrame(function () { wrap.classList.add('on'); });
      wrap.querySelector('.x').focus();
    };
  }

  /* fix the site's broken WhatsApp button (wa.me/undefined) */
  var WA = '966555185244';
  function fixWa() {
    var links = document.querySelectorAll('a[href*="wa.me/undefined"]');
    for (var i = 0; i < links.length; i++) links[i].href = links[i].href.replace('wa.me/undefined', 'wa.me/' + WA);
  }
  function watchWa() {
    fixWa();
    if (window.MutationObserver) new MutationObserver(fixWa).observe(document.body, { childList: true, subtree: true, attributes: true, attributeFilter: ['href'] });
  }
  if (document.body) watchWa(); else document.addEventListener('DOMContentLoaded', watchWa);

  if (document.readyState === 'complete') setTimeout(show, 600);
  else window.addEventListener('load', function () { setTimeout(show, 600); });
})();
