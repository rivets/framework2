function mktoggle(e,a){return'<i class="'+e+" fas fa-toggle-"+(a?"on":"off")+'"></i>'}function tick(e){return mktoggle("",e)}function toggle(e){e.toggleClass("fa-toggle-off").toggleClass("fa-toggle-on")}function dotoggle(e,a,t,o){if(e.preventDefault(),e.stopPropagation(),!a.hasClass("fadis"))if(a.hasClass("htick")){// this is not yet created so tick the hidden box
var n=a.next();n.val(1==n.val()?0:1),toggle(a)}else{// toggle at the other end
var i=a.parent().parent();$.ajax(base+"/ajax/toggle/"+t+"/"+i.data("id")+"//"+o,{method:"PATCH"}).done(function(){toggle(a)}).fail(function(e){bootbox.alert("<h3>Toggle failed</h3>"+e.responseText)})}}function dodelbean(e,t,o){e.preventDefault(),e.stopPropagation(),bootbox.confirm("Are you sure you you want to delete this "+o+"?",function(e){if(e){// user picked OK
var a=$(t).parent().parent();$.ajax(base+"/ajax/bean/"+o+"/"+a.data("id")+"/",{method:"DELETE"}).done(function(){a.css("background-color","yellow").fadeOut(1500,function(){a.remove()})}).fail(function(e){bootbox.alert("<h3>Delete failed</h3>"+e.responseText)})}})}function tableClick(a){a.preventDefault();var t=$(a.target);a.data.clicks.forEach(function(e){t.hasClass(e[0])&&e[1](a,t,a.data.bean,e[2])})}function goedit(e,a,t){window.location.href=base+"/admin/edit/"+t+"/"+a.parent().parent().data("id")+"/"}function goview(e,a,t){window.location.href=base+"/admin/view/"+t+"/"+a.parent().parent().data("id")+"/"}function beanCreate(a,e,t,o){$.post(base+"/ajax/bean/"+a+"/",e).done(t).fail(function(e){bootbox.alert("<h3>Failed to create new "+a+" failed<h3>"+e.responseText)}).always(function(e){$(o).attr("disabled",!1)})}function addMore(e){e.preventDefault(),$("#mrow").before($("#example").clone()),$("input,textarea",$("#mrow").prev()).val(""),// clear the new inputs
$("option",$("#mrow").prev()).prop("selected",!1)}