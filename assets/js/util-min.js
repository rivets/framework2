class FWAjaxRQ{constructor(e){this.request=e}onloaded(){if(this.status>=200&&this.status<400){let e=this.options.hasOwnProperty("accept")&&"application/json"==this.options.accept?JSON.parse(this.response):this.response;if(this.options.hasOwnProperty("success"))for(let t of this.options.success)e=t(e,this)}else if(this.options.hasOwnProperty("fail"))for(let e of this.options.fail)e(this);if(this.options.hasOwnProperty("always"))for(let e of this.options.always)e(this)}onfailed(){if(this.options.hasOwnProperty("fail"))for(let e of this.options.fail)e(this);if(this.options.hasOwnProperty("always"))for(let e of this.options.always)e(this)}done(e){return this.request.options.hasOwnProperty("success")||(this.request.options.success=[]),this.request.options.success.push(e),this}fail(e){return this.request.options.hasOwnProperty("fail")||(this.request.options.fail=[]),this.request.options.fail.push(e),this}always(e){return this.request.options.hasOwnProperty("always")||(this.request.options.always=[]),this.request.options.always.push(e),this}}var fwdom={on:function(e,t,o,n=null){(null!==n?n:document).querySelectorAll(e).forEach((function(e){e.addEventListener(t,o,!1)}))},data:function(e,t){const o="data-"+t;return e.closest("["+o+"]").getAttribute(o)},stop:function(e){e.preventDefault(),e.stopPropagation()},toggleClass:function(e,t){for(let o of e)for(let e of t)o.classList.toggle(e)},mkjQ:function(e){return jQuery(e)},nosubmit:function(e){return fwdom.stop(e),!1}},framework={base:"",putorpatch:"PUT",currentModal:null,makeQString:function(e){let t="",o="";for(var n in e)e.hasOwnProperty(n)&&(t+=o+n+"="+encodeURIComponent(e[n]),o="&");return t},ajax:function(e,t){let o=new XMLHttpRequest,n=t.hasOwnProperty("method")?t.method:"GET",a=t.hasOwnProperty("accept")?t.accept:"",r="",s="text/plain; charset=UTF-8";t.hasOwnProperty("data")&&(t.data instanceof FormData||"object"!=typeof t.data?(r=t.data,s=""):(r=framework.makeQString(t.data),"GET"==n.toUpperCase()?(s="text/plain",e+="?"+r,r=""):s="application/x-www-form-urlencoded; charset=UTF-8"));let i=t.hasOwnProperty("type")?t.type:s;o.options=t,o.open(n,e,!t.hasOwnProperty("async")||t.async),""!==i&&"multipart/form-data"!=i&&o.setRequestHeader("Content-Type",i),""!=a&&o.setRequestHeader("Accept",a);let l=new FWAjaxRQ(o);return o.onload=l.onloaded,o.onerror=l.onfailed,o.send(r),l},getJSON:function(e,t,o){var n=new XMLHttpRequest;let a=new FWAjaxRQ(n);return n.open("GET",e,!0),n.setRequestHeader("Accept","application/json"),n.onload=function(){this.status>=200&&this.status<400?t(JSON.parse(this.response)):o(this)},n.onerror=function(){o(this)},n.send(),a},mktoggle:function(e,t){return'<i class="'+e+" fa-solid fa-toggle-"+(t?"on":"off")+'"></i>'},tick:function(e){return framework.mktoggle("",e)},toggle:function(e){fwdom.toggleClass([e],["fa-toggle-off","fa-toggle-on"])},buildFWLink:function(){let e=framework.base;for(let t of arguments)e+="/"+t;return e+"/"},dotoggle:function(e,t,o,n){fwdom.stop(e);let a=t.classList;if(!a.contains("fadis"))if(a.contains("htick")){const e=t.nextElementSibling;e.value=1==e.value?0:1,framework.toggle(t)}else{let e=t.closest("[data-id]");e instanceof jQuery&&(e=e[0]),framework.ajax(framework.buildFWLink("ajax/toggle",o,e.getAttribute("data-id"),n),{method:framework.putorpatch}).done((function(){framework.toggle(t)})).fail((function(e){framework.alert("<h3>Toggle failed</h3>"+e.responseText)}))}},deletebean:function(e,t,o,n,a,r=""){fwdom.stop(e),""===r&&(r="this "+o),framework.confirm("Are you sure you you want to delete "+r+"?",(function(e){e&&framework.ajax(framework.buildFWLink("ajax/bean",o,n),{method:"DELETE"}).done(a).fail((function(e){framework.alert("<h3>Delete failed</h3>"+e.responseText)}))}))},editcall:function(e){return framework.ajax(framework.buildFWLink("ajax",e.op,e.bean,e.pk,e.name),{method:framework.putorpatch,data:{value:e.value}})},removeNode:function(e){var t=[e];if(e.hasAttribute("rowspan")){let o=parseInt(e.getAttribute("rowspan"))-1;for(;o>0;)t[o]=t[o-1].elementSibling}for(let e of t)e.parentNode.removeChild(e)},fadetodel:function(e,t=null){e.classList.add("fader"),e.style.opacity="0",setTimeout((function(){framework.removeNode(e),null!==t&&t()}),1500)},dodelbean:function(e,t,o,n="",a=null){let r=t.closest("[data-id]");"undefined"!=typeof jQuery&&r instanceof jQuery&&(r=r[0]),framework.deletebean(e,t,o,r.getAttribute("data-id"),(function(){framework.fadetodel(r,a)}),n)},containerClick:function(e){fwdom.stop(e);const t=e.target.classList;e.data.clicks.forEach((function(o){let[n,a,r]=o;t.contains(n)&&a(e,e.target,e.data.bean,r)}))},goFWLink:function(e,t,o,n="/"){let a=e.closest("[data-id]");a instanceof jQuery&&(a=a[0]),window.location.href=framework.buildFWLink(t,o,a.getAttribute("data-id"),n)},goedit:function(e,t,o){framework.goFWLink(t,"admin/edit",o)},goLink:function(e){window.location.href=e.target.getAttribute("href")},goview:function(e,t,o){framework.goFWLink(t,"admin/view",o)},beanCreate:function(e,t,o,n){framework.ajax(framework.buildFWLink("ajax/bean",e),{method:"POST",data:t}).done(o).fail((function(t){framework.alert("<h3>Failed to create new "+e+"</h3>"+t.responseText)})).always((function(){(n instanceof Object?n:document.getElementById(n)).disabled=!1}))},addMore:function(e){fwdom.stop(e);const t=document.getElementById("mrow"),o=t.previousElementSibling.cloneNode(!0);for(var n of o.getElementsByTagName("input"))"checkbox"==n.getAttribute("type")||"radio"==n.getAttribute("type")?n.checked=!1:n.value="";for(n of o.getElementsByTagName("textarea"))n.innerHTML="";for(n of o.getElementsByTagName("option"))n.selected=!1;for(n of o.getElementsByTagName("select"))n.children[0].selected=!0;t.parentNode.insertBefore(o,t)},easeInOut:function(e,t,o,n,a){return Math.ceil(e+Math.pow(1/o*n,a)*(t-e))},doBGFade:function(e,t,o,n,a,r,s){e.bgFadeInt&&window.clearInterval(e.bgFadeInt);let i=0;e.bgFadeInt=window.setInterval((function(){e.css("backgroundcolor","rgb("+framework.easeInOut(t[0],o[0],a,i,s)+","+framework.easeInOut(t[1],o[1],a,i,s)+","+framework.easeInOut(t[2],o[2],a,i,s)+")"),i+=1,i>a&&(e.css("backgroundcolor",n),window.clearInterval(e.bgFadeInt))}),r)},addElement:function(e,t,o,n,a=null){const r=document.createElement(t),s=undefined;Object.keys(o).forEach((function(e){r.setAttribute(e,o[e])})),r.innerHTML=n,null===a?e.appendChild(r):e.insertBefore(r,a)},alert:function(e,t=""){document.querySelector("body").insertAdjacentHTML("beforeend",'<div class="modal" id="_fwalert" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">'+t+'</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body"><p>'+e+'</p></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">OK</button></div></div></div></div>'),framework.currentModal=document.getElementById("_fwalert"),framework.currentModal.addEventListener("hide.bs.modal",(function(){framework.currentModal.remove(),framework.currentModal=null})),bootstrap.Modal.getOrCreateInstance(framework.currentModal).show()},confirm:function(e,t,o=""){document.querySelector("body").insertAdjacentHTML("beforeend",'<div class="modal" id="_fwconfirm" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">'+o+'</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body"><p>'+e+'</p></div><div class="modal-footer"><button type="button" id="_fwno" class="btn btn-secondary">No</button><button type="button" id="_fwyes" class="btn btn-primary">Yes</button></div></div></div></div>'),framework.currentModal=document.getElementById("_fwconfirm"),framework.currentModal.addEventListener("hide.bs.modal",(function(e){framework.currentModal.remove(),framework.currentModal=null})),document.getElementById("_fwyes").addEventListener("click",(function(e){e.preventDefault(),bootstrap.Modal.getOrCreateInstance(framework.currentModal).hide(),t(!0)})),document.getElementById("_fwno").addEventListener("click",(function(e){e.preventDefault(),bootstrap.Modal.getOrCreateInstance(framework.currentModal).hide(),t(!1)})),bootstrap.Modal.getOrCreateInstance(framework.currentModal).show()}};framework.tableClick=framework.containerClick;
//# sourceMappingURL=util-min.js.map