fwdom.makeEdit=function(e,t){let o="",i=e.innerText;switch(""===i&&(i=e.getAttribute("data=empty")),t){case"select":o="<span>Not supported yet<span>";break;case"textarea":o='<textarea rows="5" cols="80" class="edbox">'+i+'"</textarea>';break;default:o='<input type="'+t+'" value="'+i+'" class="edbox"/>';break}return o+'<i class="fas fa-times-circle edno"></i><i class="fas fa-check-circle edyes"></i>'},fwdom.outsideClick=function(e){fwdom.tip==e.target||fwdom.tip.contains(e.target)||(popover.dispose(),document.body.removeEventListener("click",fwdom.outsideClock))},fwdom.editable=function(e){let t=e.getAttribute("name"),o=e.getAttribute("data-type");e.addEventListener("click",(function(t){fwdom.popover=new bootstrap.Popover(e,{title:e.getAttribute("data-title"),html:!0,sanitize:!1,content:fwdom.makeEdit(e,o),placement:"auto",template:'<div class="popover" role="tooltip"><div class="popover-arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>'}),fwdom.popover.show();let i=fwdom.popover.tip.querySelector(".edbox");i.focus(),i.addEventListener("blur",(function(e){console.log(e)})),fwdom.tip=popover.tip,fwdom.tip.querySelector(".edno").addEventListener("click",(function(e){fwdom.popover.dispose()})),fwdom.tip.querySelector(".edyes").addEventListener("click",(function(e){fwdom.popover.dispose()})),document.body.addEventListener("click",fwdom.outsideClick)}))};
//# sourceMappingURL=fw-editable-min.js.map