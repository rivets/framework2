/* globals document, fwdom, bootstrap */
/* jshint undef: true, unused: false */
fwdom.makeEdit = function(d, type)
 {
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
        box = '<textarea rows="5" cols="80" class="edbox">' + text + '"</textarea>';
        break;
    default:
        box = '<input type="'+type+'" value="' + text + '" class="edbox"/>';
        break;
    }
    return box + '<i class="fas fa-times-circle edno"></i><i class="fas fa-check-circle edyes"></i>';
 };

 fwdom.outsideClick = function(e)
 {
    if (fwdom.tip != e.target && !fwdom.tip.contains(e.target))
    {
        fwdom.popover.dispose();
        document.body.removeEventListener('click', fwdom.outsideClock);
    }
 };

 fwdom.editable = function(div) {
    let name = div.getAttribute('name');
    let type = div.getAttribute('data-type');
    div.addEventListener('click', function(e){
        fwdom.popover = new bootstrap.Popover(div, {
            title: div.getAttribute('data-title'),
            html: true,
            sanitize: false,
            content: fwdom.makeEdit(div, type),
            placement: 'auto',
            template: '<div class="popover" role="tooltip"><div class="popover-arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>'
        });
        fwdom.popover.show();
        fwdom.tip = fwdom.popover.tip;
        let box = fwdom.tip.querySelector('.edbox');
        box.focus();
        box.addEventListener('blur', function(e){
            console.log(e);
        });

        fwdom.tip.querySelector('.edno').addEventListener('click', function(e){
            fwdom.popover.dispose();
        });
        fwdom.tip.querySelector('.edyes').addEventListener('click', function(e){
            fwdom.popover.dispose();
        });
        document.body.addEventListener('click', fwdom.outsideClick);
    });
 };