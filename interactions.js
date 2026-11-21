/* TEMSAH V32: full interactive behaviors. All content records live in HTML. */
(() => {
  'use strict';

  const $ = (q, root = document) => root.querySelector(q);
  const $$ = (q, root = document) => [...root.querySelectorAll(q)];

  const CART_KEY = 'temsah-v30-cart';
  const FAV_KEY = 'temsah-v30-favs';
  const SVC_KEY = 'temsah-v30-services';
  const COUPON_KEY = 'temsah-v30-coupon';
  const GW_KEY = 'temsah-v30-gateway';

  const DEFAULT_CART = ['1', '2'];
  const DEFAULT_FAVS = ['1', '3', '7'];

  const PRODUCT_META = {
    '1': { name: 'قالب فروشگاهی آریو | Ario Shop', seller: 'استودیو آرکا', cat: 'قالب وردپرس', price: 985000, orig: 1240000, ver: '2.8.1' },
    '2': { name: 'افزونه فرم‌ساز پیشرفته فرمینو', seller: 'نئوکد', cat: 'افزونه وردپرس', price: 485000, orig: 485000, ver: '3.4.0' },
    '3': { name: 'قالب شرکتی HTML مدل آوان', seller: 'دیزاین‌لب', cat: 'قالب HTML', price: 380000, orig: 520000, ver: '1.3.0' },
    '4': { name: 'اسکریپت مدیریت پروژه تسکینو', seller: 'کدنما', cat: 'اسکریپت PHP', price: 1390000, orig: 1390000, ver: '2.1.0' },
    '5': { name: 'قالب خبری و مجله‌ای آوید', seller: 'استودیو آرکا', cat: 'قالب وردپرس', price: 645000, orig: 645000, ver: '1.9.2' },
    '6': { name: 'افزونه افزایش سرعت اسپارک کش', seller: 'نئوکد', cat: 'افزونه وردپرس', price: 295000, orig: 395000, ver: '4.0.1' },
    '7': { name: 'داشبورد مدیریتی نئوادمین', seller: 'دیزاین‌لب', cat: 'پنل مدیریت', price: 765000, orig: 765000, ver: '2.4.0' },
    '8': { name: 'اسکریپت رزرو آنلاین نوبان', seller: 'کدنما', cat: 'اسکریپت PHP', price: 1090000, orig: 1390000, ver: '3.1.0' },
    '9': { name: 'قالب فروشگاهی ویکان', seller: 'دیزاین‌لب', cat: 'قالب وردپرس', price: 740000, orig: 740000, ver: '1.7.0' },
    '10': { name: 'سورس اپلیکیشن مدیریت وظایف', seller: 'کدنما', cat: 'سورس اپلیکیشن', price: 690000, orig: 690000, ver: '1.4.2' },
    '11': { name: 'افزونه مگامنوی حرفه‌ای ویژن', seller: 'نئوکد', cat: 'افزونه وردپرس', price: 390000, orig: 510000, ver: '2.2.0' },
    '12': { name: 'کیت رابط کاربری فلو | Flow UI', seller: 'دیزاین‌لب', cat: 'فایل رابط کاربری', price: 540000, orig: 540000, ver: '1.8.0' }
  };

  const SERVICE_PRICES = { support: 215000, install: 390000, seo: 265000, training: 95000 };

  const SELLER_META = {
    'استودیو آرکا': {
      letter: 'آ', role: 'طراحی قالب‌های وردپرس و تجربه‌های فروشگاهی',
      sales: '۶۵۶', products: '۲', rating: '۴.۸',
      medals: [
        { color: 'violet', icon: 'work.svg', earned: true },
        { color: 'pink', icon: 'bag.svg', earned: true },
        { color: 'violet', icon: 'buy.svg', earned: true },
        { color: 'green', icon: 'chat.svg', earned: true },
        { color: 'gold', icon: 'activity.svg', earned: false }
      ]
    },
    'نئوکد': {
      letter: 'ن', role: 'توسعه افزونه و ابزارهای تخصصی وردپرس',
      sales: '۱٬۱۰۸', products: '۳', rating: '۴.۸',
      medals: [
        { color: 'violet', icon: 'work.svg', earned: true },
        { color: 'pink', icon: 'bag.svg', earned: true },
        { color: 'violet', icon: 'buy.svg', earned: true },
        { color: 'gold', icon: 'activity.svg', earned: true },
        { color: 'green', icon: 'chat.svg', earned: true }
      ]
    },
    'دیزاین‌لب': {
      letter: 'د', role: 'طراحی رابط کاربری، پنل مدیریت و قالب HTML',
      sales: '۴۹۷', products: '۴', rating: '۴.۸',
      medals: [
        { color: 'violet', icon: 'work.svg', earned: true },
        { color: 'pink', icon: 'bag.svg', earned: true },
        { color: 'blue', icon: 'document.svg', earned: true },
        { color: 'green', icon: 'chat.svg', earned: true },
        { color: 'gold', icon: 'activity.svg', earned: false }
      ]
    },
    'کدنما': {
      letter: 'ک', role: 'اسکریپت‌های کاربردی و محصولات تحت وب',
      sales: '۴۲۵', products: '۳', rating: '۴.۸',
      medals: [
        { color: 'violet', icon: 'work.svg', earned: true },
        { color: 'pink', icon: 'bag.svg', earned: true },
        { color: 'blue', icon: 'document.svg', earned: true },
        { color: 'green', icon: 'chat.svg', earned: true },
        { color: 'gold', icon: 'activity.svg', earned: false }
      ]
    }
  };

  function readList(k, fallback) {
    try {
      const raw = localStorage.getItem(k);
      if (raw === null && fallback) {
        localStorage.setItem(k, JSON.stringify(fallback));
        return [...fallback];
      }
      const a = JSON.parse(raw || '[]');
      return Array.isArray(a) ? a.map(String) : [];
    } catch {
      return fallback ? [...fallback] : [];
    }
  }

  function readObj(k) {
    try {
      const o = JSON.parse(localStorage.getItem(k) || '{}');
      return o && typeof o === 'object' && !Array.isArray(o) ? o : {};
    } catch {
      return {};
    }
  }

  function saveJson(k, v) {
    try { localStorage.setItem(k, JSON.stringify(v)); } catch {}
  }

  function readStr(k, def = '') {
    try { return localStorage.getItem(k) ?? def; } catch { return def; }
  }

  function saveStr(k, v) {
    try { localStorage.setItem(k, v); } catch {}
  }

  const faDigits = n => Number(n || 0).toLocaleString('fa-IR');
  const money = n => faDigits(n) + ' تومان';
  const normFa = s => (s || '').replace(/\u200c/g, ' ').replace(/\s+/g, ' ').trim().toLocaleLowerCase('fa');
  const parseFaNum = s => {
    const map = { '۰': '0', '۱': '1', '۲': '2', '۳': '3', '۴': '4', '۵': '5', '۶': '6', '۷': '7', '۸': '8', '۹': '9' };
    const clean = (s || '').replace(/[۰-۹]/g, d => map[d]).replace(/[^\d]/g, '');
    return Number(clean || 0);
  };

  const toast = t => {
    const el = $('#toast');
    if (!el) return;
    el.textContent = t;
    el.classList.add('visible');
    clearTimeout(window.tmToast);
    window.tmToast = setTimeout(() => el.classList.remove('visible'), 2800);
  };

  /* -------------------- Legacy Query Param Routing -------------------- */
  const pageName = location.pathname.split('/').pop() || 'index.html';
  const params = new URLSearchParams(location.search);

  if (pageName === 'product.html' && params.get('id')) {
    const id = Number(params.get('id'));
    if (id >= 2 && id <= 12) location.replace(`product-${id}.html`);
  } else if (pageName === 'article.html' && params.get('id')) {
    const id = Number(params.get('id'));
    if (id >= 2 && id <= 4) location.replace(`article-${id}.html`);
  } else if (pageName === 'seller.html' && params.get('id')) {
    const id = Number(params.get('id'));
    if (id >= 2 && id <= 4) location.replace(`seller-${id}.html`);
  } else if (pageName === 'order-detail.html' && params.get('id')) {
    const id = Number(params.get('id'));
    if (id >= 2 && id <= 4) location.replace(`order-detail-${id}.html`);
  }

  if (pageName === 'vendor-edit-product.html' && params.get('id')) {
    const p = PRODUCT_META[params.get('id')];
    const form = $('#v11ProductForm');
    if (p && form) {
      const nameIn = form.querySelector('input[name="name"]');
      const verIn = form.querySelector('input[name="version"]');
      const demoIn = form.querySelector('input[name="demo"]');
      const catIn = form.querySelector('input[readonly]');
      if (nameIn) nameIn.value = p.name;
      if (verIn) verIn.value = p.ver;
      if (demoIn) demoIn.value = `https://demo.temsah.me/${params.get('id')}`;
      if (catIn) catIn.value = p.cat;
    }
  }

  if (pageName === 'submit-product-form.html' && params.get('cat')) {
    const catIn = $('#v11ProductForm input[readonly]');
    if (catIn) catIn.value = params.get('cat');
  }

  if (pageName === 'ticket-view.html' && params.get('id')) {
    const tid = params.get('id');
    const presets = {
      '#T-1842': ['خطا در نصب قالب فروشگاهی', 'قالب فروشگاهی آروین', 'پاسخ فروشنده'],
      '#T-1819': ['درخواست راهنمای تنظیمات فرم', 'افزونه فرم‌ساز پیشرفته فرمینو', 'باز'],
      '#T-1733': ['سؤال درباره لایسنس چنددامنه', 'اسکریپت مدیریت پروژه تسکینو', 'بسته‌شده']
    };
    if (presets[tid]) {
      const h2 = $('.v10-thread-top h2');
      const spans = $$('.v10-thread-meta > span');
      if (h2) h2.textContent = presets[tid][0];
      if (spans[0]) spans[0].innerHTML = `<span class="v9-badge blue">${presets[tid][2]}</span>`;
      if (spans[1]) spans[1].textContent = tid;
      if (spans[2]) spans[2].textContent = presets[tid][1];
    }
  }

  /* -------------------- Product Gallery & Tabs -------------------- */
  function gallery(g, n) {
    const slides = $$('.tm-gallery-slide', g);
    if (!slides.length) return;
    n = (n + slides.length) % slides.length;
    g.dataset.current = n;
    const track = $('.tm-gallery-track', g);
    if (track) track.style.transform = `translateX(${n * 100}%)`;
    $$('[data-tm-gallery-dot]', g).forEach((x, i) => {
      if (i === n) x.setAttribute('aria-current', 'true');
      else x.removeAttribute('aria-current');
    });
  }
  $$('[data-tm-gallery]').forEach(g => gallery(g, 0));

  function tab(id, changeUrl = true) {
    const target = $$('[data-product-tab]').find(x => x.dataset.productTab === id);
    if (!target) return;
    $$('[data-product-tab]').forEach(b => {
      const on = b === target;
      b.classList.toggle('is-active', on);
      b.setAttribute('aria-selected', String(on));
      b.tabIndex = on ? 0 : -1;
    });
    $$('.tm-tab-panel').forEach(p => { p.hidden = p.id !== id; });
    if (changeUrl && history.replaceState) history.replaceState(null, '', '#' + encodeURIComponent(id));
  }
  if ($('[data-product-tab]')) {
    tab(decodeURIComponent(location.hash.substring(1)) || 'description', false);
  }

  /* -------------------- Cart & Checkout -------------------- */
  function syncCount() {
    const len = readList(CART_KEY, DEFAULT_CART).length;
    $$('.cart-count').forEach(x => { x.textContent = faDigits(len); });
  }

  function renderCart() {
    const list = $('.t12-cart-list');
    if (!list) return;
    list.replaceChildren();
    const ids = readList(CART_KEY, DEFAULT_CART);
    const services = readObj(SVC_KEY);
    const coupon = readStr(COUPON_KEY, '');

    let sumPrice = 0;
    let sumOriginal = 0;
    let sumServices = 0;

    for (const id of ids) {
      const tpl = $('#tmCartRow-' + id);
      if (!tpl) continue;
      const frag = tpl.content.cloneNode(true);
      const itemServices = Array.isArray(services[id]) ? services[id] : [];
      frag.querySelectorAll('[data-cartservice]').forEach(cb => {
        if (itemServices.includes(cb.value)) {
          cb.checked = true;
          sumServices += Number(cb.dataset.servicePrice || SERVICE_PRICES[cb.value] || 0);
        }
      });
      if (itemServices.length) {
        const extras = frag.querySelector('.t12-extras');
        if (extras) extras.hidden = false;
      }
      list.append(frag);
      const price = Number(tpl.dataset.price || 0);
      const orig = Number(tpl.dataset.original || price);
      sumPrice += price;
      sumOriginal += orig;
    }

    if (!list.children.length) {
      const empty = $('#tmEmptyCart');
      if (empty) list.append(empty.content.cloneNode(true));
    }

    const prodDiscount = Math.max(0, sumOriginal - sumPrice);
    const couponDiscount = (coupon.toUpperCase() === 'TEMSAH10' && sumPrice > 0) ? Math.round(sumPrice * 0.1) : 0;
    const finalTotal = Math.max(0, sumPrice + sumServices - couponDiscount);

    const pPrice = $('#tmProductsPrice') || $('.t12-price-box > div:first-child b');
    const pCount = $('.t12-price-box > div:first-child span');
    const pDisc = $('#tmProductsDiscount') || $('.t12-price-box > div:nth-child(2) b');
    const sRow = $('#tmServicesRow');
    const sTotal = $('#tmServicesTotal');
    const cRow = $('#tmCouponRow');
    const cTotal = $('#tmCouponTotal');
    const cBadge = $('#tmCouponBadge');
    const cInput = $('#t12-coupon-input');
    const totalNode = $('#tmFinalTotal') || $('.t12-total strong');

    if (pCount) pCount.textContent = `قیمت محصولات (${faDigits(ids.length)})`;
    if (pPrice) pPrice.textContent = money(sumOriginal);
    if (pDisc) pDisc.textContent = money(prodDiscount);
    if (sRow && sTotal) {
      sRow.hidden = sumServices <= 0;
      sTotal.textContent = money(sumServices);
    }
    if (cRow && cTotal) {
      cRow.hidden = couponDiscount <= 0;
      cTotal.textContent = '−' + money(couponDiscount);
    }
    if (cBadge) cBadge.hidden = couponDiscount <= 0;
    if (cInput && coupon && !cInput.value) cInput.value = coupon;
    if (totalNode) totalNode.innerHTML = `${faDigits(finalTotal)} <small>تومان</small>`;

    const gw = readStr(GW_KEY, 'saman');
    $$('input[name="t12-gateway"]').forEach(r => {
      const checked = r.value === gw;
      r.checked = checked;
      r.closest('.t12-payment')?.classList.toggle('active', checked);
    });
  }

  function syncCheckout() {
    const sidebar = $('.t13-checkout-sidebar');
    if (!sidebar) return;
    const ids = readList(CART_KEY, DEFAULT_CART);
    const services = readObj(SVC_KEY);
    const coupon = readStr(COUPON_KEY, '');
    const gw = readStr(GW_KEY, 'saman');

    // Sync payment method visual
    const gwMap = { saman: 0, pasargad: 1, wallet: 2 };
    const methods = $$('.t13-methods .t13-method');
    if (methods.length && gw in gwMap) {
      methods.forEach((m, i) => {
        const chosen = i === gwMap[gw];
        m.classList.toggle('chosen', chosen);
        const existingBadge = m.querySelector('.v9-badge');
        if (chosen && !existingBadge) {
          const b = document.createElement('span');
          b.className = 'v9-badge green';
          b.textContent = 'انتخاب‌شده';
          m.append(b);
        } else if (!chosen && existingBadge) {
          existingBadge.remove();
        }
      });
    }

    // Remove existing product rows and rebuild from cart
    $$('.t13-checkout-product', sidebar).forEach(el => el.remove());
    const sums = $('.t13-checkout-sums', sidebar);
    if (!sums) return;

    let sumOrig = 0, sumPrice = 0, sumSvc = 0;
    for (const id of ids) {
      const p = PRODUCT_META[id];
      if (!p) continue;
      sumPrice += p.price;
      sumOrig += p.orig;
      const itemSvcs = Array.isArray(services[id]) ? services[id] : [];
      itemSvcs.forEach(s => { sumSvc += SERVICE_PRICES[s] || 0; });

      const row = document.createElement('div');
      row.className = 't13-checkout-product';
      const extraNote = itemSvcs.length ? ` (+${faDigits(itemSvcs.length)} سرویس)` : '';
      row.innerHTML = `<div><b>${p.name}</b><small>${p.seller}${extraNote}</small></div><strong>${money(p.price)}</strong>`;
      sidebar.insertBefore(row, sums);
    }

    if (!ids.length) {
      const empty = document.createElement('div');
      empty.className = 't13-empty';
      empty.innerHTML = 'سبد خرید خالی است. <a href="catalog.html">بازگشت به فروشگاه</a>';
      sidebar.insertBefore(empty, sums);
    }

    const disc = Math.max(0, sumOrig - sumPrice);
    const couponDisc = (coupon.toUpperCase() === 'TEMSAH10' && sumPrice > 0) ? Math.round(sumPrice * 0.1) : 0;
    const total = Math.max(0, sumPrice + sumSvc - couponDisc);

    const rows = $$('.t13-checkout-sums > div', sidebar);
    if (rows[0]) rows[0].querySelector('b').textContent = money(sumOrig);
    if (rows[1]) rows[1].querySelector('b').textContent = '−' + money(disc);
    if (rows[2]) rows[2].querySelector('b').textContent = money(sumSvc);
    if (rows[3]) rows[3].querySelector('b').textContent = '−' + money(couponDisc);
    const totEl = $('.t13-checkout-total strong', sidebar);
    if (totEl) totEl.textContent = money(total);
  }

  /* -------------------- Favorites & Wishlist Sync -------------------- */
  function syncFavs() {
    const favs = readList(FAV_KEY, DEFAULT_FAVS);
    $$('[data-fav]').forEach(btn => {
      const on = favs.includes(String(btn.dataset.fav));
      btn.classList.toggle('is-fav', on);
      btn.setAttribute('aria-pressed', String(on));
    });

    const wGrid = $('#wishlistGrid');
    const wEmpty = $('#wishlistEmpty');
    if (wGrid) {
      let visible = 0;
      $$('.product-card', wGrid).forEach(card => {
        const pid = String(card.dataset.productId || '');
        const show = favs.includes(pid);
        card.hidden = !show;
        if (show) visible++;
      });
      if (wEmpty) wEmpty.hidden = visible > 0;
    }
  }

  /* -------------------- Search Overlay -------------------- */
  function searchOpen() {
    const overlay = $('#tmSearchOverlay');
    if (!overlay) return;
    overlay.hidden = false;
    overlay.classList.add('open');
    $('#tmStaticSearchInput')?.focus();
  }

  function searchClose() {
    const overlay = $('#tmSearchOverlay');
    if (overlay) {
      overlay.hidden = true;
      overlay.classList.remove('open');
    }
  }

  function searchFilter(text) {
    const q = normFa(text);
    $$('.tm-search-result').forEach(x => {
      x.hidden = q ? !normFa(x.textContent).includes(q) : false;
    });
  }

  $('#tmStaticSearchInput')?.addEventListener('input', e => searchFilter(e.target.value));
  $$('.header-search input,.hero-search input').forEach(x => {
    x.addEventListener('focus', () => {
      if ($('#tmSearchOverlay')) {
        x.blur();
        searchOpen();
      }
    });
  });

  /* -------------------- Catalog Filter & Sort -------------------- */
  function initCatalog() {
    const list = $('#catalogList');
    if (!list) return;

    const catMap = {
      all: '',
      wordpress: 'قالب وردپرس',
      plugin: 'افزونه وردپرس',
      html: 'قالب HTML',
      script: 'اسکریپت PHP',
      dashboard: 'پنل مدیریت',
      app: 'سورس اپلیکیشن',
      ui: 'رابط کاربری'
    };

    let activeCat = params.get('category') || 'all';
    let activeSort = params.get('sort') || 'popular';
    let queryText = params.get('q') || '';
    let freeOnly = false;
    let saleOnly = activeSort === 'discount';

    const saleCb = $('#saleOnly');
    const freeCb = $('#freeOnly');
    if (saleCb) saleCb.checked = saleOnly;

    const cards = $$('.product-card', list);
    const meta = cards.map(c => {
      const pid = Number(c.dataset.productId || 0);
      const typeTxt = $('.card-type', c)?.childNodes[0]?.textContent?.trim() || '';
      const priceNum = parseFaNum($('.card-price strong', c)?.textContent);
      const salesText = $$('.card-meta span', c)[1]?.textContent || '';
      const salesNum = parseFaNum(salesText);
      const hasSale = !!$('.discount-badge', c) || !!$('.card-price del', c);
      return { el: c, pid, typeTxt, priceNum, salesNum, hasSale, text: normFa(c.textContent) };
    });

    function ensureEmptyBox() {
      let box = $('#tmCatalogEmpty');
      if (!box) {
        box = document.createElement('div');
        box.id = 'tmCatalogEmpty';
        box.className = 'tm-empty-box';
        box.hidden = true;
        box.innerHTML = '<img alt="" class="ico" src="assets/icons/search.svg"/><h3>محصولی با این فیلترها پیدا نشد</h3><p>می‌توانید فیلترها را بازنشانی کنید تا تمام ۱۲ محصول نمایش داده شوند.</p><button class="v9-action" id="tmCatalogResetBtn" type="button">حذف فیلترها</button>';
        list.parentNode.insertBefore(box, list.nextSibling);
      }
      return box;
    }

    function apply() {
      const catLabel = catMap[activeCat] || '';
      const q = normFa(queryText);

      // Update active UI controls
      $$('[data-filtercat]').forEach(b => b.classList.toggle('active', b.dataset.filtercat === activeCat));
      $$('[data-sort]').forEach(b => b.classList.toggle('active', b.dataset.sort === activeSort));

      // Sort
      const sorted = [...meta].sort((a, b) => {
        if (activeSort === 'cheap') return a.priceNum - b.priceNum;
        if (activeSort === 'expensive') return b.priceNum - a.priceNum;
        if (activeSort === 'new') return b.pid - a.pid;
        if (activeSort === 'discount') return (b.hasSale ? 1 : 0) - (a.hasSale ? 1 : 0) || b.salesNum - a.salesNum;
        return b.salesNum - a.salesNum;
      });
      sorted.forEach(item => list.append(item.el));

      // Filter
      let visible = 0;
      for (const item of sorted) {
        let ok = true;
        if (catLabel && !item.typeTxt.includes(catLabel)) ok = false;
        if (freeOnly && item.priceNum > 0) ok = false;
        if (saleOnly && !item.hasSale) ok = false;
        if (q && !item.text.includes(q)) ok = false;
        item.el.hidden = !ok;
        if (ok) visible++;
      }

      const cCount = $('#catalogCount');
      const rCount = $('#resultCount');
      if (cCount) cCount.textContent = faDigits(visible);
      if (rCount) rCount.textContent = `${faDigits(visible)} محصول`;
      ensureEmptyBox().hidden = visible > 0;
    }

    document.addEventListener('click', e => {
      const fc = e.target.closest('[data-filtercat]');
      if (fc) { activeCat = fc.dataset.filtercat; apply(); return; }
      const sb = e.target.closest('[data-sort]');
      if (sb) { activeSort = sb.dataset.sort; apply(); return; }
      if (e.target.closest('#resetFilters') || e.target.closest('#tmCatalogResetBtn')) {
        activeCat = 'all'; activeSort = 'popular'; queryText = ''; freeOnly = false; saleOnly = false;
        if (freeCb) freeCb.checked = false;
        if (saleCb) saleCb.checked = false;
        apply();
        toast('فیلترها بازنشانی شدند');
      }
    });
    freeCb?.addEventListener('change', () => { freeOnly = freeCb.checked; apply(); });
    saleCb?.addEventListener('change', () => { saleOnly = saleCb.checked; apply(); });

    apply();
  }
  initCatalog();

  /* -------------------- Product Feedback Persistence -------------------- */
  function initProductFeedback() {
    if (!$('#tmQuestionForm') && !$('#tmReviewForm')) return;
    const fbKey = 'temsah-v30-feedback-' + pageName;
    const saved = readObj(fbKey);

    function renderList(panelId, items) {
      const panel = document.getElementById(panelId);
      if (!panel || !Array.isArray(items) || !items.length) return;
      $$('.tm-user-feedback.is-session', panel).forEach(n => n.remove());
      const form = panel.querySelector('form');
      for (const item of items) {
        const div = document.createElement('div');
        div.className = 'tm-user-feedback is-session';
        const stars = item.stars ? `<span class="tm-feedback-stars">${'★'.repeat(item.stars)}${'☆'.repeat(5 - item.stars)}</span>` : '';
        div.innerHTML = `<strong></strong>${stars}<small></small><p></p>`;
        div.querySelector('strong').textContent = item.author || 'کاربر تمساح';
        div.querySelector('small').textContent = ' · هم‌اکنون (نمایشی در مرورگر)';
        div.querySelector('p').textContent = item.text || '';
        if (form) panel.insertBefore(div, form);
        else panel.append(div);
      }
    }

    if (saved.questions) renderList('questions', saved.questions);
    if (saved.reviews) renderList('reviews', saved.reviews);

    $('#tmQuestionForm')?.addEventListener('submit', e => {
      e.preventDefault();
      const form = e.target;
      const inputs = form.querySelectorAll('input, textarea');
      const author = inputs[0]?.value?.trim() || 'کاربر تمساح';
      const text = inputs[1]?.value?.trim() || '';
      if (!text) return;
      const cur = readObj(fbKey);
      cur.questions = Array.isArray(cur.questions) ? cur.questions : [];
      cur.questions.push({ author, text });
      saveJson(fbKey, cur);
      renderList('questions', cur.questions);
      form.reset();
      form.hidden = true;
      toast('پرسش شما به صورت نمایشی در این مرورگر ثبت شد');
    });

    $('#tmReviewForm')?.addEventListener('submit', e => {
      e.preventDefault();
      const form = e.target;
      const author = form.querySelector('input')?.value?.trim() || 'خریدار نمونه';
      const stars = Number(form.querySelector('select')?.value || 5);
      const text = form.querySelector('textarea')?.value?.trim() || '';
      if (!text) return;
      const cur = readObj(fbKey);
      cur.reviews = Array.isArray(cur.reviews) ? cur.reviews : [];
      cur.reviews.push({ author, stars, text });
      saveJson(fbKey, cur);
      renderList('reviews', cur.reviews);
      form.reset();
      form.hidden = true;
      toast('دیدگاه شما به صورت نمایشی در این مرورگر ثبت شد');
    });
  }
  initProductFeedback();

  /* -------------------- Medal & Seller Hover Tooltips -------------------- */
  function initTooltips() {
    if (!$('[data-tm-medal]') && !$('[data-tm-seller-hover]')) return;
    const tip = document.createElement('div');
    tip.className = 'tm-medal-tooltip';
    tip.hidden = true;
    tip.setAttribute('role', 'tooltip');
    document.body.append(tip);

    let hideTimer = null;
    function placeTip(anchor) {
      const r = anchor.getBoundingClientRect();
      tip.hidden = false;
      const tr = tip.getBoundingClientRect();
      let top = r.top - tr.height - 10;
      let side = 'top';
      if (top < 10) {
        top = r.bottom + 10;
        side = 'bottom';
      }
      let left = r.left + r.width / 2 - tr.width / 2;
      left = Math.max(10, Math.min(window.innerWidth - tr.width - 10, left));
      tip.dataset.side = side;
      tip.style.top = `${Math.round(top)}px`;
      tip.style.left = `${Math.round(left)}px`;
    }

    function showMedalTip(btn) {
      clearTimeout(hideTimer);
      tip.className = 'tm-medal-tooltip';
      const label = btn.getAttribute('aria-label') || btn.getAttribute('title') || '';
      if (!label) return;
      tip.innerHTML = '<div class="tm-tip-label"></div>';
      tip.querySelector('.tm-tip-label').textContent = label;
      placeTip(btn);
    }

    function showSellerTip(link) {
      clearTimeout(hideTimer);
      const name = link.dataset.tmSellerHover;
      const s = SELLER_META[name];
      if (!s) return;
      tip.className = 'tm-medal-tooltip tm-tooltip-dev';
      const colorMap = {
        violet: ['#6551d4', '#a397fa'],
        pink: ['#c146b2', '#ef87d7'],
        gold: ['#ee9b23', '#ffd070'],
        green: ['#48a26d', '#83dca5'],
        blue: ['#4859d5', '#8495ff']
      };
      const medalsHtml = s.medals.map(m => {
        const [a, b] = colorMap[m.color] || ['#8590a2', '#bdc4d0'];
        return `<span class="tm-dev-tip-medal${m.earned ? '' : ' locked'}" style="--tmBadgeA:${a};--tmBadgeB:${b}"><img alt="" src="assets/icons/${m.icon}"/></span>`;
      }).join('');
      tip.innerHTML =
        `<div class="tm-dev-tip-head"><span class="tm-dev-tip-avatar">${s.letter}</span><div><strong>${name}</strong><small>${s.role}</small></div><span class="tm-dev-tip-check"><img alt="" src="assets/icons/shield-done.svg"/></span></div>` +
        `<div class="tm-dev-tip-stats"><div><strong>${s.sales}</strong><small>فروش موفق</small></div><div><strong>${s.products}</strong><small>محصول</small></div><div><strong>${s.rating}</strong><small>امتیاز</small></div></div>` +
        `<div class="tm-dev-tip-title">نشان‌های توسعه‌دهنده</div><div class="tm-dev-tip-medals">${medalsHtml}</div>` +
        `<div class="tm-dev-tip-footer">اطلاعات فروشنده نمایشی است؛ برای مشاهده پروفایل کلیک کنید.</div>`;
      placeTip(link);
    }

    function scheduleHide() {
      hideTimer = setTimeout(() => { tip.hidden = true; }, 100);
    }

    document.addEventListener('pointerenter', e => {
      const m = e.target.closest?.('[data-tm-medal]');
      if (m) { showMedalTip(m); return; }
      const s = e.target.closest?.('[data-tm-seller-hover]');
      if (s) showSellerTip(s);
    }, true);
    document.addEventListener('pointerleave', e => {
      if (e.target.closest?.('[data-tm-medal],[data-tm-seller-hover]')) scheduleHide();
    }, true);
    document.addEventListener('focusin', e => {
      const m = e.target.closest?.('[data-tm-medal]');
      if (m) { showMedalTip(m); return; }
      const s = e.target.closest?.('[data-tm-seller-hover]');
      if (s) showSellerTip(s);
    });
    document.addEventListener('focusout', e => {
      if (e.target.closest?.('[data-tm-medal],[data-tm-seller-hover]')) scheduleHide();
    });
  }
  initTooltips();

  /* -------------------- Vendor & Buyer Portal Interactivity -------------------- */
  function initPortals() {
    // 1. vendor-products.html & vendor-product-drafts.html filtering
    const vpList = $('#v11ProductList');
    if (vpList) {
      let curStatus = 'all';
      const searchIn = $('#v11ProductSearch');
      const filterVp = () => {
        const q = normFa(searchIn?.value || '');
        const st = normFa(decodeURIComponent(curStatus));
        $$('.v11-product-line', vpList).forEach(row => {
          const badge = normFa(row.querySelector('.v11-product-state')?.textContent || '');
          const text = normFa(row.textContent);
          const okSt = curStatus === 'all' || badge.includes(st);
          const okQ = !q || text.includes(q);
          row.hidden = !(okSt && okQ);
        });
      };
      document.addEventListener('click', e => {
        const stTab = e.target.closest('[data-status-filter]');
        if (stTab) {
          e.preventDefault();
          curStatus = stTab.dataset.statusFilter;
          $$('[data-status-filter]').forEach(a => a.classList.toggle('selected', a === stTab));
          filterVp();
          return;
        }
        const pub = e.target.closest('[data-draft-publish]');
        if (pub) {
          const badge = pub.closest('.v11-product-line')?.querySelector('.v9-badge');
          if (badge) { badge.className = 'v9-badge blue'; badge.textContent = 'در حال بررسی'; }
          toast('پیش‌نویس به صورت نمایشی برای بررسی ارسال شد');
          return;
        }
        const del = e.target.closest('[data-draft-delete]');
        if (del) {
          del.closest('.v11-product-line')?.remove();
          toast('پیش‌نویس از لیست نمایشی حذف شد');
        }
      });
      searchIn?.addEventListener('input', filterVp);
    }

    // 2. vendor-questions.html & vendor-reviews.html
    const qFilter = $('#v11QuestionFilter');
    const qList = $('#v11QuestionList');
    if (qFilter && qList) {
      qFilter.addEventListener('change', () => {
        const val = normFa(qFilter.value);
        $$('.v11-question', qList).forEach(q => {
          q.hidden = val ? !normFa(q.textContent).includes(val) : false;
        });
      });
    }

    // 3. vendor-ready-replies.html & vendor-reply-templates.html
    const replyList = $('#v11ReplyList');
    if (replyList) {
      function openReplyEditor(row, existingTitle = '', existingBody = '') {
        let box = $('.tm-reply-inline', row || replyList.parentNode);
        if (box) box.remove();
        box = document.createElement('form');
        box.className = 'tm-reply-inline';
        box.innerHTML =
          '<input placeholder="عنوان پاسخ آماده" required/>' +
          '<textarea placeholder="متن کامل پاسخ..." required></textarea>' +
          '<div class="tm-reply-inline-actions">' +
          '<button class="v9-action" type="submit">ذخیره پاسخ</button>' +
          '<button class="v11-btn muted" data-cancel-inline="" type="button">انصراف</button>' +
          '</div>';
        box.querySelector('input').value = existingTitle;
        box.querySelector('textarea').value = existingBody;
        if (row) row.after(box);
        else replyList.prepend(box);
        box.querySelector('input').focus();
        box.addEventListener('submit', ev => {
          ev.preventDefault();
          const t = box.querySelector('input').value.trim();
          const b = box.querySelector('textarea').value.trim();
          if (!t || !b) return;
          if (row) {
            row.querySelector('b').textContent = t;
            row.querySelector('p').textContent = b;
            toast('پاسخ آماده ویرایش شد');
          } else {
            const idx = $$('.v11-reply-row', replyList).length;
            const item = document.createElement('div');
            item.className = 'v11-reply-row';
            item.innerHTML = '<div><b></b><p></p></div><button class="v11-btn muted" type="button">ویرایش</button><button class="v11-btn danger" type="button">حذف</button>';
            item.querySelector('b').textContent = t;
            item.querySelector('p').textContent = b;
            item.querySelector('.v11-btn.muted').dataset.v11editreply = String(idx);
            item.querySelector('.v11-btn.danger').dataset.v11delreply = String(idx);
            replyList.append(item);
            toast('پاسخ آماده جدید اضافه شد');
          }
          box.remove();
        });
      }

      document.addEventListener('click', e => {
        if (e.target.closest('#v11AddReply')) { openReplyEditor(null); return; }
        const ed = e.target.closest('[data-v11editreply]');
        if (ed) {
          const row = ed.closest('.v11-reply-row');
          if (row) openReplyEditor(row, row.querySelector('b')?.textContent || '', row.querySelector('p')?.textContent || '');
          return;
        }
        const dl = e.target.closest('[data-v11delreply]');
        if (dl) {
          dl.closest('.v11-reply-row')?.remove();
          toast('پاسخ آماده حذف شد');
        }
      });
    }

    // 4. vendor-ticket-view.html
    const readySelect = $('#v11ReadySelect');
    const ticketReply = $('#v11TicketReply');
    const replyForm = $('#v11ReplyForm');
    if (readySelect && ticketReply) {
      const canned = {
        '0': 'سلام. لطفاً نسخه فعلی وردپرس و ووکامرس و تصویر خطا را بررسی بفرمایید؛ آخرین بروزرسانی محصول سازگاری کامل را اضافه کرده است.'
      };
      readySelect.addEventListener('change', () => {
        if (canned[readySelect.value]) {
          ticketReply.value = (ticketReply.value ? ticketReply.value + '\n' : '') + canned[readySelect.value];
          ticketReply.focus();
        }
      });
    }
    if (replyForm && ticketReply) {
      replyForm.addEventListener('submit', e => {
        e.preventDefault();
        const txt = ticketReply.value.trim();
        if (!txt) return;
        const bubble = document.createElement('div');
        bubble.className = 'v11-chat-bubble seller';
        bubble.innerHTML = '<small>پشتیبان فروشگاه — هم‌اکنون (نمایشی)</small><p></p>';
        bubble.querySelector('p').textContent = txt;
        replyForm.parentNode.insertBefore(bubble, replyForm);
        ticketReply.value = '';
        toast('پاسخ نمایشی در تیکت ثبت شد');
      });
    }

    // 5. Buyer tickets & Vendor tickets search/filter
    const buyerFilterBox = $('.v10-ticket-filters');
    if (buyerFilterBox) {
      const tList = $('.v10-ticket-list');
      const runBuyerTickets = () => {
        const q = normFa(buyerFilterBox.querySelector('input')?.value || '');
        const st = buyerFilterBox.querySelectorAll('select')[1]?.value || 'همه';
        $$('.v10-ticket-card', tList).forEach(card => {
          const text = normFa(card.textContent);
          const okQ = !q || text.includes(q);
          const okSt = st === 'همه' || text.includes(normFa(st));
          card.hidden = !(okQ && okSt);
        });
      };
      buyerFilterBox.querySelector('input')?.addEventListener('input', runBuyerTickets);
      buyerFilterBox.querySelectorAll('select').forEach(s => s.addEventListener('change', runBuyerTickets));
      buyerFilterBox.querySelector('button')?.addEventListener('click', runBuyerTickets);
    }

    const vTickets = $('.v11-tickets');
    if (vTickets) {
      const fHead = $('.v11-filter-head');
      const inp = fHead?.querySelector('input');
      const sel = fHead?.querySelector('select');
      const runVendorTickets = () => {
        const q = normFa(inp?.value || '');
        const st = normFa(sel?.value || '');
        $$('.v11-ticket-row', vTickets).forEach(row => {
          const text = normFa(row.textContent);
          const okQ = !q || text.includes(q);
          const okSt = !st || st.includes('همه') || text.includes(st);
          row.hidden = !(okQ && okSt);
        });
      };
      inp?.addEventListener('input', runVendorTickets);
      sel?.addEventListener('change', runVendorTickets);
    }

    // 6. Period tabs & CSV download in finance / support report
    const periodData = {
      week: { kpis: ['۱۴٬۶۸۵٬۰۰۰ تومان', '۵۵ عدد', '۱٬۳۲۱٬۶۵۰ تومان', '۱۲٬۵۰۰٬۰۰۰ تومان'], bars: [42, 65, 54, 88, 72, 95, 68] },
      month: { kpis: ['۵۸٬۴۲۰٬۰۰۰ تومان', '۲۱۴ عدد', '۵٬۱۸۰٬۰۰۰ تومان', '۴۹٬۷۵۰٬۰۰۰ تومان'], bars: [55, 76, 62, 91, 80, 70, 84] },
      year: { kpis: ['۶۱۲٬۹۰۰٬۰۰۰ تومان', '۲٬۱۸۰ عدد', '۴۸٬۳۰۰٬۰۰۰ تومان', '۵۲۰٬۴۰۰٬۰۰۰ تومان'], bars: [68, 74, 82, 90, 86, 94, 98] }
    };
    const supData = {
      week: { kpis: ['۴ ساعت و ۲۱ دقیقه', '۴.۸ از ۵', '۲۴ عدد', '۹۶٪'] },
      month: { kpis: ['۳ ساعت و ۵۰ دقیقه', '۴.۹ از ۵', '۹۲ عدد', '۹۷٪'] },
      year: { kpis: ['۴ ساعت و ۰۵ دقیقه', '۴.۸ از ۵', '۱٬۰۴۵ عدد', '۹۵٪'] }
    };
    document.addEventListener('click', e => {
      const pBtn = e.target.closest('[data-v11period]');
      if (pBtn) {
        const p = pBtn.dataset.v11period;
        $$('[data-v11period]').forEach(b => b.classList.toggle('active', b === pBtn));
        const isSup = pageName.includes('support-report');
        const set = isSup ? supData[p] : periodData[p];
        if (set?.kpis) {
          $$('.v11-kpis.report .v11-kpi strong').forEach((el, i) => {
            if (set.kpis[i]) el.textContent = set.kpis[i];
          });
        }
        if (set?.bars) {
          $$('.v11-chart .v11-bar').forEach((bar, i) => {
            if (set.bars[i]) bar.style.height = set.bars[i] + '%';
          });
        }
        toast('نمایش گزارش بر اساس بازه زمانی انتخاب‌شده به‌روز شد');
        return;
      }

      const csvBtn = e.target.closest('[data-v11csv]');
      if (csvBtn) {
        const table = $('.v9-table');
        if (!table) { toast('جدولی برای خروجی یافت نشد'); return; }
        const rows = $$('tr', table).map(tr =>
          $$('th,td', tr).map(cell => `"${cell.textContent.replace(/\s+/g, ' ').trim().replace(/"/g, '""')}"`).join(',')
        );
        const blob = new Blob(['\uFEFF' + rows.join('\n')], { type: 'text/csv;charset=utf-8;' });
        const a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = `${csvBtn.dataset.v11csv || 'report'}-demo.csv`;
        document.body.append(a);
        a.click();
        a.remove();
        toast('فایل CSV نمونه دانلود شد');
        return;
      }

      const ansBtn = e.target.closest('[data-v11answer]');
      if (ansBtn) {
        const card = ansBtn.closest('.v11-question');
        if (!card) return;
        let box = $('.tm-reply-inline', card);
        if (box) { box.remove(); return; }
        const curAns = card.querySelector('.v11-question-answer p')?.textContent || '';
        box = document.createElement('form');
        box.className = 'tm-reply-inline';
        box.innerHTML =
          '<textarea placeholder="پاسخ فروشنده را بنویسید..." required></textarea>' +
          '<div class="tm-reply-inline-actions">' +
          '<button class="v9-action" type="submit">ثبت پاسخ نمایشی</button>' +
          '<button class="v11-btn muted" data-cancel-inline="" type="button">بستن</button>' +
          '</div>';
        box.querySelector('textarea').value = curAns;
        card.append(box);
        box.querySelector('textarea').focus();
        box.addEventListener('submit', ev => {
          ev.preventDefault();
          const val = box.querySelector('textarea').value.trim();
          if (!val) return;
          let ansDiv = card.querySelector('.v11-question-answer');
          if (!ansDiv) {
            ansDiv = document.createElement('div');
            ansDiv.className = 'v11-question-answer';
            ansDiv.innerHTML = '<b>پاسخ فروشنده</b><p></p>';
            card.insertBefore(ansDiv, ansBtn);
          }
          ansDiv.querySelector('p').textContent = val;
          ansBtn.textContent = 'ویرایش پاسخ';
          box.remove();
          toast('پاسخ شما به صورت نمایشی ثبت شد');
        });
      }
    });

    // 7. Editor toolbar helper & Product / Store forms
    document.addEventListener('click', e => {
      const chip = e.target.closest('.v10-chip');
      if (chip) {
        chip.parentElement?.querySelectorAll('.v10-chip').forEach(c => c.classList.toggle('active', c === chip));
        toast(`نمایش بخش: ${chip.textContent.trim()}`);
        return;
      }
      const edBtn = e.target.closest('.v11-editor-bar span, .v10-editor-toolbar span');
      if (edBtn) {
        const ta = edBtn.closest('form')?.querySelector('textarea');
        if (ta) { ta.focus(); toast(`قالب‌بندی «${edBtn.textContent.trim()}» در دمو`); }
        return;
      }
      if (e.target.closest('[data-cancel-inline]')) {
        e.target.closest('.tm-reply-inline')?.remove();
      }
    });

    $('#v11ProductForm')?.addEventListener('submit', e => {
      e.preventDefault();
      toast('اطلاعات محصول بررسی شد. ثبت نهایی در نسخه بک‌اند فعال می‌شود.');
    });
    $('#v11StoreForm')?.addEventListener('submit', e => {
      e.preventDefault();
      toast('تنظیمات فروشگاه بررسی شد. ذخیره دائمی نیازمند بک‌اند است.');
    });
  }
  initPortals();

  /* -------------------- Seller Application Wizard & Status -------------------- */
  function initOnboarding() {
    const appForm = $('#sellerAppForm');
    if (appForm) {
      let step = 1;
      let otpOk = false;
      const errBox = $('#onboardErrors');

      function showErr(msg) {
        if (!errBox) return;
        if (!msg) { errBox.classList.remove('visible'); errBox.textContent = ''; return; }
        errBox.textContent = msg;
        errBox.classList.add('visible');
      }

      function populateReview() {
        const setTxt = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val || '—'; };
        setTxt('revName', $('#appFullName')?.value.trim());
        setTxt('revPhone', $('#appPhone')?.value.trim());
        setTxt('revNationalId', $('#appNationalId')?.value.trim());
        setTxt('revIban', $('#appIban')?.value.trim() ? 'IR' + $('#appIban').value.trim() : '—');
        setTxt('revStore', $('#appStoreName')?.value.trim());
        setTxt('revSlug', $('#appStoreSlug')?.value.trim());
        setTxt('revSpec', $('#appSpecialty')?.value);
        setTxt('revPort', $('#appPortfolio')?.value.trim());
      }

      function goStep(n) {
        step = Math.max(1, Math.min(5, n));
        showErr('');
        $$('[data-step-panel]', appForm).forEach(p => { p.hidden = Number(p.dataset.stepPanel) !== step; });
        $$('[data-step-ind]').forEach(s => {
          const i = Number(s.dataset.stepInd);
          s.classList.toggle('current', i === step);
          s.classList.toggle('completed', i < step);
        });
        const prevBtn = $('#onboardPrev');
        const nextBtn = $('#onboardNext');
        const subBtn = $('#onboardSubmit');
        if (prevBtn) prevBtn.hidden = step === 1;
        if (nextBtn) nextBtn.hidden = step === 5;
        if (subBtn) subBtn.hidden = step !== 5;
        if (step === 5) populateReview();
      }

      function validateStep(n) {
        if (n === 1 && !$('#appAgree')?.checked) return 'لطفاً ابتدا مطالعه و پذیرش قرارداد همکاری را تیک بزنید.';
        if (n === 2) {
          const ph = ($('#appPhone')?.value || '').trim();
          if (!/^09\d{9}$/.test(ph)) return 'شماره موبایل معتبر ۱۱ رقمی (شروع با 09) وارد کنید یا «پر کردن نمونه آزمایشی» را بزنید.';
          if (!otpOk) return 'لطفاً کد تأیید آزمایشی 123456 را وارد کرده و دکمه «تأیید شماره موبایل» را بزنید.';
        }
        if (n === 3) {
          if (!($('#appFullName')?.value || '').trim()) return 'نام و نام خانوادگی را وارد کنید.';
          if (!/^\d{10}$/.test(($('#appNationalId')?.value || '').trim())) return 'کد ملی ۱۰ رقمی آزمایشی را وارد کنید.';
          if (!/^\d{24}$/.test(($('#appIban')?.value || '').trim())) return 'شماره شبای ۲۴ رقمی آزمایشی (بدون IR) را وارد کنید.';
        }
        if (n === 4) {
          if (!($('#appStoreName')?.value || '').trim()) return 'نام فروشگاه را وارد کنید.';
          if (!($('#appStoreSlug')?.value || '').trim()) return 'نامک انگلیسی فروشگاه را وارد کنید.';
        }
        return '';
      }

      $('#sendOtpBtn')?.addEventListener('click', () => {
        const ph = ($('#appPhone')?.value || '').trim();
        if (!/^09\d{9}$/.test(ph)) { showErr('شماره موبایل معتبر ۱۱ رقمی وارد کنید.'); return; }
        showErr('');
        toast('کد آزمایشی 123456 آماده است (پیامک واقعی ارسال نمی‌شود)');
        $('#appOtp')?.focus();
      });

      $('#verifyOtpBtn')?.addEventListener('click', () => {
        if (($('#appOtp')?.value || '').trim() === '123456') {
          otpOk = true;
          showErr('');
          if ($('#otpVerifiedBadge')) $('#otpVerifiedBadge').hidden = false;
          toast('شماره موبایل در حالت دمو تأیید شد');
        } else {
          otpOk = false;
          showErr('کد واردشده نادرست است. کد آزمایشی 123456 را وارد کنید.');
        }
      });

      $('#appIdFile')?.addEventListener('change', e => {
        const f = e.target.files?.[0];
        if (f) {
          $('#idUploadBox')?.classList.add('uploaded');
          if ($('#idUploadLabel')) $('#idUploadLabel').textContent = f.name;
        }
      });

      document.addEventListener('click', e => {
        const fill = e.target.closest('[data-fill-step]');
        if (fill) {
          const fs = Number(fill.dataset.fillStep);
          if (fs === 2) {
            if ($('#appPhone')) $('#appPhone').value = '09120001234';
            if ($('#appOtp')) $('#appOtp').value = '123456';
            otpOk = true;
            if ($('#otpVerifiedBadge')) $('#otpVerifiedBadge').hidden = false;
          } else if (fs === 3) {
            if ($('#appFullName')) $('#appFullName').value = 'مهدی احمدی (نمونه)';
            if ($('#appNationalId')) $('#appNationalId').value = '0012345678';
            if ($('#appBirth')) $('#appBirth').value = '1374/05/12';
            if ($('#appIban')) $('#appIban').value = '120170000000123456789012';
          } else if (fs === 4) {
            if ($('#appStoreName')) $('#appStoreName').value = 'استودیو کدنویسی تمساح';
            if ($('#appStoreSlug')) $('#appStoreSlug').value = 'temsah-demo-studio';
            if ($('#appPortfolio')) $('#appPortfolio').value = 'https://github.com/example';
            if ($('#appBio')) $('#appBio').value = 'طراحی و توسعه قالب‌های وردپرس و ابزارهای فرانت‌اند استاندارد.';
          }
          showErr('');
          toast('نمونه آزمایشی درج شد (اطلاعات تستی هستند)');
          return;
        }
        const jump = e.target.closest('[data-jump-step]');
        if (jump) { goStep(Number(jump.dataset.jumpStep)); return; }
        if (e.target.closest('#onboardPrev')) { goStep(step - 1); return; }
        if (e.target.closest('#onboardNext')) {
          const err = validateStep(step);
          if (err) { showErr(err); return; }
          goStep(step + 1);
        }
      });

      appForm.addEventListener('submit', e => {
        e.preventDefault();
        toast('درخواست فروشندگی آزمایشی ثبت شد! انتقال به صفحه وضعیت...');
        setTimeout(() => { location.href = 'seller-application-status.html'; }, 700);
      });
    }

    // Status page switcher
    const statusPanel = $('#onboardStatusPanel');
    if (statusPanel) {
      const states = {
        pending: {
          cls: '', icon: 'assets/icons/time-circle.svg',
          title: 'درخواست شما در حال بررسی است',
          desc: 'کارشناسان تمساح در حال بررسی اطلاعات هویتی، شماره شبا و نمونه‌کار ارسالی شما هستند. نتیجه از طریق ایمیل و پنل کاربری اطلاع‌رسانی می‌شود.',
          label: 'در انتظار بررسی', reason: false, vendor: false
        },
        approved: {
          cls: 'approved', icon: 'assets/icons/tick-square.svg',
          title: 'درخواست فروشندگی شما تأیید شد!',
          desc: 'تبریک! فروشگاه نمونه شما فعال شد. اکنون می‌توانید وارد پنل فروشندگان شوید و اولین محصول خود را ثبت کنید.',
          label: 'تأییدشده', reason: false, vendor: true
        },
        rejected: {
          cls: 'rejected', icon: 'assets/icons/close-square.svg',
          title: 'درخواست نیاز به اصلاح مدارک دارد',
          desc: 'درخواست نمونه به دلیل نقص اطلاعات زیر قابل تأیید نبود. پس از اصلاح موارد می‌توانید مجدداً ارسال کنید.',
          label: 'نیازمند اصلاح', reason: true, vendor: false
        }
      };
      document.addEventListener('click', e => {
        const b = e.target.closest('[data-app-status]');
        if (!b) return;
        const st = states[b.dataset.appStatus];
        if (!st) return;
        $$('[data-app-status]').forEach(x => x.classList.toggle('active', x === b));
        const box = $('#statusIconBox');
        if (box) box.className = 'onboard-status-icon ' + st.cls;
        if ($('#statusIconImg')) $('#statusIconImg').src = st.icon;
        if ($('#statusHeading')) $('#statusHeading').textContent = st.title;
        if ($('#statusDesc')) $('#statusDesc').textContent = st.desc;
        if ($('#statusLabelText')) $('#statusLabelText').textContent = st.label;
        if ($('#statusRejectReason')) $('#statusRejectReason').hidden = !st.reason;
        if ($('#statusVendorBtn')) $('#statusVendorBtn').hidden = !st.vendor;
      });
    }
  }
  initOnboarding();

  /* -------------------- Global Delegated Events -------------------- */
  (!document.querySelector('script[src="home-sliders.js"]') ? $$('[data-slider]') : []).forEach(btn => {
    btn.addEventListener('click', () => {
      const view = document.getElementById(btn.dataset.slider);
      if (!view) return;
      const item = view.querySelector('.ts-story,.product-card');
      const width = (item?.getBoundingClientRect().width || 240) + 16;
      view.scrollBy({ left: (btn.dataset.slide === 'next' ? -1 : 1) * width, behavior: 'smooth' });
    });
  });

  let drag = null;
  document.addEventListener('pointerdown', e => {
    const g = e.target.closest('[data-tm-gallery]');
    if (g && e.button === 0 && !e.target.closest('button')) drag = { g, x: e.clientX, y: e.clientY, id: e.pointerId };
  });
  document.addEventListener('pointerup', e => {
    if (!drag || drag.id !== e.pointerId) return;
    const { g, x, y } = drag;
    drag = null;
    const dx = e.clientX - x, dy = e.clientY - y;
    if (Math.abs(dx) > 45 && Math.abs(dx) > Math.abs(dy) * 1.2) {
      gallery(g, Number(g.dataset.current || 0) + (dx > 0 ? 1 : -1));
    }
  });
  document.addEventListener('pointercancel', () => { drag = null; });

  document.addEventListener('change', e => {
    const svc = e.target.closest('[data-cartservice]');
    if (svc) {
      const pid = String(svc.dataset.cartservice);
      const services = readObj(SVC_KEY);
      const list = Array.isArray(services[pid]) ? services[pid] : [];
      if (svc.checked && !list.includes(svc.value)) list.push(svc.value);
      else if (!svc.checked) services[pid] = list.filter(x => x !== svc.value);
      if (svc.checked) services[pid] = list;
      saveJson(SVC_KEY, services);
      renderCart();
      return;
    }
    const gw = e.target.closest('input[name="t12-gateway"]');
    if (gw) {
      saveStr(GW_KEY, gw.value);
      $$('.t12-payment').forEach(lbl => lbl.classList.toggle('active', lbl.contains(gw)));
    }
  });

  document.addEventListener('click', e => {
    const t = e.target;

    const nav = t.closest('[data-tm-gallery-nav],[data-tm-gallery-dot]');
    if (nav) {
      const g = nav.closest('[data-tm-gallery]');
      if (g) {
        e.preventDefault();
        gallery(g, nav.hasAttribute('data-tm-gallery-dot') ? Number(nav.dataset.tmGalleryDot) : Number(g.dataset.current || 0) + (nav.dataset.tmGalleryNav === 'next' ? 1 : -1));
      }
      return;
    }

    const b = t.closest('[data-product-tab]');
    if (b) { tab(b.dataset.productTab); return; }

    const more = t.closest('[data-read-more]');
    if (more) {
      const body = $('#productDescriptionBody');
      if (body) {
        const open = body.classList.toggle('is-expanded');
        more.setAttribute('aria-expanded', String(open));
        const n = [...more.childNodes].find(x => x.nodeType === 3);
        if (n) n.textContent = open ? 'مشاهده کمتر ' : 'مشاهده بیشتر ';
      }
      return;
    }

    const feedback = t.closest('[data-feedback-open]');
    if (feedback) {
      const form = $('#' + (feedback.dataset.feedbackOpen === 'question' ? 'tmQuestionForm' : 'tmReviewForm'));
      if (form) { form.hidden = false; form.querySelector('input')?.focus(); }
      return;
    }
    if (t.closest('[data-feedback-close]')) { t.closest('form').hidden = true; return; }

    if (t.closest('#megaBtn')) { $('#megaPanel')?.classList.toggle('open'); return; }
    if (t.closest('#hamburger')) { $('#mainNav')?.classList.toggle('mobile-open'); return; }
    if (!t.closest('.nav-mega-parent')) $('#megaPanel')?.classList.remove('open');

    if (t.closest('#portalMenu')) { $('#portalSidebar')?.classList.toggle('mobile-open'); return; }
    if (!t.closest('#portalSidebar') && !t.closest('#portalMenu')) $('#portalSidebar')?.classList.remove('mobile-open');

    const group = t.closest('[data-portalgroup]');
    if (group) { group.closest('.portal-nav-group')?.classList.toggle('open'); return; }

    if (t.closest('[data-search-open]')) { e.preventDefault(); searchOpen(); return; }
    if (t.closest('[data-search-close]')) { searchClose(); return; }

    const add = t.closest('[data-add]');
    if (add) {
      const id = String(add.dataset.add);
      const ids = readList(CART_KEY, DEFAULT_CART);
      if (!ids.includes(id)) ids.push(id);
      saveJson(CART_KEY, ids);
      syncCount();
      toast('محصول به سبد اضافه شد');
      return;
    }

    const fav = t.closest('[data-fav]');
    if (fav) {
      const id = String(fav.dataset.fav);
      const old = readList(FAV_KEY, DEFAULT_FAVS);
      const next = old.includes(id) ? old.filter(x => x !== id) : [...old, id];
      saveJson(FAV_KEY, next);
      syncFavs();
      toast(next.includes(id) ? 'به علاقه‌مندی‌ها اضافه شد' : 'از علاقه‌مندی‌ها حذف شد');
      return;
    }

    if (t.closest('#restoreWishlistBtn')) {
      saveJson(FAV_KEY, DEFAULT_FAVS);
      syncFavs();
      toast('علاقه‌مندی‌های نمونه بازگردانی شد');
      return;
    }

    if (t.closest('#restoreSampleCart')) {
      saveJson(CART_KEY, DEFAULT_CART);
      saveJson(SVC_KEY, {});
      saveStr(COUPON_KEY, '');
      renderCart();
      syncCount();
      toast('سبد خرید نمونه بازگردانی شد');
      return;
    }

    const del = t.closest('[data-cartdelete],[data-remove]');
    if (del) {
      const id = String(del.dataset.cartdelete || del.dataset.remove);
      saveJson(CART_KEY, readList(CART_KEY, DEFAULT_CART).filter(x => x !== id));
      const services = readObj(SVC_KEY);
      delete services[id];
      saveJson(SVC_KEY, services);
      renderCart();
      syncCount();
      toast('محصول از سبد حذف شد');
      return;
    }

    const detail = t.closest('[data-cartmore]');
    if (detail) {
      const extra = detail.closest('[data-item]')?.querySelector('.t12-extras');
      if (extra) extra.hidden = !extra.hidden;
      return;
    }

    const cat = t.closest('[data-v11category]');
    if (cat) {
      $$('.v11-category-tile').forEach(x => x.classList.toggle('selected', x === cat));
      return;
    }

    if (t.closest('#v11StartProduct')) {
      const sel = $('.v11-category-tile.selected')?.dataset.v11category || 'قالب وردپرس';
      location.href = 'submit-product-form.html?cat=' + encodeURIComponent(sel);
      return;
    }

    const demo = t.closest('[data-demo]');
    if (demo) { toast(demo.dataset.demo); return; }
  });

  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
      searchClose();
      $('#megaPanel')?.classList.remove('open');
      $('#portalSidebar')?.classList.remove('mobile-open');
      $('#mainNav')?.classList.remove('mobile-open');
    }
    const g = e.target.closest('[data-tm-gallery]');
    if (g && ['ArrowLeft', 'ArrowRight'].includes(e.key)) {
      e.preventDefault();
      gallery(g, Number(g.dataset.current || 0) + (e.key === 'ArrowLeft' ? 1 : -1));
    }
  });

  document.addEventListener('submit', e => {
    const f = e.target;
    if (f.matches('.header-search,.hero-search') && $('#tmSearchOverlay')) {
      e.preventDefault();
      searchOpen();
      return;
    }
    if (f.id === 'tmStaticSearchForm') {
      e.preventDefault();
      location.href = 'catalog.html?q=' + encodeURIComponent($('#tmStaticSearchInput')?.value || '');
      return;
    }
    if (f.id === 't12-coupon-form') {
      e.preventDefault();
      const code = ($('#t12-coupon-input')?.value || '').trim().toUpperCase();
      if (!code) {
        saveStr(COUPON_KEY, '');
        renderCart();
        toast('کد تخفیف پاک شد');
      } else if (code === 'TEMSAH10') {
        saveStr(COUPON_KEY, 'TEMSAH10');
        renderCart();
        toast('کد تخفیف ۱۰ درصدی TEMSAH10 اعمال شد');
      } else {
        toast('کد تخفیف معتبر نیست (کد آزمایشی: TEMSAH10)');
      }
      return;
    }
    if (f.hasAttribute('data-demoform')) {
      e.preventDefault();
      toast(f.dataset.demoform || 'این فرم نمایشی است.');
    }
  });

  syncCount();
  syncFavs();
  renderCart();
  syncCheckout();
})();
