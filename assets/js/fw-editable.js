/* globals document, fwdom, bootstrap */
/* jshint undef: true, unused: false */

fwdom.popover = null;
fwdom.inline = null;
fwdom.edOptions = [];
fwdom.defaultOptions = {
    type: 'text',
    emptytext: '------',
    title: 'Edit String',
    update: null
}

fwdom.makeEdit = function(d)
 {
    const options = fwdom.edOptions[d];
console.log(options, fwdom.edOptions);
    let ctext = d.innerText;
    let box;
    if (ctext === options.emptyText)
    {
        ctext = '';
    }
    switch (options.type)
    {
    case 'select':
        box = '<select class="edbox">';
        for (let opt of options.source)
        {
            box += '<option'+(opt == ctext ? ' selected' : '')+'>'+opt+'</option>';
        }
        box += '</select>';
        break;
    case 'textarea':
        box = '<textarea rows="5" cols="25" class="edbox">' + ctext + '</textarea>';
        break;
    default:
        box = '<input type="'+options.type+'" value="' + ctext + '" class="edbox"/>';
        break;
    }
    return box + '<i class="fas fa-times-circle edno"></i><i class="fas fa-check-circle edyes"></i>';
 };

 fwdom.popDispose = function()
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

 fwdom.editable = function(div, options = null) {
    if (options != null)
    {
        fwdom.edOptions[div] = options;
    }
    else
    {
        let nopt = fwdom.defaultOptions;
        let datas = div.dataset;
        for (let fld in datas)
        {
            nopt[fld] = datas[fld];
        }
        fwdom.edOptions[div] = nopt;
    }
    if (div.innerText === '')
    {
        div.innerText = div.getAttribute('data-emptytext');
        div.classList.add('edempty');
    }
    div.addEventListener('click', function(e){
        fwdom.stop(e);
        if (fwdom.inline !== null)
        {
            fwdom.popDispose();
        }
        let popover = new bootstrap.Popover(div, {
            title: fwdom.edOptions[this].title, //this.getAttribute('data-title'),
            html: true,
            sanitize: false,
            content: fwdom.makeEdit(this),
            placement: 'auto',
            template: '<div class="popover" role="tooltip"><div class="popover-arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>'
        });
        popover.show();
        let tip = popover.tip;


        tip.querySelector('.edno').addEventListener('click', fwdom.popDispose);
        tip.querySelector('.edyes').addEventListener('click', function(e){
            fwdom.stop(e);
            let box = tip.querySelector('.edbox');
            if (box.value != fwdom.inline.innerText)
            {
                if (box.value === '')
                {
                   fwdom.inline.innerText = fwdom.edOptions[fwdom.inline].emptytext;
                   fwdom.inline.classList.add('edempty');
                }
                else
                {
                   fwdom.inline.innerText = box.value;
                   fwdom.inline.classList.remove('edempty');
                }
                if (options.update == null)
                {
                    fwdom.alert('No update function defined');
                    return;
                }
                options.update(options, box.value);
            }
            fwdom.popDispose();
        });
        document.body.addEventListener('click', fwdom.outsideClick);
        fwdom.popover = popover;
        fwdom.inline = div;
    });
 };