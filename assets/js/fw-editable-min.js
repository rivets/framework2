fwdom.makeEdit=function(e,t){let a="",i=e.innerText;switch(""===i&&(i=e.getAttribute("data=empty")),t){case"select":a="<span>Not supported yet<span>";break;case"textarea":a='<textarea rows="5" cols="80" class="edbox">'+i+'"</textarea>';break;default:a='<input type="'+t+'" value="'+i+'" class="edbox"/>';break}return a+'<i class="fas fa-times-circle edno"></i><i class="fas fa-check-circle edyes"></i>'},fwdom.editable=function(e){let t=e.getAttribute("name"),a=e.getAttribute("data-type");e.addEventListener("click",(function(t){let i=new bootstrap.Popover(e,{title:e.getAttribute("data-title"),html:!0,trigger:"click focus",sanitize:!1,content:fwdom.makeEdit(e,a),placement:"auto",template:'<div class="popover" role="tooltip"><div class="popover-arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>'});i.show();let o=i.tip.querySelector(".edBox");o.focus(),o.addEventListener("blur",(function(e){console.log(e)})),i.tip.querySelector(".edno").addEventListener("click",(function(e){i.dispose()})),i.tip.querySelector(".edyes").addEventListener("click",(function(e){i.dispose()}))}))};
//# sourceMappingURL=fw-editable-min.js.map