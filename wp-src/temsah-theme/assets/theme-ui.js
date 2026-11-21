(function(){
  'use strict';

  var cfg = window.TEMSAH_UI || { restBase: '/wp-json/temsah/v1/', nonce: '', cartUrl: '/cart/', loginUrl: '/login/' };
  var $ = function(sel, ctx){ return (ctx || document).querySelector(sel); };
  var $$ = function(sel, ctx){ return Array.from((ctx || document).querySelectorAll(sel)); };

  function restUrl(path){
    var clean = path.replace(/^\/+/, '');
    if (cfg.restRouteMode) {
      return cfg.restBase + encodeURIComponent('temsah/v1/' + clean).replace(/%2F/g, '/');
    }
    return cfg.restBase + clean;
  }

  function apiFetch(path, method, body){
    var opts = {
      method: method || 'GET',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': cfg.nonce || ''
      }
    };
    if (body && method !== 'GET') {
      opts.body = JSON.stringify(body);
    }
    return fetch(restUrl(path), opts).then(function(r){
      return r.json().then(function(data){ return { status: r.status, ok: r.ok, data: data }; });
    });
  }

  var toastEl = null;
  var toastTimer = null;
  function showToast(msg){
    if (!toastEl) {
      toastEl = $('#toast');
      if (!toastEl) {
        toastEl = document.createElement('div');
        toastEl.id = 'toast';
        toastEl.className = 'toast';
        document.body.appendChild(toastEl);
      }
    }
    toastEl.textContent = msg;
    toastEl.classList.add('show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(function(){ toastEl.classList.remove('show'); }, 3200);
  }
  window.temsahShowToast = showToast;

  function toFa(v){
    var en = '0123456789', fa = '۰۱۲۳۴۵۶۷۸۹';
    return String(v).replace(/[0-9]/g, function(d){ return fa[en.indexOf(d)]; });
  }

  function updateCartBadges(count){
    var badge = $('#headerCartCount');
    if (badge) {
      badge.textContent = toFa(count);
      badge.hidden = Number(count) <= 0;
    }
    var mBadge = $('#tmCartCountLabel');
    if (mBadge) {
      mBadge.textContent = toFa(count);
    }
  }

  function initHeader(){
    var megaBtn = $('#megaBtn');
    var megaParent = megaBtn ? megaBtn.closest('.nav-mega-parent') : null;
    var megaPanel = $('#megaPanel');
    if (megaBtn && megaParent) {
      megaBtn.addEventListener('click', function(e){
        e.preventDefault();
        e.stopPropagation();
        var isOpen = megaParent.classList.toggle('open');
        if (megaPanel) megaPanel.classList.toggle('open', isOpen);
        megaBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
      });
      document.addEventListener('click', function(e){
        if (!megaParent.contains(e.target)) {
          megaParent.classList.remove('open');
          if (megaPanel) megaPanel.classList.remove('open');
          megaBtn.setAttribute('aria-expanded', 'false');
        }
      });
    }

    var burger = $('#hamburger');
    var nav = $('#mainNav');
    if (burger && nav) {
      burger.addEventListener('click', function(e){
        e.preventDefault();
        nav.classList.toggle('open');
      });
    }
  }

  function initSearchOverlay(){
    var overlay = $('#tmSearchOverlay');
    if (!overlay) return;
    var input = $('#tmStaticSearchInput');
    var form = $('#tmStaticSearchForm');
    var results = $('.tm-static-search-results', overlay);

    function openSearch(){
      overlay.hidden = false;
      if (input) setTimeout(function(){ input.focus(); }, 60);
      runSearch(input ? input.value.trim() : '');
    }
    function closeSearch(){
      overlay.hidden = true;
    }

    document.addEventListener('click', function(e){
      if (e.target.closest('[data-search-open]')) {
        e.preventDefault();
        openSearch();
      } else if (e.target.closest('[data-search-close]')) {
        e.preventDefault();
        closeSearch();
      }
    });
    document.addEventListener('keydown', function(e){ if (e.key === 'Escape') closeSearch(); });

    var timer = null;
    function runSearch(q){
      if (!results) return;
      apiFetch('search?q=' + encodeURIComponent(q || ''), 'GET').then(function(res){
        var items = (res.data && res.data.items) || [];
        results.innerHTML = '';
        if (!items.length) {
          var empty = document.createElement('p');
          empty.className = 'tm-empty-feedback';
          empty.textContent = 'محصولی با این مشخصات یافت نشد.';
          results.appendChild(empty);
          return;
        }
        items.forEach(function(item){
          var a = document.createElement('a');
          a.className = 'tm-search-result';
          a.href = item.url;
          var b = document.createElement('b');
          b.textContent = item.title;
          var sm = document.createElement('small');
          sm.textContent = item.price_text + ' · ' + item.category;
          a.appendChild(b);
          a.appendChild(sm);
          results.appendChild(a);
        });
      }).catch(function(){});
    }

    if (input) {
      input.addEventListener('input', function(){
        clearTimeout(timer);
        timer = setTimeout(function(){ runSearch(input.value.trim()); }, 220);
      });
    }
    if (form) {
      form.addEventListener('submit', function(e){
        e.preventDefault();
        runSearch(input ? input.value.trim() : '');
      });
    }
  }

  function initCommerceButtons(){
    document.addEventListener('click', function(e){
      var addBtn = e.target.closest('[data-add]');
      if (addBtn) {
        e.preventDefault();
        var pid = Number(addBtn.getAttribute('data-add'));
        if (!pid) return;
        addBtn.disabled = true;
        apiFetch('cart/add', 'POST', { product_id: pid }).then(function(res){
          addBtn.disabled = false;
          if (res.data && res.data.summary) {
            updateCartBadges(res.data.summary.count);
          }
          showToast((res.data && res.data.message) || 'خطا در افزودن به سبد.');
        }).catch(function(){
          addBtn.disabled = false;
          showToast('خطای ارتباط با سرور.');
        });
        return;
      }

      var favBtn = e.target.closest('[data-fav]');
      if (favBtn) {
        e.preventDefault();
        if (!cfg.isLoggedIn) {
          showToast('برای ذخیره در علاقه‌مندی‌ها ابتدا وارد حساب شوید.');
          setTimeout(function(){ window.location.href = cfg.loginUrl; }, 900);
          return;
        }
        var fpid = Number(favBtn.getAttribute('data-fav'));
        apiFetch('wishlist/toggle', 'POST', { product_id: fpid }).then(function(res){
          if (res.data && res.data.ok) {
            favBtn.classList.toggle('active', !!res.data.in_wishlist);
            showToast(res.data.message);
          } else {
            showToast('خطا در بروزرسانی علاقه‌مندی.');
          }
        });
        return;
      }

      var remBtn = e.target.closest('[data-remove-cart]');
      if (remBtn) {
        e.preventDefault();
        var rpid = Number(remBtn.getAttribute('data-remove-cart'));
        apiFetch('cart/remove', 'POST', { product_id: rpid }).then(function(){
          window.location.reload();
        });
        return;
      }

      var moreBtn = e.target.closest('[data-cartmore]');
      if (moreBtn) {
        e.preventDefault();
        var itemCard = moreBtn.closest('.t12-cart-item');
        var ext = itemCard ? $('.t12-extras', itemCard) : null;
        if (ext) ext.hidden = !ext.hidden;
        return;
      }

      var demoBtn = e.target.closest('[data-demo]');
      if (demoBtn) {
        e.preventDefault();
        var url = demoBtn.getAttribute('data-demo-url');
        if (url) {
          window.open(url, '_blank', 'noopener,noreferrer');
        } else {
          showToast(demoBtn.getAttribute('data-demo') || 'پیش‌نمایش آنلاین برای این محصول ثبت نشده است.');
        }
      }
    });

    document.addEventListener('change', function(e){
      var chk = e.target.closest('[data-cart-service]');
      if (!chk) return;
      var pid = Number(chk.getAttribute('data-product-id'));
      var boxes = $$('[data-cart-service][data-product-id="' + pid + '"]:checked');
      var services = boxes.map(function(b){ return b.value; });
      apiFetch('cart/services', 'POST', { product_id: pid, services: services }).then(function(){
        window.location.reload();
      });
    });

    var couponForm = $('#tmCouponForm');
    if (couponForm) {
      couponForm.addEventListener('submit', function(e){
        e.preventDefault();
        var inp = $('#tmCouponInput', couponForm);
        apiFetch('cart/coupon', 'POST', { code: inp ? inp.value.trim() : '' }).then(function(res){
          showToast((res.data && res.data.message) || '');
          if (res.data && res.data.ok) {
            setTimeout(function(){ window.location.reload(); }, 450);
          }
        });
      });
    }
  }

  function initProductPage(){
    // Tabs ([data-product-tab])
    var tabBtns = $$('[data-product-tab]');
    var panels = $$('.tm-tab-panel');
    if (tabBtns.length && panels.length) {
      tabBtns.forEach(function(btn){
        btn.addEventListener('click', function(){
          var id = btn.getAttribute('data-product-tab');
          tabBtns.forEach(function(b){
            var act = b === btn;
            b.classList.toggle('is-active', act);
            b.setAttribute('aria-selected', act ? 'true' : 'false');
            b.setAttribute('tabindex', act ? '0' : '-1');
          });
          panels.forEach(function(p){ p.hidden = (p.id !== id); });
        });
      });
    }

    // V24 Product gallery slider
    var gal = $('[data-tm-gallery]');
    if (gal) {
      var slides = $$('.tm-gallery-slide', gal);
      var track = $('.tm-gallery-track', gal);
      var dots = $$('[data-tm-gallery-dot]', gal);
      var idx = 0;
      function setSlide(n){
        if (!slides.length) return;
        idx = (n + slides.length) % slides.length;
        if (track) track.style.transform = 'translateX(' + (idx * 100) + '%)';
        dots.forEach(function(d, i){ d.setAttribute('aria-current', i === idx ? 'true' : 'false'); });
      }
      gal.addEventListener('click', function(e){
        var nav = e.target.closest('[data-tm-gallery-nav]');
        if (nav) setSlide(idx + (nav.getAttribute('data-tm-gallery-nav') === 'next' ? 1 : -1));
        var dot = e.target.closest('[data-tm-gallery-dot]');
        if (dot) setSlide(Number(dot.getAttribute('data-tm-gallery-dot')));
      });
    }

    document.addEventListener('click', function(e){
      var openFb = e.target.closest('[data-feedback-open]');
      if (openFb) {
        var kind = openFb.getAttribute('data-feedback-open');
        var targetForm = kind === 'review' ? $('#tmReviewForm') : $('#tmQuestionForm');
        if (targetForm) targetForm.hidden = false;
      }
      var closeFb = e.target.closest('[data-feedback-close]');
      if (closeFb) {
        var f = closeFb.closest('form');
        if (f) f.hidden = true;
      }
    });
  }

  function initStories(){
    var cards = $$('.ts-story-card');
    var modal = $('#tmStoryModal');
    if (!cards.length || !modal) return;
    var titleEl = $('#tmStoryTitle', modal);
    var descEl = $('#tmStoryDesc', modal);
    var ctaEl = $('#tmStoryLink', modal);
    var closeEl = $('#tmStoryClose', modal);

    cards.forEach(function(card){
      card.addEventListener('click', function(){
        if (titleEl) titleEl.textContent = card.getAttribute('data-story-title') || '';
        if (descEl) descEl.textContent = card.getAttribute('data-story-desc') || '';
        if (ctaEl) {
          var href = card.getAttribute('data-story-url') || '#';
          ctaEl.href = href;
          ctaEl.hidden = (href === '#');
        }
        modal.hidden = false;
      });
    });
    if (closeEl) closeEl.addEventListener('click', function(){ modal.hidden = true; });
    modal.addEventListener('click', function(e){ if (e.target === modal) modal.hidden = true; });
  }

  function initPortals(){
    document.addEventListener('click', function(e){
      var grpBtn = e.target.closest('[data-portalgroup]');
      if (grpBtn) {
        var grp = grpBtn.closest('.portal-nav-group');
        if (grp) grp.classList.toggle('collapsed');
      }
      var mobBtn = e.target.closest('#portalMenu, [data-portal-toggle]');
      if (mobBtn) {
        e.preventDefault();
        var sb = $('#portalSidebar');
        if (sb) {
          var opened = sb.classList.toggle('open');
          mobBtn.setAttribute('aria-expanded', opened ? 'true' : 'false');
        }
      }
      var tplBtn = e.target.closest('[data-insert-reply]');
      if (tplBtn) {
        var target = $('#vendorTicketReplyBox');
        if (target) {
          target.value = (target.value ? target.value + '\n' : '') + tplBtn.getAttribute('data-insert-reply');
          target.focus();
        }
      }
    });

    var steps = $$('.tm-wiz-step');
    if (steps.length) {
      var cur = 0;
      var pills = $$('.tm-wiz-pill');
      function showStep(idx){
        cur = Math.max(0, Math.min(steps.length - 1, idx));
        steps.forEach(function(s, i){ s.hidden = (i !== cur); });
        pills.forEach(function(p, i){
          p.classList.toggle('is-active', i === cur);
          p.classList.toggle('is-done', i < cur);
        });
      }
      document.addEventListener('click', function(e){
        if (e.target.closest('[data-wiz-next]')) { e.preventDefault(); showStep(cur + 1); }
        if (e.target.closest('[data-wiz-prev]')) { e.preventDefault(); showStep(cur - 1); }
      });
      showStep(0);
    }
  }

  document.addEventListener('DOMContentLoaded', function(){
    initHeader();
    initSearchOverlay();
    initCommerceButtons();
    initProductPage();
    initStories();
    initPortals();
  });
})();
