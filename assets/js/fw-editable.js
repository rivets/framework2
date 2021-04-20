/* globals document, fwdom, bootstrap */
/* jshint undef: true, unused: false */

fwdom.popover = null;
fwdom.inline = null;

fwdom.makeEdit = function(d, type)
 {
    let box = '';
    let text = d.innerText;
    if (text === '')
    {
        text = d.getAttribute('data-emptytext');
    }
    switch (type)
    {
    case 'select':
        box = '<span>Not supported yet<span>';
        break;
    case 'textarea':
        box = '<textarea rows="5" cols="25" class="edbox">' + text + '"</textarea>';
        break;
    default:
        box = '<input type="'+type+'" value="' + text + '" class="edbox"/>';
        break;
    }
    return box + '<i class="fas fa-times-circle edno"></i><i class="fas fa-check-circle edyes"></i>';
 };

 fwdom.popDispose = function(e)
 {
    document.body.removeEventListener('click', fwdom.outsideClick);
    if (fwdom.popover !== null)
    {
        fwdom.popover.dispose();
        fwdom.popover = null;
    }
 };

 fwdom.outsideClick = function(e)
 {
    if (fwdom.inline != e.target && fwdom.inline != fwdom.popover.tip && !fwdom.popover.tip.contains(e.target))
    {
        fwdom.popDispose(e);
    }
 };

 fwdom.editable = function(div) {
    let name = div.getAttribute('name');
    let type = div.getAttribute('data-type');
    div.addEventListener('click', function(e){
        if (fwdom.inline !== null)
        {
            fwdom.popDispose();
        }
        let popover = new bootstrap.Popover(div, {
            title: this.getAttribute('data-title'),
            html: true,
            sanitize: false,
            content: fwdom.makeEdit(this, type),
            placement: 'auto',
            template: '<div class="popover" role="tooltip"><div class="popover-arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>'
        });
        popover.show();
        let tip = popover.tip;


        tip.querySelector('.edno').addEventListener('click', fwdom.popDispose);
        tip.querySelector('.edyes').addEventListener('click', function(e){
            let box = tip.querySelector('.edbox');
            switch (type)
            {
            case 'select':
                if (box.innerText != fwdom.inline.innerText)
                {
                    fwdom.inline.innerText = box.innerText;
                    alert('update');
                }
                break;
            case 'textarea':
                break;
            default:
                if (box.value != fwdom.inline.innerText)
                {
                    alert('update');
                }
            }
            fwdom.popDispose();
        });
        document.body.addEventListener('click', fwdom.outsideClick);
        fwdom.popover = popover;
        fwdom.inline = div;
    });
 };