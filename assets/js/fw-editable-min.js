fwdom.popover=null,fwdom.inline=null,fwdom.domid=-1,fwdom.edOptions=[],fwdom.makeEdit=function(){const e=fwdom.edOptions[this.getAttribute("data-editable-id")];let t=this.innerText,o;switch(t===e.emptyText&&(t=""),e.type){case"select":o='<select class="edbox">',"string"==typeof e.source?framework.getJSON(e.source,(function(t){e.source=t}),(function(e){fwdom.alert("Cannot fetch list")}),!1):"function"==typeof e.source&&(e.source=e.source());for(let i of e.source)o+="object"==typeof i?'<option value="'+i.value+'"'+(i.text==t?" selected":"")+">"+i.text+"</option>":"<option"+(i==t?" selected":"")+">"+i+"</option>";o+="</select>";break;case"textarea":o='<textarea rows="5" cols="80" class="edbox">'+t+"</textarea>";break;default:o='<input type="'+e.type+'" value="'+t+'" class="edbox"/>';break}return o+'<i class="fas fa-times-circle edno"></i><i class="fas fa-check-circle edyes"></i>'},fwdom.popDispose=function(){document.body.removeEventListener("click",fwdom.outsideClick),null!==fwdom.popover&&(fwdom.popover.dispose(),fwdom.popover=null)},fwdom.outsideClick=function(e){fwdom.inline==e.target||fwdom.inline==fwdom.popover.tip||fwdom.popover.tip.contains(e.target)||fwdom.popDispose(e)},fwdom.editUpdate=function(e,t){return framework.ajax(framework.buildFWLink("ajax",e.op,e.bean,e.key,e.field),{method:putorpatch,data:{value:t}})},fwdom.editable=function(e,t=null){let o={type:"text",emptyText:"------",title:"Edit String",update:null};if(null!=t)for(let e in t)o[e]=t[e];let i=e.dataset;for(let e in i)o[e]=i[e];fwdom.domid+=1,fwdom.edOptions[fwdom.domid]=o,e.setAttribute("data-editable-id",fwdom.domid),""===e.innerText&&(e.innerText=o.emptyText,e.classList.add("edempty")),e.addEventListener("click",(function(t){if(fwdom.stop(t),e.hasAttribute("disabled"))return;const i=fwdom.edOptions[this.getAttribute("data-editable-id")].type;null!==fwdom.inline&&fwdom.popDispose();let d=new bootstrap.Popover(e,{title:o.title,container:"body",html:!0,sanitize:!1,content:fwdom.makeEdit(),placement:"auto",template:'<div class="popover pop'+i+'" role="tooltip"><div class="popover-arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>'});d.show();let n=d.tip;n.querySelector(".edno").addEventListener("click",fwdom.popDispose),n.querySelector(".edyes").addEventListener("click",(function(e){fwdom.stop(e);let t=fwdom.edOptions[fwdom.inline.getAttribute("data-editable-id")],o=n.querySelector(".edbox");o.value!=fwdom.inline.innerText&&(null==t.update?fwdom.alert("No update function defined"):t.update(t,o.value).done((function(e){console.log(e),""===o.value?(fwdom.inline.innerText=t.emptytext,fwdom.inline.classList.add("edempty")):(fwdom.inline.innerText=o.value,fwdom.inline.classList.remove("edempty"))})).fail((function(e){console.log(e),fwdom.alert("Update failed")}))),fwdom.popDispose()})),document.body.addEventListener("click",fwdom.outsideClick),fwdom.popover=d,fwdom.inline=e}))};
//# sourceMappingURL=fw-editable-min.js.map