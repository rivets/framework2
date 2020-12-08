class FWAjaxRQ{constructor(e,t){this.request=e,this.options=t}done(e){return this.request.onload=e,this}fail(e){return this.request.onerror=e,this}always(e){let t=this.request.onload,o=this.request.onerror;return null!==t&&(this.request.onload=function(o){t(o.response),e(o.response)}),null!==o&&(this.request.onerror=function(t){o(t.response),e(t.response)}),this}}var framework={makeQString:function(e){var t="";let o="";for(var a in e)e.hasOwnProperty(a)&&(t+=o+encodeURI(a+"="+e[a]),o="&");return t},onloaded:function(e,t){e.status>=200&&e.status<400?t.hasOwnProperty("success")&&t.success(e.response):t.hasOwnProperty("fail")&&t.fail(e.response),t.hasOwnProperty("always")&&t.always(e.response)},onfailed:function(e,t){t.hasOwnProperty("fail")&&t.fail(e.response),t.hasOwnProperty("always")&&t.always(e.response)},ajax:function(e,t){let o=new XMLHttpRequest,a=t.hasOwnProperty("method")?t.method:"GET",n=t.hasOwnProperty("data")?framework.makeQString(t.data):"",r=t.hasOwnProperty("type")?t.type:""!==n?"application/x-www-form-urlencoded; charset=UTF-8":"text/plain; charset=UTF-8";return o.open(a,e,!0),o.setRequestHeader("Content-Type",r),o.onload=function(){framework.onloaded(this,t)},o.onerror=function(){framework.onfailed(this,t)},o.send(n),new FWAjaxRQ(o,t)},getJSON:function(e,t,o){var a=new XMLHttpRequest;a.open("GET",e,!0),a.setRequestHeader("Accept","application/json"),a.onload=function(){this.status>=200&&this.status<400?t(JSON.parse(this.response)):o(this)},a.onerror=function(){o(this)},a.send()},mktoggle:function(e,t){return'<i class="'+e+" fas fa-toggle-"+(t?"on":"off")+'"></i>'},tick:function(e){return framework.mktoggle("",e)},toggle:function(e){e.classList.toggle("fa-toggle-off"),e.classList.toggle("fa-toggle-on")},dotoggle:function(e,t,o,a){e.preventDefault(),e.stopPropagation();let n=t.classList;if(!n.contains("fadis"))if(n.contains("htick")){const e=t.nextElementSibling;e.value=1==e.value?0:1,framework.toggle(t)}else{let e=t.closest("[data-id]");e instanceof jQuery&&(e=e[0]),framework.ajax(base+"/ajax/toggle/"+o+"/"+e.getAttribute("data-id")+"/"+a,{method:putorpatch,success:function(){framework.toggle(t)},fail:function(e){bootbox.alert("<h3>Toggle failed</h3>"+e.responseText)}})}},deletebean:function(e,t,o,a,n,r=""){e.preventDefault(),e.stopPropagation(),""===r&&(r="this "+o),bootbox.confirm("Are you sure you you want to delete "+r+"?",(function(e){e&&framework.ajax(base+"/ajax/bean/"+o+"/"+a+"/",{method:"DELETE",success:n,fail:function(e){bootbox.alert("<h3>Delete failed</h3>"+e.responseText)}})}))},editcall:function(e){const t=base+"/ajax/"+e.op+"/"+e.bean+"/"+e.pk+"/"+e.name+"/";return framework.ajax(t,{method:putorpatch,data:{value:e.value}})},removeNode:function(e){var t=[e];if(e.hasAttribute("rowspan")){let o=parseInt(e.getAttribute("rowspan"))-1;for(;o>0;)t[o]=t[o-1].elementSibling}for(let e of t)e.parentNode.removeChild(e)},fadetodel:function(e,t=null){e.classList.add("fader"),e.style.opacity="0",setTimeout((function(){framework.removeNode(e),null!==t&&t()}),1500)},dodelbean:function(e,t,o,a="",n=null){let r=t.closest("[data-id]");r instanceof jQuery&&(r=r[0]),framework.deletebean(e,t,o,r.getAttribute("data-id"),(function(){framework.fadetodel(r,n)}),a)},tableClick:function(e){e.preventDefault();const t=e.target.classList;e.data.clicks.forEach((function(o){let[a,n,r]=o;t.contains(a)&&n(e,e.target,e.data.bean,r)}))},goedit:function(e,t,o){let a=t.closest("[data-id]");a instanceof jQuery&&(a=a[0]),window.location.href=base+"/admin/edit/"+o+"/"+a.getAttribute("data-id")+"/"},goview:function(e,t,o){window.location.href=base+"/admin/view/"+o+"/"+t.parent().parent().data("id")+"/"},beanCreate:function(e,t,o,a){framework.ajax(base+"/ajax/bean/"+e+"/",{method:"POST",data:t,success:o,fail:function(t){bootbox.alert("<h3>Failed to create new "+e+"</h3>"+t.responseText)},always:function(){document.getElementById(a).setAttribute("disabled",!1)}})},addMore:function(e){e.preventDefault(),e.stopPropagation();const t=document.getElementById("mrow"),o=t.previousElementSibling.cloneNode(!0);for(var a of o.getElementsByTagName("input"))"checkbox"==a.getAttribute("type")||"radio"==a.getAttribute("type")?a.removeAttribute("checked"):a.setAttribute("value","");for(a of o.getElementsByTagName("textarea"))a.innerHTML="";for(a of o.getElementsByTagName("option"))a.removeAttribute("selected",!1);for(a of o.getElementsByTagName("select"))a.children[0].setAttribute("selected","selected");t.parentNode.insertBefore(o,t)},easeInOut:function(e,t,o,a,n){return Math.ceil(e+Math.pow(1/o*a,n)*(t-e))},doBGFade:function(e,t,o,a,n,r,s){e.bgFadeInt&&window.clearInterval(e.bgFadeInt);let i=0;e.bgFadeInt=window.setInterval((function(){e.css("backgroundcolor","rgb("+framework.easeInOut(t[0],o[0],n,i,s)+","+framework.easeInOut(t[1],o[1],n,i,s)+","+framework.easeInOut(t[2],o[2],n,i,s)+")"),i+=1,i>n&&(e.css("backgroundcolor",a),window.clearInterval(e.bgFadeInt))}),r)}};