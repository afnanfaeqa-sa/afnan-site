/* Authorised representatives: remember who brought the visitor, then show that rep's number site-wide.
   Data comes from content/reps.json (injected by build.py as REPS). First rep wins for 90 days.
   The complaints line stays as it is. With no saved rep, nothing on the page changes. */
(function () {
  var REPS = __REPS__;
  var KEY = "afn_rep", DAYS = 90, KEEP = ["583228121"];
  var CENTER = ["920005284", "112988883", "550604553", "555185244",
    "555891227", "555872894", "555160295", "555081952", "555433989",
    "509750006", "555344183", "507360004", "555288906", "555298337",
    "505428885", "508885216", "503888502", "508885096", "504888537"];

  function byCode(c) { c = String(c || "").toUpperCase(); for (var i = 0; i < REPS.length; i++) if (REPS[i].code === c) return REPS[i]; return null; }
  function readCookie() { var m = document.cookie.match(/(?:^|;\s*)afn_rep=([A-Za-z0-9_-]+)/); return m ? m[1] : null; }
  function readStore() {
    try { var v = JSON.parse(localStorage.getItem(KEY) || "null"); if (v && v.code && v.exp > Date.now()) return v.code; } catch (e) {}
    return null;
  }
  function save(code) {
    var exp = Date.now() + DAYS * 864e5;
    try { localStorage.setItem(KEY, JSON.stringify({ code: code, exp: exp })); } catch (e) {}
    var dom = /(^|\.)afnanfaeqasa\.sa$/.test(location.hostname) ? "; domain=.afnanfaeqasa.sa" : "";
    document.cookie = KEY + "=" + code + "; max-age=" + DAYS * 86400 + "; path=/" + dom + "; SameSite=Lax" + (location.protocol === "https:" ? "; Secure" : "");
  }

  // a rep page or a ?ref= link can set the rep, but never replaces one already saved
  var saved = byCode(readCookie()) || byCode(readStore());
  var incoming = byCode(new URLSearchParams(location.search).get("ref"));
  if (!saved && incoming) { save(incoming.code); saved = incoming; }
  else if (saved) save(saved.code); // keep cookie and storage in step
  if (!saved) return;

  var REP = saved, intl = REP.phone, local = "0" + intl.slice(3);
  var shown = local.slice(0, 3) + " " + local.slice(3, 6) + " " + local.slice(6);
  window.AFN_REP = { code: REP.code, name: REP.name, phone: intl, shown: shown };

  function digits(s) { return s.replace(/\D/g, ""); }
  function isCenter(d) {
    for (var i = 0; i < KEEP.length; i++) if (d.slice(-9) === KEEP[i]) return false;
    for (var j = 0; j < CENTER.length; j++) if (d.slice(-CENTER[j].length) === CENTER[j]) return true;
    return false;
  }
  // phone numbers written as text, with or without +966/0 and spaces
  var NUM = /(?:\+?\s?966[\s -]?|0)?(?:\d[\s -]?){8}\d/g;

  function fixLink(a) {
    var h = a.getAttribute("href") || "";
    var m = h.match(/^tel:(.+)$/i);
    var to = null;
    if (m && isCenter(digits(m[1]))) to = "tel:+" + intl;
    else {
      m = h.match(/^https?:\/\/(?:wa\.me|api\.whatsapp\.com\/send\?phone=)\/?(\d+)/i);
      if (m && isCenter(m[1])) to = h.replace(m[1], intl);
    }
    if (to && to !== h) a.setAttribute("href", to); // only write when it changes, so the observer never loops
  }
  function fixText(node) {
    var t = node.nodeValue;
    if (!/\d{3}/.test(t)) return;
    var out = t.replace(NUM, function (s) { return isCenter(digits(s)) ? shown : s; });
    if (out !== t) node.nodeValue = out;
  }
  function walk(root) {
    if (root.nodeType === 3) { fixText(root); return; }
    if (root.nodeType !== 1 || /^(SCRIPT|STYLE|NOSCRIPT|TEXTAREA|INPUT)$/.test(root.tagName)) return;
    if (root.tagName === "A") fixLink(root);
    if (root.querySelectorAll) root.querySelectorAll("a[href]").forEach(fixLink);
    var w = document.createTreeWalker(root, NodeFilter.SHOW_TEXT, {
      acceptNode: function (n) { return n.parentNode && /^(SCRIPT|STYLE|NOSCRIPT|TEXTAREA)$/.test(n.parentNode.nodeName) ? 2 : 1; }
    });
    var n; while ((n = w.nextNode())) fixText(n);
  }
  // the footer lists every center line; once they all read the same, keep one and show whose it is
  function tidyFooter() {
    var box = document.querySelector(".foot-phones");
    if (!box || box.dataset.rep) return;
    box.dataset.rep = "1";
    var seen = {};
    box.querySelectorAll(".fp-main a, .fp-list li").forEach(function (el) {
      var d = digits(el.textContent).slice(-9);
      if (seen[d]) el.hidden = true; else seen[d] = 1;
    });
    var first = box.querySelector(".fp-main a small");
    if (first) first.textContent = "ممثلك المعتمد · " + REP.name;
  }
  function run() {
    walk(document.body);
    tidyFooter();
    new MutationObserver(function (list) {
      list.forEach(function (r) {
        if (r.type === "attributes") fixLink(r.target);
        else r.addedNodes.forEach(walk);
      });
    }).observe(document.body, { childList: true, subtree: true, attributes: true, attributeFilter: ["href"] });
    // links built at click time (booking, chat) are rewritten on the way out
    document.addEventListener("click", function (e) { var a = e.target.closest && e.target.closest("a[href]"); if (a) fixLink(a); }, true);
    var open = window.open;
    window.open = function (u) {
      if (typeof u === "string") { var m = u.match(/wa\.me\/(\d+)/); if (m && isCenter(m[1])) arguments[0] = u.replace(m[1], intl); }
      return open.apply(window, arguments);
    };
  }
  if (document.readyState === "loading") document.addEventListener("DOMContentLoaded", run); else run();
})();
