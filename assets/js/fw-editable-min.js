fwdom.popover=null,fwdom.inline=null,fwdom.makeEdit=function(e,t){let o="",i=e.innerText;switch(""===i&&(i=e.getAttribute("data-emptytext")),t){case"select":o="<span>Not supported yet<span>";break;case"textarea":o='<textarea rows="5" cols="25" class="edbox">'+i+'"</textarea>';break;default:o='<input type="'+t+'" value="'+i+'" class="edbox"/>';break}return o+'<i class="fas fa-times-circle edno"></i><i class="fas fa-check-circle edyes"></i>'},fwdom.popDispose=function(e){document.body.removeEventListener("click",fwdom.outsideClick),fwdom.popover.dispose(),fwdom.popover=null,fwdom.tip=null},fwdom.outsideClick=function(e){fwdom.inline==e.target||fwdom.inline==fwdom.popover.tip||fwdom.popover.tip.contains(e.target)||fwdom.popDispose(e)},fwdom.editable=function(e){let t=e.getAttribute("name"),o=e.getAttribute("data-type");e.addEventListener("click",(function(t){null!==fwdom.inline&&fwdom.popDispose();let i=new bootstrap.Popover(e,{title:e.getAttribute("data-title"),html:!0,sanitize:!1,content:fwdom.makeEdit(e,o),placement:"auto",template:'<div class="popover" role="tooltip"><div class="popover-arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>'});i.show();let d=i.tip;d.querySelector(".edno").addEventListener("click",fwdom.popDispose),d.querySelector(".edyes").addEventListener("click",(function(e){let t=d.querySelector(".edbox");switch(o){case"select":t.innerText!=fwdom.inline.innerText&&alert("update");break;case"textarea":break;default:t.value!=fwdom.inline.innerText&&alert("update")}fwdom.popDispose()})),document.body.addEventListener("click",fwdom.outsideClick),fwdom.popover=i,fwdom.inline=e}))};
//# sourceMappingURL=fw-editable-min.js.map