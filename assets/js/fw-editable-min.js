var fweditable={popover:null,inline:null,domid:-1,edOptions:[],emptyText:"--------",makeEdit:function(e){const t=fweditable.edOptions[e.getAttribute("data-editable-id")];let i=e.innerHTML,l;switch(i===t.emptyText&&(i=""),t.type){case"select":l='<select class="edbox">',"function"==typeof t.source&&(t.source=t.source());for(let e of t.source)l+="object"==typeof e?'<option value="'+e.value+'"'+(e.text==i?" selected":"")+">"+e.text+"</option>":"<option"+(e==i?" selected":"")+">"+e+"</option>";l+="</select>";break;case"textarea":l='<textarea rows="5" cols="80" class="edbox">'+i+"</textarea>";break;default:l='<input type="'+t.type+'" value="'+i+'" class="edbox"/>';break}return l+'<i class="fas fa-times-circle edno"></i><i class="fas fa-check-circle edyes"></i>'},popDispose:function(){document.body.removeEventListener("click",fweditable.outsideClick),null!==fweditable.popover&&(fweditable.popover.dispose(),fweditable.popover=null)},outsideClick:function(e){fweditable.inline==e.target||fweditable.inline==fweditable.popover.tip||fweditable.popover.tip.contains(e.target)||fweditable.popDispose(e)},editUpdate:function(e,t){return framework.ajax(framework.buildFWLink("ajax",e.op,e.bean,e.key,e.field),{method:putorpatch,data:{value:t}})},popClick:function(e){if(e.hasAttribute("disabled"))return;const t=fweditable.edOptions[e.getAttribute("data-editable-id")],i=t.type,l=t.title;null!==fweditable.inline&&fweditable.popDispose();let o=new bootstrap.Popover(e,{title:l,container:"body",html:!0,sanitize:!1,content:fweditable.makeEdit(e),placement:"auto",template:'<div class="popover pop'+i+'" role="tooltip"><div class="popover-arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>'});o.show();let a=o.tip;a.querySelector(".edno").addEventListener("click",fweditable.popDispose),a.querySelector(".edyes").addEventListener("click",(function(e){fwdom.stop(e);let t=fweditable.edOptions[fweditable.inline.getAttribute("data-editable-id")],i=a.querySelector(".edbox");i.value!=fweditable.inline.innerHTML&&(null==t.update?fwdom.alert("No update function defined"):t.update(t,i.value).done((function(){if("select"==t.type){for(let e of t.source)if("object"==typeof e){if(i.value==e.value){fweditable.inline.innerHTML=e.text;break}}else if(i.value==e){fweditable.inline.innerHTML=e;break}}else""===i.value?(fweditable.inline.innerHTML=t.emptyText,fweditable.inline.classList.add("edempty")):(fweditable.inline.innerHTML=i.value,fweditable.inline.classList.remove("edempty"))})).fail((function(e){fwdom.alert("Update failed")}))),fweditable.popDispose()})),document.body.addEventListener("click",fweditable.outsideClick),fweditable.popover=o,fweditable.inline=e},editable:function(e,t=null){let i={type:"text",emptyText:fweditable.emptyText,title:"Edit String",update:null};if(null!=t)for(let e in t)i[e]=t[e];let l=e.dataset;for(let e in l)i[e]=l[e];fweditable.domid+=1,fweditable.edOptions[fweditable.domid]=i,e.setAttribute("data-editable-id",fweditable.domid),""===e.innerHTML&&(e.innerHTML=i.emptyText,e.classList.add("edempty")),e.addEventListener("click",(function(e){fwdom.stop(e);const t=this.getAttribute("data-editable-id"),i=fweditable.edOptions[t],l=this;"string"==typeof i.source?framework.getJSON(i.source,(function(e){fweditable.edOptions[t].source=e,fweditable.popClick(l)}),(function(e){fwdom.alert("Cannot fetch list")}),!1):fweditable.popClick(l)}))}};
//# sourceMappingURL=fw-editable-min.js.map