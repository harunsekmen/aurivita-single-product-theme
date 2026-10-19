(() => {
  const config=window.AurivitaConfig, dialog=document.getElementById('av-cart-dialog');
  if (!config || !dialog) return;
  let nonce=config.nonce, restNonce=config.restNonce, cart=null, adding=false, queue=Promise.resolve();
  let generation=0, recoveryEpoch=0, readFlight=null, recoveryFlight=null, recoveryRequired=false, pendingMutations=0;
  let requestSequence=0, nonceSequence=0, recoveryButton=null;
  let returnFocus=null, removedItem=null, focusRestore=null, focusIntent=null;
  const giftDialog=document.getElementById('av-gift-dialog');
  const openDialog=target=>window.AurivitaDialog?window.AurivitaDialog.open(target):target?.showModal();
  const closeDialog=target=>window.AurivitaDialog?window.AurivitaDialog.close(target):Promise.resolve((target?.close(),true));
  const pendingKeys=new Set();
  const emit=(state, extra={})=>document.dispatchEvent(new CustomEvent('av:cart-state',{detail:{state,...extra}}));
  const money=(value,unit=2,currency='USD')=>new Intl.NumberFormat(config.locale || 'tr-TR',{style:'currency',currency:currency, currencyDisplay:'code',minimumFractionDigits:unit,maximumFractionDigits:unit}).format(Number(value)/10**unit);
  const escape=value=>String(value).replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  const messageText=value=>String(value).replace(/&(quot|amp|lt|gt|apos|#(?:x[0-9a-f]+|\d+));/gi,(match,entity)=>{
    const named={quot:'"',amp:'&',lt:'<',gt:'>',apos:"'"},name=entity.toLowerCase();
    if(named[name])return named[name];
    const point=name.startsWith('#x')?parseInt(name.slice(2),16):Number(name.slice(1));
    return point>0 && point<=0x10ffff && !(point>=0xd800 && point<=0xdfff)?String.fromCodePoint(point):match;
  });
  async function request(route, body) {
    let response; const sequence=++requestSequence;
    try { response=await fetch(config.storeApi+route,{method:body?'POST':'GET',credentials:'same-origin',cache:'no-store',headers:{'Content-Type':'application/json','Nonce':nonce,'X-WP-Nonce':restNonce},...(body?{body:JSON.stringify(body)}:{})}); }
    catch(cause) { throw Object.assign(new Error(cause?.message || 'Could not connect to your cart.'),{uncertain:!!body}); }
    if(sequence>=nonceSequence) {
      nonceSequence=sequence;
      nonce=config.nonce=response.headers.get('Nonce') || nonce;
      restNonce=config.restNonce=response.headers.get('X-WP-Nonce') || restNonce;
    }
    let result;
    try { result=await response.json(); } catch { const error=new Error('No cart response was received. Please check your cart.'); error.uncertain=!!body; throw error; }
    if(!response.ok) throw Object.assign(new Error(messageText(result?.message || 'Your cart could not be updated.')),{uncertain:response.status>=500,cart:validCart(result?.data?.cart)?result.data.cart:null});
    if(!validCart(result)) throw Object.assign(new Error('The cart response could not be verified. Please check your cart.'),{uncertain:!!body});
    return result;
  }
  function validCart(value) {
    const totalsValid=(totals,field)=>totals && typeof totals.currency_code==='string' && /^[a-z]{3}$/i.test(totals.currency_code) && Number.isInteger(totals.currency_minor_unit) && totals.currency_minor_unit>=0 && totals.currency_minor_unit<=20 && ['string','number'].includes(typeof totals[field]) && String(totals[field]).trim()!=='' && Number.isFinite(Number(totals[field]));
    return !!value && Array.isArray(value.items) && Number.isFinite(value.items_count) && totalsValid(value.totals,'total_items') && value.items.every(item=>item && typeof item.key==='string' && Number.isFinite(item.quantity) && totalsValid(item.totals,'line_total') && Number.isFinite(item.quantity_limits?.minimum) && Number.isFinite(item.quantity_limits?.maximum) && Array.isArray(item.variation) && item.variation.every(variation=>variation && typeof variation==='object'));
  }
  function syncControls() {
    const items=new Map((cart?.items || []).map(item=>[item.key,item]));
    for(const line of dialog.querySelectorAll('[data-key]')) {
      const item=items.get(line.dataset.key), busy=recoveryRequired || !!recoveryFlight || pendingKeys.has(line.dataset.key);
      line.setAttribute('aria-busy',String(busy));
      for(const button of line.querySelectorAll('button')) {
        const quantity=Number(button.dataset.quantity);
        button.disabled=busy || !item || (button.hasAttribute('data-quantity') && !button.hasAttribute('data-remove') && (quantity<item.quantity_limits.minimum || quantity>item.quantity_limits.maximum));
      }
    }
    const globalBusy=recoveryRequired || !!recoveryFlight;
    for(const link of dialog.querySelectorAll('.cart-checkout-cta,.cart-full-link')) link.setAttribute('aria-disabled',String(globalBusy || pendingMutations>0));
    for(const control of dialog.querySelectorAll('[data-av-coupon-submit],[data-av-coupon-remove],[data-av-cart-undo],[data-av-cart-gift-open]')) control.disabled=globalBusy || pendingKeys.has(control.hasAttribute('data-av-cart-undo')?'undo':control.hasAttribute('data-av-cart-gift-open')?'gift':'coupon');
    if(giftDialog) for(const button of giftDialog.querySelectorAll('[data-av-gift-select]')) button.disabled=globalBusy || pendingKeys.has('gift') || button.dataset.unavailable==='true';
    if(recoveryButton) recoveryButton.disabled=!!recoveryFlight;
    if(focusRestore && !focusRestore.disabled) {
      if(document.activeElement===document.body)focusRestore.focus?.();
      focusRestore=null;focusIntent=null;
    }
  }
  function setStatus(message, undo=false) {
    const status=dialog.querySelector('[data-av-cart-status]');if(!status)return;
    status.hidden=!message;status.textContent=message;
    if(undo) {const button=document.createElement('button');button.type='button';button.setAttribute('data-av-cart-undo','');button.textContent='Undo';status.append(document.createTextNode(' '),button);}
  }
  function renderRewards(value) {
    const rewards=value.extensions?.aurivita || {}, gift=rewards.gift || {}, shipping=rewards.shipping || {};
    const format=amount=>money(amount,value.totals.currency_minor_unit,value.totals.currency_code);
    const target=dialog.querySelector('[data-av-cart-rewards]');
    if(target) {
      const milestones=[shipping.active?{...shipping,label:'Free shipping',kind:'shipping'}:null,gift.active?{...gift,label:gift.selected_key?'Your gift was added':'Choose your gift',kind:'gift'}:null].filter(Boolean);
      target.hidden=!milestones.length || !value.items.length;
      const next=milestones.filter(item=>!item.eligible).sort((a,b)=>Number(a.remaining)-Number(b.remaining))[0];
      const copy=next?`${next.kind==='shipping'?'toward free shipping':'toward choosing a gift'} ${format(next.remaining)} remaining.`:gift.active && gift.eligible?gift.selected_key?'Your gift is in your cart.':'You can now choose a gift.':shipping.address_known && shipping.available?'Free shipping is available for this address.':'You have reached the free-shipping threshold; eligible-region conditions apply.';
      const progress=milestones.reduce((sum,item)=>sum+Math.min(1,Number(item.amount)/Math.max(1,Number(item.threshold))),0)/milestones.length*100;
      const icon=kind=>kind==='gift'?'<svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 10h16v10H4zM3 6h18v4H3zM12 6v14M12 6H8a2 2 0 1 1 2-2l2 2Zm0 0h4a2 2 0 1 0-2-2l-2 2Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/></svg>':'<svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M3 5h11v12H3zM14 9h4l3 4v4h-7M5 17a2 2 0 1 0 4 0M16 17a2 2 0 1 0 4 0" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/></svg>';
      target.innerHTML=`<p class="cart-reward-message">${escape(copy)}</p><div class="cart-reward-track" role="progressbar" aria-label="Progress toward cart rewards" aria-valuemin="0" aria-valuemax="100" aria-valuenow="${Math.round(progress)}">${milestones.map(item=>`<span class="cart-reward-segment"><span style="width:${Math.min(100,Number(item.amount)/Math.max(1,Number(item.threshold))*100)}%"></span></span>`).join('')}</div><div class="cart-reward-milestones">${milestones.map(item=>`<div class="cart-reward-step ${item.eligible?'is-earned':''}"><strong>${format(item.threshold)}</strong><span class="cart-reward-icon" aria-hidden="true">${icon(item.kind)}</span>${item.kind==='gift' && item.eligible?`<button type="button" data-av-cart-gift-open aria-controls="av-gift-dialog" aria-haspopup="dialog">${escape(item.label)}</button>`:`<span>${escape(item.label)}</span>`}</div>`).join('')}</div>${shipping.active && !shipping.address_known?'<p class="cart-reward-condition">Free shipping applies in eligible shipping regions.</p>':''}`;
    }
    if(giftDialog) {
      const options=giftDialog.querySelector('[data-av-gift-options]');
      if(options) options.innerHTML=gift.eligible?(gift.options || []).map(item=>`<article class="cart-gift-option ${item.selected?'is-selected':''}">${item.image?`<img src="${escape(item.image)}" alt="${escape(item.name)}" width="80" height="80">`:''}<div><h3>${escape(item.name)}</h3>${Number(item.reference_price)>0?`<p><del>${format(item.reference_price)}</del> <strong>${format(0)}</strong></p>`:''}</div><button type="button" data-av-gift-select="${Number(item.id)}" data-unavailable="${!item.available}" ${!item.available?'disabled':''}>${item.selected?'Selected':item.available?'Choose gift':'Out of stock'}</button></article>`).join(''):'<p>Reach the offer threshold to choose a gift.</p>';
      if(giftDialog.open && !gift.eligible) closeDialog(giftDialog);
    }
    const recommendations=dialog.querySelector('[data-av-cart-recommendations]');
    if(recommendations) {
      recommendations.hidden=!rewards.recommendations?.length || !value.items.length;
      const list=recommendations.querySelector('[data-av-cart-recommendation-list]');
      if(list) list.innerHTML=(rewards.recommendations || []).map(item=>`<article class="cart-recommendation">${item.image?`<img src="${escape(item.image)}" alt="${escape(item.name)}" width="80" height="80" loading="lazy">`:''}<h3><a href="${escape(item.permalink)}">${escape(item.name)}</a></h3><p>${format(item.price)}</p>${item.type==='simple'?`<button type="button" data-av-cart-recommend="${Number(item.id)}">Add to cart</button>`:`<a href="${escape(item.permalink)}">View options</a>`}</article>`).join('');
    }
    if(gift.notice && !['Choose your gift.','Your gift was added.'].includes(gift.notice)) setStatus(gift.notice);
  }
  function render(value) {
    const focused=document.activeElement,held=focused===document.body?focusIntent:null,focusedKey=focused?.closest?.('[data-key]')?.dataset.key || held?.key;
    const focusAction=held?.action || (focused?.hasAttribute?.('data-remove')?'remove':focused?.hasAttribute?.('data-quantity') && Number(focused.dataset.quantity)>Number(cart?.items.find(item=>item.key===focusedKey)?.quantity)?'increase':'decrease');
    cart=value;
    document.querySelectorAll('[data-av-cart-count]').forEach(el=>{el.textContent=String(value.items_count);el.hidden=!value.items_count;});
    const titleCount=dialog.querySelector('[data-av-cart-title-count]');if(titleCount)titleCount.textContent=`(${value.items_count})`;
    const items=dialog.querySelector('[data-av-cart-items]');
    items.innerHTML=value.items.length?value.items.map(item=>{
      const image=item.images?.[0], price=item.totals, unit=price.currency_minor_unit, gift=item.extensions?.aurivita?.gift;
      const attributes=item.variation.map(v=>escape(v.value)).join(' · ');
      const priceText=`${gift && Number(item.extensions.aurivita.reference_price)>0?`<del>${money(item.extensions.aurivita.reference_price,unit,price.currency_code)}</del>`:''}<strong>${money(Number(price.line_total)+(config.cartTaxIncluded?Number(price.line_total_tax || 0):0),unit,price.currency_code)}</strong>`;
      return `<div class="cart-line" data-key="${escape(item.key)}">${image?`<img src="${escape(image.thumbnail || image.src)}" alt="${escape(image.alt || item.name)}" width="80" height="80">`:'<span class="cart-line-placeholder" aria-hidden="true"></span>'}<div class="cart-line-info"><h3><a href="${escape(item.permalink)}">${escape(item.name)}</a></h3><p>${attributes}</p>${gift?'<span class="cart-gift-label">Gift · 1 item</span>':''}</div><div class="cart-line-price">${priceText}</div><div class="cart-line-actions">${gift?'<button type="button" class="cart-gift-change" data-av-cart-gift-open>Change</button>':`<div class="quantity-control"><button type="button" data-quantity="${item.quantity-1}" ${item.quantity===1?'data-remove':''} aria-label="${item.quantity===1?'Remove item':'Decrease quantity'}" ${item.quantity>1 && item.quantity<=item.quantity_limits.minimum?'disabled':''}>${item.quantity===1?'×':'−'}</button><span>${item.quantity}</span><button type="button" data-quantity="${item.quantity+1}" aria-label="Increase quantity" ${item.quantity>=item.quantity_limits.maximum?'disabled':''}>+</button></div>`}<button type="button" class="cart-line-remove" data-remove aria-label="${escape(item.name)} remove item">Remove</button></div></div>`;
    }).join(''):'<div class="cart-empty"><h3>Your cart is empty</h3><p>Explore our products and start shopping.</p><button class="local-primary" data-av-cart-close>Continue shopping</button></div>';
    dialog.querySelector('[data-av-cart-summary]').hidden=!value.items.length;
    dialog.querySelector('[data-av-cart-total]').textContent=money(value.totals.total_price ?? value.totals.total_items,value.totals.currency_minor_unit,value.totals.currency_code);
    const discount=dialog.querySelector('[data-av-cart-discount]'),discountValue=dialog.querySelector('[data-av-cart-discount-value]');
    const giftSavings=value.items.reduce((sum,item)=>sum+(item.extensions?.aurivita?.gift?Number(item.extensions.aurivita.reference_price):0),0),saving=Number(value.totals.total_discount || 0)+(config.cartTaxIncluded?Number(value.totals.total_discount_tax || 0):0)+giftSavings;
    if(discount)discount.hidden=!saving;if(discountValue)discountValue.textContent='− '+money(saving,value.totals.currency_minor_unit,value.totals.currency_code);
    const coupons=dialog.querySelector('[data-av-cart-coupons]');if(coupons){coupons.hidden=!value.coupons?.length;coupons.innerHTML=(value.coupons || []).map(coupon=>`<span class="cart-coupon-tag">${escape(coupon.code)} <button type="button" data-av-coupon-remove="${escape(coupon.code)}" aria-label="${escape(coupon.code)} remove coupon">×</button></span>`).join('');}
    const couponSection=dialog.querySelector('.cart-coupon');if(couponSection)couponSection.hidden=!value.items.length;
    const address=value.shipping_address || {},addressKnown=address.country && (address.address_1 || address.city || address.postcode);
    const note=dialog.querySelector('[data-av-cart-shipping-note]');if(note)note.textContent=value.needs_shipping && value.has_calculated_shipping && addressKnown?'The total includes calculated shipping and taxes.':'Shipping and taxes are calculated at checkout.';
    renderRewards(value);
    syncControls();
    if(focusedKey && dialog.open) {
      const line=[...dialog.querySelectorAll('[data-key]')].find(row=>row.dataset.key===focusedKey);
      const item=value.items.find(item=>item.key===focusedKey);
      const control=line && [...line.querySelectorAll('button')].find(button=>focusAction==='remove'?button.hasAttribute('data-remove'):button.hasAttribute('data-quantity') && (focusAction==='increase'?Number(button.dataset.quantity)>item.quantity:Number(button.dataset.quantity)<item.quantity));
      const restored=control || dialog.querySelector('[data-av-cart-close]');
      if(restored?.disabled)focusRestore=restored;else {restored?.focus?.();focusIntent=null;}
    }
  }
  const open=()=>{if(!dialog.open)returnFocus=document.activeElement;openDialog(dialog);document.body.classList.add('av-cart-open','av-modal-open');};
  const close=()=>giftDialog?.open?closeDialog(giftDialog).then(closed=>closed?closeDialog(dialog):false):closeDialog(dialog);
  dialog.addEventListener('close',()=>{if(dialog.open)return;document.body.classList.remove('av-cart-open');if(!document.querySelector('dialog[open]'))document.body.classList.remove('av-modal-open');returnFocus?.focus?.({preventScroll:true});});
  function report(error) {
    const p=dialog.querySelector('[data-av-cart-error]');p.hidden=false;
    p.textContent=error.uncertain?'The action could not be verified. Check your cart before trying again.':error.message;
    recoveryButton=document.createElement('button');recoveryButton.type='button';
    recoveryButton.setAttribute('data-av-cart-recover','');recoveryButton.textContent='Check cart again';
    p.append(document.createTextNode(' '),recoveryButton);syncControls();
  }
  const blockedError=()=>Object.assign(new Error('Check your cart again before continuing.'),{uncertain:true});
  function readCart() {
    const version=generation;
    if(readFlight?.generation===version) return readFlight.promise;
    const flight={generation:version};
    flight.promise=request('cart').then(value=>{
      if(version===generation) render(value);
      return {value,generation:version};
    }).finally(()=>{if(readFlight===flight) readFlight=null;});
    readFlight=flight;return flight.promise;
  }
  async function refresh() {
    for(;;) {
      const version=generation, barrier=queue;
      await barrier;
      if(version!==generation || barrier!==queue) continue;
      let snapshot;
      try { snapshot=await readCart(); }
      catch(error) { if(version!==generation) continue; throw error; }
      if(snapshot.generation===generation) return snapshot.value;
    }
  }
  function recover() {
    if(recoveryFlight) return recoveryFlight;
    recoveryFlight=refresh().then(value=>{
      recoveryRequired=false;dialog.querySelector('[data-av-cart-error]').hidden=true;
      emit('idle',{cart:value});return value;
    }).catch(error=>{
      recoveryRequired=true;error.uncertain=true;report(error);emit('uncertain',{error:error.message});throw error;
    }).finally(()=>{recoveryFlight=null;syncControls();});
    syncControls();return recoveryFlight;
  }
  function mutate(route, payload, key='') {
    const epoch=recoveryEpoch;
    const focused=document.activeElement;
    if(key && focused?.closest?.('[data-key]')?.dataset.key===key) {
      const quantity=cart?.items.find(item=>item.key===key)?.quantity;
      focusIntent={key,action:focused.hasAttribute('data-remove')?'remove':Number(focused.dataset.quantity)>quantity?'increase':'decrease'};
    }
    generation++;
    pendingMutations++;
    if(key) pendingKeys.add(key);
    syncControls();
    const run=async()=>{
      let sent=false;
      try {
        if(epoch!==recoveryEpoch || recoveryRequired) throw Object.assign(new Error('Your cart has changed. Check it before continuing.'),{uncertain:recoveryRequired});
        const body=typeof payload==='function'?payload():payload;
        sent=true;
        const value=await request(route,body);render(value);
        if(route==='cart/add-item') {dialog.querySelector('[data-av-cart-error]').hidden=true;emit('added',{cart:value});open();}
        return value;
      } catch(error) {
        if(sent) {
          // A failed operation invalidates already accepted intents and earlier reads.
          recoveryEpoch++;generation++;recoveryRequired=!!error.uncertain;
          if(error.cart) render(error.cart);
        }
        report(error);
        if(route==='cart/add-item' || error.uncertain) emit(error.uncertain?'uncertain':'error',{error:error.message});
        if(sent) {
          try { await readCart(); }
          catch {
            recoveryRequired=true;error.uncertain=true;report(error);emit('uncertain',{error:error.message});
          }
        }
        throw error;
      } finally {
        if(key) pendingKeys.delete(key);
        pendingMutations--;
        syncControls();
      }
    };
    const operation=queue.then(run);
    queue=operation.then(()=>{},()=>{});
    return operation;
  }
  window.AurivitaCart={open,close,refresh,recover,async add(id,quantity=1,attributes={}){
    if(adding) throw Object.assign(new Error('Your cart is being updated.'),{uncertain:false});
    if(recoveryRequired || recoveryFlight) throw blockedError();
    adding=true;emit('pending');dialog.querySelector('[data-av-cart-error]').hidden=true;
    try { return await mutate('cart/add-item',{id:Number(id),quantity:Number(quantity),variation:Object.entries(attributes).map(([attribute,value])=>({attribute,value:String(value)}))}); }
    finally {adding=false;}
  }};
  document.addEventListener('click',event=>{
    if(event.target.closest('[data-av-cart-open]')) {open();refresh().catch(report);}
    if(event.target.closest('[data-av-cart-close]')) close();
  });
  function openGifts(trigger) {
    if(!giftDialog || !cart?.extensions?.aurivita?.gift?.eligible)return;
    giftDialog.returnFocus=trigger;openDialog(giftDialog);
  }
  if(giftDialog) {
    giftDialog.addEventListener('close',()=>{
      if(giftDialog.open || !dialog.open || dialog.hasAttribute('data-closing'))return;
      const trigger=giftDialog.returnFocus?.isConnected?giftDialog.returnFocus:dialog.querySelector('[data-av-cart-gift-open]');
      trigger?.focus?.();
    });
    giftDialog.addEventListener('click',event=>{
      if(event.target.closest('[data-av-gift-close]')) {closeDialog(giftDialog);return;}
      const button=event.target.closest('[data-av-gift-select]');
      if(!button || button.disabled || pendingKeys.has('gift') || recoveryRequired || recoveryFlight)return;
      const error=giftDialog.querySelector('[data-av-gift-error]');if(error)error.hidden=true;
      mutate('cart/extensions',{namespace:'aurivita',data:{action:'select_gift',id:Number(button.dataset.avGiftSelect)}},'gift').then(()=>{closeDialog(giftDialog);setStatus('Your gift was added to your cart.');}).catch(failure=>{if(error){error.hidden=false;error.textContent=failure.message;}});
    });
  }
  const couponForm=dialog.querySelector('[data-av-coupon-form]');
  if(couponForm)couponForm.addEventListener('submit',event=>{
    event.preventDefault();if(pendingKeys.has('coupon') || recoveryRequired || recoveryFlight)return;
    const input=couponForm.querySelector('[name="coupon_code"]'),code=input?.value.trim();if(!code)return;
    const feedback=dialog.querySelector('[data-av-coupon-error]');if(feedback)feedback.hidden=true;
    mutate('cart/apply-coupon',{code},'coupon').then(value=>{input.value='';dialog.querySelector('[data-av-cart-error]').hidden=true;if(feedback){feedback.hidden=false;feedback.textContent='Promo code applied.';}const notice=value.extensions?.aurivita?.gift?.notice;setStatus(notice && !['Choose your gift.','Your gift was added.'].includes(notice)?notice:'');}).catch(error=>{if(feedback){feedback.hidden=false;feedback.textContent=error.message;feedback.scrollIntoView?.({block:'nearest'});}});
  });
  dialog.addEventListener('click',event=>{
    if(event.target.closest('.cart-checkout-cta,.cart-full-link') && (pendingMutations || recoveryRequired || recoveryFlight)) {
      event.preventDefault();
      if(recoveryRequired || recoveryFlight)report(blockedError());else setStatus('Your cart is updating. Continue when it is ready.');
      return;
    }
    if(event.target.closest('[data-av-cart-recover]')) {recover().catch(()=>{});return;}
    const giftOpen=event.target.closest('[data-av-cart-gift-open]');if(giftOpen){openGifts(giftOpen);return;}
    const couponRemove=event.target.closest('[data-av-coupon-remove]');if(couponRemove){if(!couponRemove.disabled && !pendingKeys.has('coupon'))mutate('cart/remove-coupon',{code:couponRemove.dataset.avCouponRemove},'coupon').then(()=>setStatus('Promo code removed.')).catch(()=>{});return;}
    const undo=event.target.closest('[data-av-cart-undo]');if(undo){
      if(!removedItem || undo.disabled || pendingKeys.has('undo'))return;
      const item=removedItem;removedItem=null;
      const gift=item.extensions?.aurivita?.gift;
      mutate(gift?'cart/extensions':'cart/add-item',gift?{namespace:'aurivita',data:{action:'select_gift',id:item.id}}:{id:item.id,quantity:item.quantity,variation:item.variation.map(v=>({attribute:v.attribute,value:v.value}))},'undo').then(()=>setStatus('Item restored to your cart.')).catch(()=>{});return;
    }
    const recommendation=event.target.closest('[data-av-cart-recommend]');if(recommendation){window.AurivitaCart.add(Number(recommendation.dataset.avCartRecommend)).catch(()=>{});return;}
    const control=event.target.closest('[data-quantity],[data-remove]');if(!control || control.disabled)return;
    const key=control.closest('[data-key]')?.dataset.key;
    if(!key || pendingKeys.has(key) || recoveryRequired || recoveryFlight) return;
    const item=cart?.items.find(item=>item.key===key), remove=control.hasAttribute('data-remove');
    if(!item) return;
    const delta=Number(control.dataset.quantity)-item.quantity;
    if(!remove && ![-1,1].includes(delta)) return;
    mutate(remove?'cart/remove-item':'cart/update-item',()=>{
      const current=cart?.items.find(item=>item.key===key);
      if(!current) throw Object.assign(new Error('This item is no longer in your cart.'),{uncertain:false});
      return {key,...(remove?{}:{quantity:current.quantity+delta})};
    },key).then(()=>{if(remove){removedItem=item;setStatus('Item removed from your cart.',true);syncControls();}}).catch(()=>{});
  });
  refresh().catch(report);
})();
