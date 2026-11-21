/* TEMSAH V30: interaction logic only. All product/content records live in HTML. */
(()=>{'use strict';
const $=(q,root=document)=>root.querySelector(q), $$=(q,root=document)=>[...root.querySelectorAll(q)];
const key='temsah-v30-cart',favKey='temsah-v30-favs';
const read=k=>{try{const a=JSON.parse(localStorage.getItem(k)||'[]');return Array.isArray(a)?a:[]}catch{return[]}};
const save=(k,a)=>{try{localStorage.setItem(k,JSON.stringify(a))}catch{}};
const money=n=>n.toLocaleString('fa-IR')+' تومان';
const toast=t=>{const el=$('#toast');if(!el)return;el.textContent=t;el.classList.add('visible');clearTimeout(window.tmToast);window.tmToast=setTimeout(()=>el.classList.remove('visible'),2500)};
function gallery(g,n){const slides=$$('.tm-gallery-slide',g);if(!slides.length)return;n=(n+slides.length)%slides.length;g.dataset.current=n;const track=$('.tm-gallery-track',g);if(track)track.style.transform=`translateX(${n*100}%)`;$$('[data-tm-gallery-dot]',g).forEach((x,i)=>{if(i===n)x.setAttribute('aria-current','true');else x.removeAttribute('aria-current')})}
$$('[data-tm-gallery]').forEach(g=>gallery(g,0));
function tab(id,changeUrl=true){const target=$$('[data-product-tab]').find(x=>x.dataset.productTab===id);if(!target)return;$$('[data-product-tab]').forEach(b=>{const on=b===target;b.classList.toggle('is-active',on);b.setAttribute('aria-selected',String(on));b.tabIndex=on?0:-1});$$('.tm-tab-panel').forEach(p=>p.hidden=p.id!==id);if(changeUrl&&history.replaceState)history.replaceState(null,'','#'+encodeURIComponent(id))}
if($('[data-product-tab]'))tab(decodeURIComponent(location.hash.substring(1))||'description',false);
function syncCount(){$$('.cart-count').forEach(x=>x.textContent=read(key).length.toLocaleString('fa-IR'))}
function renderCart(){const list=$('.t12-cart-list');if(!list)return;list.replaceChildren();let total=0;const ids=read(key);for(const id of ids){const tpl=$('#tmCartRow-'+id);if(!tpl)continue;list.append(tpl.content.cloneNode(true));total+=Number(tpl.dataset.price||0)}if(!list.children.length){const empty=$('#tmEmptyCart');if(empty)list.append(empty.content.cloneNode(true))}const totalNode=$('.t12-total strong');if(totalNode)totalNode.textContent=money(total);const first=$('.t12-price-box > div:first-child b');if(first)first.textContent=money(total);const count=$('.t12-price-box > div:first-child span');if(count)count.textContent=`قیمت محصولات (${ids.length.toLocaleString('fa-IR')})`;const discount=$('.t12-price-box > div:nth-child(2) b');if(discount)discount.textContent='۰ تومان'}
function searchOpen(){const overlay=$('#tmSearchOverlay');if(!overlay)return;overlay.hidden=false;overlay.classList.add('open');$('#tmStaticSearchInput')?.focus()}
function searchClose(){const overlay=$('#tmSearchOverlay');if(overlay){overlay.hidden=true;overlay.classList.remove('open')}}
function searchFilter(text){const q=text.trim().toLocaleLowerCase('fa');$$('.tm-search-result').forEach(x=>x.hidden=!x.textContent.toLocaleLowerCase('fa').includes(q))}
function catalogFilter(text){const q=text.trim().toLocaleLowerCase('fa');$$('.product-card').forEach(x=>x.hidden=!x.textContent.toLocaleLowerCase('fa').includes(q))}
if(location.pathname.endsWith('catalog.html')){const q=new URLSearchParams(location.search).get('q');if(q)catalogFilter(q)}
$('#tmStaticSearchInput')?.addEventListener('input',e=>searchFilter(e.target.value));
$$('.header-search input,.hero-search input').forEach(x=>x.addEventListener('focus',()=>{if($('#tmSearchOverlay')){x.blur();searchOpen()}}));
(!document.querySelector('script[src="home-sliders.js"]')?$$('[data-slider]'):[]).forEach(btn=>btn.addEventListener('click',()=>{const view=document.getElementById(btn.dataset.slider);if(!view)return;const item=view.querySelector('.ts-story,.product-card');const width=(item?.getBoundingClientRect().width||240)+16;view.scrollBy({left:(btn.dataset.slide==='next'?-1:1)*width,behavior:'smooth'})}));
let drag=null;document.addEventListener('pointerdown',e=>{const g=e.target.closest('[data-tm-gallery]');if(g&&e.button===0&&!e.target.closest('button'))drag={g,x:e.clientX,y:e.clientY,id:e.pointerId}});
document.addEventListener('pointerup',e=>{if(!drag||drag.id!==e.pointerId)return;const {g,x,y}=drag;drag=null;const dx=e.clientX-x,dy=e.clientY-y;if(Math.abs(dx)>45&&Math.abs(dx)>Math.abs(dy)*1.2)gallery(g,Number(g.dataset.current||0)+(dx>0?1:-1))});
document.addEventListener('pointercancel',()=>drag=null);
document.addEventListener('click',e=>{
 const t=e.target;
 const nav=t.closest('[data-tm-gallery-nav],[data-tm-gallery-dot]');if(nav){const g=nav.closest('[data-tm-gallery]');if(g){e.preventDefault();gallery(g,nav.hasAttribute('data-tm-gallery-dot')?Number(nav.dataset.tmGalleryDot):Number(g.dataset.current||0)+(nav.dataset.tmGalleryNav==='next'?1:-1))}return}
 const b=t.closest('[data-product-tab]');if(b){tab(b.dataset.productTab);return}
 const more=t.closest('[data-read-more]');if(more){const body=$('#productDescriptionBody');if(body){const open=body.classList.toggle('is-expanded');more.setAttribute('aria-expanded',String(open));const n=[...more.childNodes].find(x=>x.nodeType===3);if(n)n.textContent=open?'مشاهده کمتر ':'مشاهده بیشتر '}return}
 const feedback=t.closest('[data-feedback-open]');if(feedback){const form=$('#'+(feedback.dataset.feedbackOpen==='question'?'tmQuestionForm':'tmReviewForm'));if(form){form.hidden=false;form.querySelector('input')?.focus()}return}
 if(t.closest('[data-feedback-close]')){t.closest('form').hidden=true;return}
 if(t.closest('#megaBtn')){$('#megaPanel')?.classList.toggle('open');return}
 if(t.closest('#hamburger')){$('#mainNav')?.classList.toggle('mobile-open');return}
 if(!t.closest('.nav-mega-parent'))$('#megaPanel')?.classList.remove('open');
 if(t.closest('#portalMenu')){$('#portalSidebar')?.classList.toggle('mobile-open');return}
 const group=t.closest('[data-portalgroup]');if(group){group.closest('.portal-nav-group')?.classList.toggle('open');return}
 if(t.closest('[data-search-open]')){e.preventDefault();searchOpen();return}
 if(t.closest('[data-search-close]')){searchClose();return}
 const add=t.closest('[data-add]');if(add){const id=String(add.dataset.add);const ids=read(key);if(!ids.includes(id))ids.push(id);save(key,ids);syncCount();toast('محصول به سبد اضافه شد');return}
 const fav=t.closest('[data-fav]');if(fav){const id=String(fav.dataset.fav),old=read(favKey),next=old.includes(id)?old.filter(x=>x!==id):[...old,id];save(favKey,next);fav.classList.toggle('is-fav',next.includes(id));toast('علاقه‌مندی‌ها در این مرورگر ذخیره شد');return}
 const del=t.closest('[data-cartdelete],[data-remove]');if(del){const id=String(del.dataset.cartdelete||del.dataset.remove);save(key,read(key).filter(x=>x!==id));renderCart();syncCount();toast('محصول از سبد حذف شد');return}
 const detail=t.closest('[data-cartmore]');if(detail){const extra=detail.closest('[data-item]')?.querySelector('.t12-extras');if(extra)extra.hidden=!extra.hidden;return}
 const cat=t.closest('[data-v11category]');if(cat){$$('.v11-category-tile').forEach(x=>x.classList.toggle('selected',x===cat));return}
 if(t.closest('#v11StartProduct')){location.href='submit-product-form.html';return}
 const demo=t.closest('[data-demo]');if(demo){toast(demo.dataset.demo);return}
});
document.addEventListener('keydown',e=>{if(e.key==='Escape'){searchClose();$('#megaPanel')?.classList.remove('open')}const g=e.target.closest('[data-tm-gallery]');if(g&&['ArrowLeft','ArrowRight'].includes(e.key)){e.preventDefault();gallery(g,Number(g.dataset.current||0)+(e.key==='ArrowLeft'?1:-1))}});
document.addEventListener('submit',e=>{const f=e.target;if(f.matches('.header-search,.hero-search')&&$('#tmSearchOverlay')){e.preventDefault();searchOpen();return}if(f.id==='tmStaticSearchForm'){e.preventDefault();location.href='catalog.html?q='+encodeURIComponent($('#tmStaticSearchInput')?.value||'');return}if(['tmQuestionForm','tmReviewForm'].includes(f.id)){e.preventDefault();toast('ثبت آنلاین به سرور نیاز دارد؛ این نسخه فقط HTML است.');return}if(f.hasAttribute('data-demoform')){e.preventDefault();toast(f.dataset.demoform||'این فرم نمایشی است.')}});
syncCount();renderCart();
})();
