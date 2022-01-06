class FWAjaxRQ{constructor(e){this.request=e}onloaded(){this.status>=200&&this.status<400?this.options.hasOwnProperty("success")&&this.options.success(this.options.hasOwnProperty("accept")&&"application/json"==this.options.accept?JSON.parse(this.response):this.response,this):this.options.hasOwnProperty("fail")&&this.options.fail(this),this.options.hasOwnProperty("always")&&this.options.always(this)}onfailed(){this.options.hasOwnProperty("fail")&&this.options.fail(this),this.options.hasOwnProperty("always")&&this.options.always(this)}done(e){return this.request.options.success=e,this}fail(e){return this.request.options.fail=e,this}always(e){return this.request.options.always=e,this}}var framework={base:"",putorpatch:"PUT",currentModal:null,makeQString:function(e){let t="",o="";for(var a in e)e.hasOwnProperty(a)&&(t+=o+a+"="+encodeURIComponent(e[a]),o="&");return t},ajax:function(e,t){let o=new XMLHttpRequest,a=t.hasOwnProperty("method")?t.method:"GET",n=t.hasOwnProperty("accept")?t.accept:"",r="",s="text/plain; charset=UTF-8";t.hasOwnProperty("data")&&(t.data instanceof FormData||"object"!=typeof t.data?(r=t.data,s=""):(r=framework.makeQString(t.data),s="application/x-www-form-urlencoded; charset=UTF-8"));let i=t.hasOwnProperty("type")?t.type:s;o.options=t,o.open(a,e,!t.hasOwnProperty("async")||t.async),""!==i&&"multipart/form-data"!=i&&o.setRequestHeader("Content-Type",i),""!=n&&o.setRequestHeader("Accept",n);let d=new FWAjaxRQ(o);return o.onload=d.onloaded,o.onerror=d.onfailed,o.send(r),d},getJSON:function(e,t,o){var a=new XMLHttpRequest;let n=new FWAjaxRQ(a);return a.open("GET",e,!0),a.setRequestHeader("Accept","application/json"),a.onload=function(){this.status>=200&&this.status<400?t(JSON.parse(this.response)):o(this)},a.onerror=function(){o(this)},a.send(),n},mktoggle:function(e,t){return'<i class="'+e+" fas fa-toggle-"+(t?"on":"off")+'"></i>'},tick:function(e){return framework.mktoggle("",e)},toggle:function(e){fwdom.toggleClass([e],["fa-toggle-off","fa-toggle-on"])},buildFWLink:function(){let e=framework.base;for(let t of arguments)e+="/"+t;return e+"/"},dotoggle:function(e,t,o,a){fwdom.stop(e);let n=t.classList;if(!n.contains("fadis"))if(n.contains("htick")){const e=t.nextElementSibling;e.value=1==e.value?0:1,framework.toggle(t)}else{let e=t.closest("[data-id]");e instanceof jQuery&&(e=e[0]),framework.ajax(framework.buildFWLink("ajax/toggle",o,e.getAttribute("data-id"),a),{method:framework.putorpatch}).done((function(){framework.toggle(t)})).fail((function(e){framework.alert("<h3>Toggle failed</h3>"+e.responseText)}))}},deletebean:function(e,t,o,a,n,r=""){fwdom.stop(e),""===r&&(r="this "+o),fwdom.confirm("Are you sure you you want to delete "+r+"?",(function(e){e&&framework.ajax(framework.buildFWLink("ajax/bean",o,a),{method:"DELETE"}).done(n).fail((function(e){framework.alert("<h3>Delete failed</h3>"+e.responseText)}))}))},editcall:function(e){return framework.ajax(framework.buildFWLink("ajax",e.op,e.bean,e.pk,e.name),{method:framework.putorpatch,data:{value:e.value}})},removeNode:function(e){var t=[e];if(e.hasAttribute("rowspan")){let o=parseInt(e.getAttribute("rowspan"))-1;for(;o>0;)t[o]=t[o-1].elementSibling}for(let e of t)e.parentNode.removeChild(e)},fadetodel:function(e,t=null){e.classList.add("fader"),e.style.opacity="0",setTimeout((function(){framework.removeNode(e),null!==t&&t()}),1500)},dodelbean:function(e,t,o,a="",n=null){let r=t.closest("[data-id]");"undefined"!=typeof jQuery&&r instanceof jQuery&&(r=r[0]),framework.deletebean(e,t,o,r.getAttribute("data-id"),(function(){framework.fadetodel(r,n)}),a)},containerClick:function(e){fwdom.stop(e);const t=e.target.classList;e.data.clicks.forEach((function(o){let[a,n,r]=o;t.contains(a)&&n(e,e.target,e.data.bean,r)}))},goFWLink:function(e,t,o,a="/"){let n=e.closest("[data-id]");n instanceof jQuery&&(n=n[0]),window.location.href=framework.buildFWLink(t,o,n.getAttribute("data-id"),a)},goedit:function(e,t,o){framework.goFWLink(t,"admin/edit",o)},goLink:function(e){window.location.href=e.target.getAttribute("href")},goview:function(e,t,o){framework.goFWLink(t,"admin/view",o)},beanCreate:function(e,t,o,a){framework.ajax(framework.buildFWLink("ajax/bean",e),{method:"POST",data:t}).done(o).fail((function(t){framework.alert("<h3>Failed to create new "+e+"</h3>"+t.responseText)})).always((function(){(a instanceof Object?a:document.getElementById(a)).disabled=!1}))},addMore:function(e){fwdom.stop(e);const t=document.getElementById("mrow"),o=t.previousElementSibling.cloneNode(!0);for(var a of o.getElementsByTagName("input"))"checkbox"==a.getAttribute("type")||"radio"==a.getAttribute("type")?a.checked=!1:a.value="";for(a of o.getElementsByTagName("textarea"))a.innerHTML="";for(a of o.getElementsByTagName("option"))a.selected=!1;for(a of o.getElementsByTagName("select"))a.children[0].selected=!0;t.parentNode.insertBefore(o,t)},easeInOut:function(e,t,o,a,n){return Math.ceil(e+Math.pow(1/o*a,n)*(t-e))},doBGFade:function(e,t,o,a,n,r,s){e.bgFadeInt&&window.clearInterval(e.bgFadeInt);let i=0;e.bgFadeInt=window.setInterval((function(){e.css("backgroundcolor","rgb("+framework.easeInOut(t[0],o[0],n,i,s)+","+framework.easeInOut(t[1],o[1],n,i,s)+","+framework.easeInOut(t[2],o[2],n,i,s)+")"),i+=1,i>n&&(e.css("backgroundcolor",a),window.clearInterval(e.bgFadeInt))}),r)},addElement:function(e,t,o,a,n=null){const r=document.createElement(t),s=undefined;Object.keys(o).forEach((function(e){r.setAttribute(e,o[e])})),r.innerHTML=a,null===n?e.appendChild(r):e.insertBefore(r,n)},alert:function(e,t=""){document.querySelector("body").insertAdjacentHTML("beforeend",'<div class="modal" id="_fwalert" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">'+t+'</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body"><p>'+e+'</p></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">OK</button></div></div></div></div>'),framework.currentModal=document.getElementById("_fwalert"),framework.currentModal.addEventListener("hide.bs.modal",(function(){framework.currentModal.remove(),framework.currentModal=null})),bootstrap.Modal.getOrCreateInstance(framework.currentModal).show()},confirm:function(e,t,o=""){document.querySelector("body").insertAdjacentHTML("beforeend",'<div class="modal" id="_fwconfirm" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">'+o+'</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body"><p>'+e+'</p></div><div class="modal-footer"><button type="button" id="_fwno" class="btn btn-secondary">No</button><button type="button" id="_fwyes" class="btn btn-primary">Yes</button></div></div></div></div>'),framework.currentModal=document.getElementById("_fwconfirm"),framework.currentModal.addEventListener("hide.bs.modal",(function(e){framework.currentModal.remove(),framework.currentModal=null})),document.getElementById("_fwyes").addEventListener("click",(function(e){e.preventDefault(),bootstrap.Modal.getOrCreateInstance(framework.currentModal).hide(),t(!0)})),document.getElementById("_fwno").addEventListener("click",(function(e){e.preventDefault(),bootstrap.Modal.getOrCreateInstance(framework.currentModal).hide(),t(!1)})),bootstrap.Modal.getOrCreateInstance(framework.currentModal).show()}};framework.tableClick=framework.containerClick;
//# sourceMappingURL=util-min.js.map