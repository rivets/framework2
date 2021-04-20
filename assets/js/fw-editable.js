 fwdom.makeEdit = function(d)
 {
    let type = d.getAttribute('data-type');
    let box = '';
    let text = d.innerText;
    if (text === '')
    {
        text = d.getAttribute('data=empty');
    }
    switch (type)
    {
    case 'select':
        box = '<span>Not supported yet<span>';
        break;
    case 'textarea':
        box = '<textarea rows="5" cols="80">' + text + '"</textarea>';
        break;
    default:
        box = '<input type="'+type+'" value="' + text + '"/>';
        break;
    }
    return box + '<i class="fas fa-times-circle edno"></i><i class="fas fa-check-circle edyes"></i>';
 };

 fwdom.editable = function(div) {
    let name = div.getAttribute('name');
    div.addEventListener('click', function(e){
        let popover = new bootstrap.Popover(div, {
            title: div.getAttribute('data-title'),
            html: true,
            trigger: 'click focus',
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