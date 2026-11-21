/* TEMSAH slider controller. Supports RTL scrollLeft differences in Firefox/WebKit/Chromium. */
(()=>{'use strict';
  const $all=(s,root=document)=>Array.from(root.querySelectorAll(s));
  function scrollStep(view){const first=view.querySelector('.ts-slider-track>*');if(!first)return view.clientWidth*.8;const track=view.querySelector('.ts-slider-track');const gap=parseFloat(getComputedStyle(track).columnGap)||0;return first.getBoundingClientRect().width+gap}
  function maxScroll(view){return Math.max(0,view.scrollWidth-view.clientWidth)}
  function rtlMode(){
    const outer=document.createElement('div'),inner=document.createElement('div');
    outer.dir='rtl';outer.style.cssText='width:4px;height:1px;overflow:scroll;position:absolute;top:-9999px;visibility:hidden';
    inner.style.cssText='width:9px;height:1px';outer.appendChild(inner);document.body.appendChild(outer);
    let mode;
    if(outer.scrollLeft>0)mode='default';
    else{outer.scrollLeft=1;mode=outer.scrollLeft===0?'negative':'reverse';}
    outer.remove();return mode;
  }
  let mode='negative';
  function progress(view){
    if(getComputedStyle(view).direction!=='rtl')return view.scrollLeft;
    if(mode==='negative')return -view.scrollLeft;
    if(mode==='reverse')return view.scrollLeft;
    return maxScroll(view)-view.scrollLeft;
  }
  function setProgress(view,n,behavior='auto'){
    const target=Math.max(0,Math.min(maxScroll(view),n));
    let left=target;
    if(getComputedStyle(view).direction==='rtl')left=mode==='negative'?-target:mode==='reverse'?target:maxScroll(view)-target;
    view.scrollTo({left,behavior});
  }
  function pages(view){const max=maxScroll(view);return Math.max(1,Math.ceil(max/scrollStep(view))+1)}
  const behavior=()=>matchMedia('(prefers-reduced-motion: reduce)').matches?'auto':'smooth';
  function update(id){
    const view=document.getElementById(id);if(!view)return;
    const current=progress(view),max=maxScroll(view),step=scrollStep(view);
    const prev=document.querySelector(`[data-slider="${id}"][data-slide="prev"]`);
    const next=document.querySelector(`[data-slider="${id}"][data-slide="next"]`);
    if(prev)prev.disabled=current<3;
    if(next)next.disabled=current>=max-3;
    const dots=document.querySelector(`[data-dots-for="${id}"]`);
    if(dots){const count=pages(view);if(dots.children.length!==count){
      dots.innerHTML=Array.from({length:count},(_,i)=>`<button type="button" data-slide-to="${i}" data-slider="${id}" aria-label="نمایش اسلاید ${i+1}" aria-current="false"></button>`).join('');
    }const active=Math.min(count-1,Math.round(current/step));$all('button',dots).forEach((b,i)=>b.setAttribute('aria-current',String(i===active)));}
  }
  function move(view,direction){const step=scrollStep(view);setProgress(view,progress(view)+(direction==='next'?step:-step),behavior());}
  function enableMouseDrag(view){
    let drag=null,blockClickUntil=0;
    view.addEventListener('pointerdown',e=>{
      if(e.pointerType!=='mouse'||e.button!==0||e.target.closest('button,input,textarea,select'))return;
      drag={id:e.pointerId,x:e.clientX,y:e.clientY,scroll:progress(view),moved:false};
    });
    window.addEventListener('pointermove',e=>{
      if(!drag||e.pointerId!==drag.id)return;
      const dx=e.clientX-drag.x,dy=e.clientY-drag.y;
      if(!drag.moved){
        if(Math.abs(dx)<7)return;
        if(Math.abs(dx)<Math.abs(dy)*1.15){drag=null;return;}
        drag.moved=true;
        view.classList.add('ts-is-dragging');
        view.style.scrollSnapType='none';view.style.scrollBehavior='auto';
        try{view.setPointerCapture(e.pointerId)}catch(_){}
      }
      // Content follows pointer: moving mouse LEFT increases logical scroll progress.
      setProgress(view,drag.scroll-dx);
      e.preventDefault();
    },{passive:false});
    function end(e){
      if(!drag||e.pointerId!==drag.id)return;
      const wasDragging=drag.moved;drag=null;
      if(!wasDragging)return;
      blockClickUntil=performance.now()+450;
      try{if(view.hasPointerCapture(e.pointerId))view.releasePointerCapture(e.pointerId)}catch(_){}
      view.classList.remove('ts-is-dragging');
      const step=scrollStep(view);const destination=Math.round(progress(view)/step)*step;
      view.style.scrollSnapType='';view.style.scrollBehavior='';
      setProgress(view,destination,behavior());update(view.id);
    }
    window.addEventListener('pointerup',end);window.addEventListener('pointercancel',end);
    view.addEventListener('click',e=>{
      if(performance.now()<blockClickUntil){e.preventDefault();e.stopImmediatePropagation();blockClickUntil=0;}
    },true);
    view.addEventListener('dragstart',e=>e.preventDefault());
  }
  function init(){
    mode=rtlMode();
    const views=$all('.ts-slider-viewport');if(!views.length)return;
    views.forEach(view=>{
      update(view.id);enableMouseDrag(view);let scheduled=false;
      view.addEventListener('scroll',()=>{if(!scheduled){scheduled=true;requestAnimationFrame(()=>{scheduled=false;update(view.id)})}},{passive:true});
      view.addEventListener('keydown',e=>{if(e.target!==view)return;if(e.key==='ArrowLeft'||e.key==='ArrowRight'){e.preventDefault();move(view,e.key==='ArrowLeft'?'next':'prev')}});
    });
    document.addEventListener('click',e=>{
      const btn=e.target.closest('[data-slider][data-slide], [data-slider][data-slide-to]');if(!btn)return;
      const view=document.getElementById(btn.dataset.slider);if(!view)return;e.preventDefault();
      if(btn.dataset.slideTo!==undefined)setProgress(view,scrollStep(view)*Number(btn.dataset.slideTo),behavior());
      else move(view,btn.dataset.slide);
    });
    window.addEventListener('resize',()=>views.forEach(view=>update(view.id)),{passive:true});
  }
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init,{once:true});else init();
})();
