fwdom.popover=null,fwdom.inline=null,fwdom.edOptions=[],fwdom.defaultOptions={type:"text",emptytext:"------",title:"Edit String",update:null},fwdom.makeEdit=function(e){const t=fwdom.edOptions[e];let o=e.innerText,i;switch(o===t.emptyText&&(o=""),t.type){case"select":i='<select class="edbox">';for(let e of t.source)i+="<option"+(e==o?" selected":"")+">"+e+"</option>";i+="</select>";break;case"textarea":i='<textarea rows="5" cols="25" class="edbox">'+o+"</textarea>";break;default:i='<input type="'+t.type+'" value="'+o+'" class="edbox"/>';break}return i+'<i class="fas fa-times-circle edno"></i><i class="fas fa-check-circle edyes"></i>'},fwdom.popDispose=function(){document.body.removeEventListener("click",fwdom.outsideClick),null!==fwdom.popover&&(fwdom.popover.dispose(),fwdom.popover=null)},fwdom.outsideClick=function(e){fwdom.inline==e.target||fwdom.inline==fwdom.popover.tip||fwdom.popover.tip.contains(e.target)||fwdom.popDispose(e)},fwdom.editable=function(e,t=null){let o;if(null!=t)fwdom.edOptions[fwdom.edOptions.length]=t,o=t;else{let t=fwdom.defaultOptions,o=e.dataset;for(let e in o)t[e]=o[e];fwdom.edOptions[fwdom.edOptions.length]=t}e.setAttribute("data-editable-id",fwdom.edOptions.length),""===e.innerText&&(e.innerText=o.emptyText,e.classList.add("edempty")),e.addEventListener("click",(function(t){fwdom.stop(t),null!==fwdom.inline&&fwdom.popDispose();let i=new bootstrap.Popover(e,{title:o.title,html:!0,sanitize:!1,content:fwdom.makeEdit(this),placement:"auto",template:'<div class="popover" role="tooltip"><div class="popover-arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>'});i.show();let d=i.tip;d.querySelector(".edno").addEventListener("click",fwdom.popDispose),d.querySelector(".edyes").addEventListener("click",(function(e){fwdom.stop(e);let t=fwdom.edOptions[fwdom.inline.getAttribute("data-editable-id")],o=d.querySelector(".edbox");if(o.value!=fwdom.inline.innerText){if(""===o.value?(fwdom.inline.innerText=t.emptytext,fwdom.inline.classList.add("edempty")):(fwdom.inline.innerText=o.value,fwdom.inline.classList.remove("edempty")),console.log(t),null==t.update)return void fwdom.alert("No update function defined");t.update(t,o.value)}fwdom.popDispose()})),document.body.addEventListener("click",fwdom.outsideClick),fwdom.popover=i,fwdom.inline=e}))};
//# sourceMappingURL=fw-editable-min.js.map