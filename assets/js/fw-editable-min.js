fwdom.popover=null,fwdom.inline=null,fwdom.edOptions=[],fwdom.makeEdit=function(e,t){let o="",i=e.innerText;switch(i===e.getAttribute("data-emptytext")&&(i=""),t){case"select":o='<select class="edbox">';for(let e of i.split(";"))o+="<option>"+e+"</option>";o+="</select>";break;case"textarea":o='<textarea rows="5" cols="25" class="edbox">'+i+"</textarea>";break;default:o='<input type="'+t+'" value="'+i+'" class="edbox"/>';break}return o+'<i class="fas fa-times-circle edno"></i><i class="fas fa-check-circle edyes"></i>'},fwdom.popDispose=function(e){document.body.removeEventListener("click",fwdom.outsideClick),null!==fwdom.popover&&(fwdom.popover.dispose(),fwdom.popover=null)},fwdom.outsideClick=function(e){fwdom.inline==e.target||fwdom.inline==fwdom.popover.tip||fwdom.popover.tip.contains(e.target)||fwdom.popDispose(e)},fwdom.editable=function(e,t){""===e.innerText&&(e.innerText=e.getAttribute("data-emptytext"),e.classList.add("edempty"));let o=e.getAttribute("name"),i=e.getAttribute("data-type");e.addEventListener("click",(function(t){null!==fwdom.inline&&fwdom.popDispose();let o=new bootstrap.Popover(e,{title:this.getAttribute("data-title"),html:!0,sanitize:!1,content:fwdom.makeEdit(this,i,this.getAttribute("data-value")),placement:"auto",template:'<div class="popover" role="tooltip"><div class="popover-arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>'});o.show();let d=o.tip;d.querySelector(".edno").addEventListener("click",fwdom.popDispose),d.querySelector(".edyes").addEventListener("click",(function(e){let t=d.querySelector(".edbox");t.value!=fwdom.inline.innerText&&(alert("update"),""===t.value?(fwdom.inline.innerText=fwdom.inline.getAttribute("data-emptytext"),fwdom.inline.classList.add("edempty")):(fwdom.inline.innerText=t.value,fwdom.inline.classList.remove("edempty"))),fwdom.popDispose()})),document.body.addEventListener("click",fwdom.outsideClick),fwdom.popover=o,fwdom.inline=e}))};
//# sourceMappingURL=fw-editable-min.js.map