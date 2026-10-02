(() => {
 const init=()=>{
  const section=document.querySelector('[data-atlas-map]');if(!section||section.dataset.ready)return;section.dataset.ready='1';
  const words=JSON.parse(section.dataset.words),mode=section.dataset.mode,canvas=section.querySelector('[data-map-canvas]'),panel=section.querySelector('#atlas-map-panel'),status=section.querySelector('[data-map-status]'),selected=section.querySelector('[data-map-selected]');let map,pin;
  const lat=document.querySelector('[name=latitude]'),lng=document.querySelector('[name=longitude]');
  const valid=()=>lat&&lng&&lat.value!==''&&lng.value!==''&&Number.isFinite(+lat.value)&&Number.isFinite(+lng.value)&&Math.abs(+lat.value)<=90&&Math.abs(+lng.value)<=180;
  const pick=point=>{const y=Math.max(-90,Math.min(90,point.lat)),x=((point.lng+180)%360+360)%360-180;
   if(mode==='pick'){lat.value=y.toFixed(7);lng.value=x.toFixed(7);if(pin)pin.setLatLng([y,x]);else{pin=L.marker([y,x],{draggable:true,title:words['map.selected']}).addTo(map);pin.on('dragend',()=>pick(pin.getLatLng()));}}
   else{const link=document.createElement('a'),url=new URL(section.dataset.create);url.searchParams.set('lat',y.toFixed(7));url.searchParams.set('lng',x.toFixed(7));link.href=url;link.textContent=words['map.add-here'];L.popup().setLatLng([y,x]).setContent(link).openOn(map);}
   selected.textContent=`${words['map.selected']}: ${y.toFixed(7)}, ${x.toFixed(7)}`;
  };
  const start=()=>{if(map){map.invalidateSize();return;}if(!window.L){status.textContent=words['map.failed'];return;}
   map=L.map(canvas,{scrollWheelZoom:false,zoomControl:false,zoomAnimation:false,fadeAnimation:false,markerZoomAnimation:false,inertia:false}).setView(valid()?[+lat.value,+lng.value]:[25.04,102.71],12);
   L.control.zoom({zoomInTitle:words['map.zoom-in'],zoomOutTitle:words['map.zoom-out']}).addTo(map);
   map.on('popupopen',event=>event.popup.getElement().querySelector('.leaflet-popup-close-button')?.setAttribute('aria-label',words['map.close']));
   if(section.dataset.tiles)L.tileLayer(section.dataset.tiles,{attribution:section.dataset.attribution,maxZoom:19}).on('tileerror',()=>{status.textContent=words['map.failed'];}).addTo(map);else status.textContent=words['map.failed'];
   const bounds=[];for(const item of JSON.parse(section.dataset.markers)){const symbols={shrine:'◆',altar:'■',incense:'▲',niche:'●',other:'✚'},link=document.createElement('a');link.href=item.url;link.textContent=item.title;L.marker([item.lat,item.lng],{title:item.title,alt:item.title,icon:L.icon({iconUrl:item.icon,iconSize:[32,32],iconAnchor:[16,16]})}).addTo(map).bindPopup(link);bounds.push([item.lat,item.lng]);}
   if(bounds.length)map.fitBounds(bounds,{padding:[30,30],maxZoom:14});map.on('click',event=>pick(event.latlng));if(valid())pick({lat:+lat.value,lng:+lng.value});
  };
  section.querySelector('[data-map-toggle]')?.addEventListener('click',event=>{panel.hidden=!panel.hidden;event.currentTarget.setAttribute('aria-expanded',String(!panel.hidden));if(!panel.hidden)start();});
  section.querySelector('[data-map-center]').onclick=()=>{start();if(map)pick(map.getCenter());};
  section.querySelector('[data-map-locate]').onclick=()=>{if(!navigator.geolocation){status.textContent=words['map.location-error'];return;}navigator.geolocation.getCurrentPosition(position=>{start();if(map){pick({lat:position.coords.latitude,lng:position.coords.longitude});map.setView([position.coords.latitude,position.coords.longitude],16);}},()=>{status.textContent=words['map.location-error'];},{timeout:10000,maximumAge:0,enableHighAccuracy:true});};
  section.querySelector('[data-map-clear]')?.addEventListener('click',()=>{lat.value='';lng.value='';selected.textContent='';if(pin){map.removeLayer(pin);pin=null;}});
  const sync=()=>{if(valid()){start();pick({lat:+lat.value,lng:+lng.value});map.panTo([+lat.value,+lng.value],{animate:false});}else if(pin){map.removeLayer(pin);pin=null;}};
  if(mode==='pick'){lat.addEventListener('change',sync);lng.addEventListener('change',sync);start();}
  window.destroyAtlasMap=()=>{if(map){map.stop();map.remove();}};
 };window.addEventListener('atlas-page-replaced',init);init();
})();
