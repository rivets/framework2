class FWAjaxRQ{constructor(e){this.request=e}onloaded(){this.status>=200&&this.status<400?this.options.hasOwnProperty("success")&&this.options.success(this.response,this):this.options.hasOwnProperty("fail")&&this.options.fail(this),this.options.hasOwnProperty("always")&&this.options.always(this)}onfailed(){this.options.hasOwnProperty("fail")&&this.options.fail(this),this.options.hasOwnProperty("always")&&this.options.always(this)}done(e){return this.request.options.success=e,this}fail(e){return this.request.options.fail=e,this}always(e){return this.request.options.always=e,this}}var framework={base:"",putorpatch:"PUT",currentModal:null,makeQString:function(e){let t="",o="";for(var n in e)e.hasOwnProperty(n)&&(t+=o+encodeURI(n+"="+e[n]),o="&");return t},ajax:function(e,t){let o=new XMLHttpRequest,n=t.hasOwnProperty("method")?t.method:"GET",a=t.hasOwnProperty("data")?"object"==typeof t.data?framework.makeQString(t.data):t.data:"",r=t.hasOwnProperty("type")?t.type:""!==a?"application/x-www-form-urlencoded; charset=UTF-8":"text/plain; charset=UTF-8",i=new FWAjaxRQ(o);return o.options=t,o.open(n,e,!t.hasOwnProperty("async")||t.async),o.setRequestHeader("Content-Type",r),o.onload=i.onloaded,o.onerror=i.onfailed,o.send(a),i},getJSON:function(e,t,o){var n=new XMLHttpRequest;let a=new FWAjaxRQ(n);return n.open("GET",e,!0),n.setRequestHeader("Accept","application/json"),n.onload=function(){this.status>=200&&this.status<400?t(JSON.parse(this.response)):o(this)},n.onerror=function(){o(this)},n.send(),a},mktoggle:function(e,t){return'<i class="'+e+" fas fa-toggle-"+(t?"on":"off")+'"></i>'},tick:function(e){return framework.mktoggle("",e)},toggle:function(e){fwdom.toggleClass([e],["fa-toggle-off","fa-toggle-on"])},buildFWLink:function(){let e=framework.base;for(let t of arguments)e+="/"+t;return e+"/"},dotoggle:function(e,t,o,n){fwdom.stop(e);let a=t.classList;if(!a.contains("fadis"))if(a.contains("htick")){const e=t.nextElementSibling;e.value=1==e.value?0:1,framework.toggle(t)}else{let e=t.closest("[data-id]");e instanceof jQuery&&(e=e[0]),framework.ajax(framework.buildFWLink("ajax/toggle",o,e.getAttribute("data-id"),n),{method:putorpatch}).done((function(){framework.toggle(t)})).fail((function(e){fwdom.alert("<h3>Toggle failed</h3>"+e.responseText)}))}},deletebean:function(e,t,o,n,a,r=""){fwdom.stop(e),""===r&&(r="this "+o),fwdom.confirm("Are you sure you you want to delete "+r+"?",(function(e){e&&framework.ajax(framework.buildFWLink("ajax/bean",o,n),{method:"DELETE"}).done(a).fail((function(e){fwdom.alert("<h3>Delete failed</h3>"+e.responseText)}))}))},editcall:function(e){return framework.ajax(framework.buildFWLink("ajax",e.op,e.bean,e.pk,e.name),{method:putorpatch,data:{value:e.value}})},removeNode:function(e){var t=[e];if(e.hasAttribute("rowspan")){let o=parseInt(e.getAttribute("rowspan"))-1;for(;o>0;)t[o]=t[o-1].elementSibling}for(let e of t)e.parentNode.removeChild(e)},fadetodel:function(e,t=null){e.classList.add("fader"),e.style.opacity="0",setTimeout((function(){framework.removeNode(e),null!==t&&t()}),1500)},dodelbean:function(e,t,o,n="",a=null){let r=t.closest("[data-id]");r instanceof jQuery&&(r=r[0]),framework.deletebean(e,t,o,r.getAttribute("data-id"),(function(){framework.fadetodel(r,a)}),n)},containerClick:function(e){fwdom.stop(e);const t=e.target.classList;e.data.clicks.forEach((function(o){let[n,a,r]=o;t.contains(n)&&a(e,e.target,e.data.bean,r)}))},goFWLink:function(e,t,o,n="/"){let a=e.closest("[data-id]");a instanceof jQuery&&(a=a[0]),window.location.href=framework.buildFWLink(t,o,a.getAttribute("data-id"),n)},goedit:function(e,t,o){framework.goFWLink(t,"admin/edit",o)},goLink:function(e){window.location.href=e.target.getAttribute("href")},goview:function(e,t,o){framework.goFWLink(t,"admin/view",o)},beanCreate:function(e,t,o,n){framework.ajax(framework.buildFWLink("ajax/bean",e),{method:"POST",data:t}).done(o).fail((function(t){fwdom.alert("<h3>Failed to create new "+e+"</h3>"+t.responseText)})).always((function(){(n instanceof Object?n:document.getElementById(n)).disabled=!1}))},addMore:function(e){fwdom.stop(e);const t=document.getElementById("mrow"),o=t.previousElementSibling.cloneNode(!0);for(var n of o.getElementsByTagName("input"))"checkbox"==n.getAttribute("type")||"radio"==n.getAttribute("type")?n.checked=!1:n.value="";for(n of o.getElementsByTagName("textarea"))n.innerHTML="";for(n of o.getElementsByTagName("option"))n.selected=!1;for(n of o.getElementsByTagName("select"))n.children[0].selected=!0;t.parentNode.insertBefore(o,t)},easeInOut:function(e,t,o,n,a){return Math.ceil(e+Math.pow(1/o*n,a)*(t-e))},doBGFade:function(e,t,o,n,a,r,i){e.bgFadeInt&&window.clearInterval(e.bgFadeInt);let s=0;e.bgFadeInt=window.setInterval((function(){e.css("backgroundcolor","rgb("+framework.easeInOut(t[0],o[0],a,s,i)+","+framework.easeInOut(t[1],o[1],a,s,i)+","+framework.easeInOut(t[2],o[2],a,s,i)+")"),s+=1,s>a&&(e.css("backgroundcolor",n),window.clearInterval(e.bgFadeInt))}),r)},addElement:function(e,t,o,n,a=null){const r=document.createElement(t),i=undefined;Object.keys(o).forEach((function(e){r.setAttribute(e,o[e])})),r.innerHTML=n,null===a?e.appendChild(r):e.insertBefore(r,a)},alert:function(e,t=""){framework.currentModal=document.createTextNode('<div class="modal" id="_fwalert" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">'+t+'</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body"><p>'+e+'</p></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">OK</button></div></div></div></div>'),framework.currentModal.addEventListener("hide.bs.modal",(function(){framework.currentModal.remove(),framework.currentModal=null}))},confirm:function(e,t,o){}};framework.tableClick=framework.containerClick;
//# sourceMappingURL=util-min.js.map