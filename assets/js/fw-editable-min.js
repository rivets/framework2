fwdom.makeEdit=function(e){let t=e.getAttribute("data-type"),i="";switch(t){case"select":i="";break;case"textarea":i='<textarea rows="5" cols="80">'+e.innerText+'"</textarea>';break;default:i='<input type="'+t+'" value="'+e.innerText+'"/>';break}return i+'<i class="fas fa-times-circle edno"></i><i class="fas fa-check-circle edyes"></i>'},fwdom.editable=function(e){let t=e.getAttribute("name");e.addEventListener("click",(function(t){let i=new bootstrap.Popover(e,{title:e.getAttribute("data-title"),html:!0,sanitize:!1,content:fwdom.makeEdit(e),placement:"auto",template:'<div class="popover" role="tooltip"><div class="popover-arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>'});i.show(),i.tip.querySelector("input").focus(),i.tip.querySelector(".edno").addEventListener("click",(function(e){i.dispose()})),i.tip.querySelector(".edyes").addEventListener("click",(function(e){i.dispose()}))}))};
//# sourceMappingURL=fw-editable-min.js.map