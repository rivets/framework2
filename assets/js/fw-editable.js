 fwdom.makeEdit = function(d)
 {
    return '<form><input type="text" value="' + d.innerText + '"/>' +
       '<i class="fas fa-times-circle red"></i><i class="fas fa-times-check green"></i></form>';
 };

 fwdom.editable = function(div) {
    div.style.cursor = 'pointer';
    div.addEventListener('click', function(e){
        let popover = new bootstrap.Popover(div, {
            title: 'Edit',
            content: fwdom.makeEdit(div),
            placement: 'auto',
            template: '<div class="popover" role="tooltip"><div class="popover-arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>'
        });
        popover.show();
    });
 };