var fweditable={popover:null,inline:null,domid:-1,taid:"",edOptions:[],emptyText:"--------",emptyTiny:/<p><br[^>]*><\/p>/,makeEdit:function(e){const t=fweditable.edOptions[e.getAttribute("data-editable-id")];let i,a=e.innerHTML;switch(fweditable.taid="",a===t.emptyText&&(a=""),t.type){case"select":i='<select class="edbox">',"function"==typeof t.source&&(t.source=t.source());for(let e of t.source)if("object"==typeof e){let t=e.value;t instanceof String&&t.includes('"')&&(t=t.replace(/"/,"&quot;")),i+='<option value="'+t+'"'+(e.text==a?" selected":"")+">"+e.text+"</option>"}else i+="<option"+(e==a?" selected":"")+">"+e+"</option>";i+="</select>";break;case"textarea":i='<textarea rows="5" cols="80" class="edbox">'+a+"</textarea>";break;case"text":a.includes('"')&&(a=a.replace(/"/,"&quot;")),a.includes("&")&&(a=a.replace(/"/,"&amp;")),i='<form id="edboxfrm"><input type="'+t.type+'" value="'+a+'" class="edbox"/></form>';break;case"html":fweditable.taid="hta"+fweditable.domid,fweditable.domid+=1,i='<textarea rows="5" cols="80" class="edbox" id="'+fweditable.taid+'">'+a+"</textarea>";break;default:a.includes('"')&&(a=a.replace(/"/,"&quot;")),a.includes("&")&&(a=a.replace(/"/,"&amp;")),i='<input type="'+t.type+'" value="'+a+'" class="edbox"/>'}return i+'<i class="fad fa-times-circle edno"></i><i class="fad fa-check-circle edyes"></i>'},popDispose:function(){document.body.removeEventListener("click",fweditable.outsideClick),null!==fweditable.popover&&(fweditable.popover.dispose(),fweditable.popover=null)},outsideClick:function(e){fweditable.inline==e.target||fweditable.inline==fweditable.popover.tip||fweditable.popover.tip.contains(e.target)||(null!=e.target.closest('div[class~="tox-dialog"]')||e.target.classList.contains("tox-dialog-wrap__backdrop")?fwdom.stop(e):fweditable.popDispose(e))},editUpdate:function(e,t){return framework.ajax(framework.buildFWLink("ajax",e.op,e.bean,e.key,e.field),{method:framework.putorpatch,data:{value:t}})},popClick:function(e){if(e.classList.contains("disabled"))return;const t=fweditable.edOptions[e.getAttribute("data-editable-id")],i=t.type,a=t.title;null!==fweditable.inline&&fweditable.popDispose();let l=new bootstrap.Popover(e,{title:a,container:"body",html:!0,sanitize:!1,content:fweditable.makeEdit(e),placement:"auto",template:'<div class="popover pop'+i+'" role="tooltip"><div class="popover-arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>'});l.show();let o=l.tip;e.addEventListener("shown.bs.popover",(function(e){fwdom.stop(e);const t=document.querySelector(".popover").querySelector(".edbox");t.focus({preventScroll:!0}),"SELECT"!=t.nodeName&&t.setSelectionRange(1e5,1e5),""!==fweditable.taid&&tinymce.init({selector:"textarea#"+fweditable.taid,branding:!1,toolbar:"undo redo | bold italic | superscript subscript | bullist numlist | link | code | charmap",menubar:!1,plugins:"lists link code charmap"})})),o.querySelector(".edno").addEventListener("click",fweditable.popDispose);const d=function(e){fwdom.stop(e);let t=fweditable.edOptions[fweditable.inline.getAttribute("data-editable-id")],i=o.querySelector(".edbox");""!==fweditable.taid&&(i.value=tinymce.activeEditor.getContent({format:"raw"}),i.value.match(fweditable.emptyTiny)?i.value="":i.value.replace(/<br[^>*]><\/p>/i,"</p>")),i.value!=fweditable.inline.innerText&&(null==t.update?framework.alert("No update function defined"):t.update(t,i.value).done((function(){if("select"==t.type){for(let e of t.source)if("object"==typeof e){if(i.value==e.value){fweditable.inline.innerText=e.text;break}}else if(i.value==e){fweditable.inline.innerText=e;break}}else""===i.value?(fweditable.inline.innerText=t.emptyText,fweditable.inline.classList.add("edempty")):(fweditable.inline.innerText=i.value,fweditable.inline.classList.remove("edempty"))})).fail((function(e){framework.alert("Update failed")}))),fweditable.popDispose()};o.querySelector(".edyes").addEventListener("click",d),"text"==i&&document.getElementById("edboxfrm").addEventListener("submit",d),document.body.addEventListener("click",fweditable.outsideClick),fweditable.popover=l,fweditable.inline=e},editable:function(e,t=null){let i={type:"text",emptyText:fweditable.emptyText,title:"Edit String",update:null};if(null!=t)for(let e in t)i[e]=t[e];let a=e.dataset;for(let e in a)i[e]=a[e];fweditable.domid+=1,fweditable.edOptions[fweditable.domid]=i,e.setAttribute("data-editable-id",fweditable.domid),""===e.innerHTML&&(e.innerHTML=i.emptyText,e.classList.add("edempty")),e.addEventListener("click",(function(e){fwdom.stop(e);const t=this.getAttribute("data-editable-id"),i=fweditable.edOptions[t],a=this;"string"==typeof i.source?framework.getJSON(i.source,(function(e){fweditable.edOptions[t].source=e,fweditable.popClick(a)}),(function(e){framework.alert("Cannot fetch list")}),!1):fweditable.popClick(a)}))}};