 fwdom.makeEdit = function(d)
 {
    return '<input type="text" value="' + d.innerText + '"/>' +
       '<i class="fas fa-times-circle edno"></i><i class="fas fa-check-circle edyes"></i>';
 };

 fwdom.editable = function(div) {
    div.style.cursor = 'pointer';
    div.addEventListener('click', function(e){
        let popover = new bootstrap.Popover(div, {
            title: 'Edit',
            html: true,
            sanitize: false,
            content: fwdom.makeEdit(div),
            placement: 'auto',
            template: '<div class="popover" role="tooltip"><div class="popover-arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>'
        });
        popover.show();
        popover.tip.querySelector('input').focus();
        popover.tip.querySelector('.edno').addEventListener('click', function(e){
            popover.dispose();
        });
        popover.tip.querySelector('.edyes').addEventListener('click', function(e){
            popover.dispose();
        });
    });
 };