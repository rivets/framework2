/* globals document, fwdom, bootstrap, framework */
/* jshint undef: true, unused: false */

fwdom.popover = null;
fwdom.inline = null;
fwdom.domid = -1;
fwdom.edOptions = [];

fwdom.makeEdit = function(d)
 {
    console.log(this);
    const options = fwdom.edOptions[d.getAttribute('data-editable-id')];
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

        if (typeof options.source == 'function')
        {
            options.source = options.source();
        }
        for (let opt of options.source)
        {
            if (typeof opt == 'object')
            {
                box += '<option value="'+opt.value+'"'+(opt.text == ctext ? ' selected' : '')+'>'+opt.text+'</option>';
            }
            else
            {
                box += '<option'+(opt == ctext ? ' selected' : '')+'>'+opt+'</option>';
            }
        }
        box += '</select>';
        break;
    case 'textarea':
        box = '<textarea rows="5" cols="80" class="edbox">' + ctext + '</textarea>';
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

fwdom.editUpdate = function(options, value) {
    return framework.ajax(framework.buildFWLink('ajax', options.op, options.bean, options.key, options.field), {
        method: putorpatch,
        data: { value: value }
    });
};

fwdom.popClick = function(div){
    if (div.hasAttribute('disabled'))
    {
        return;
    }
    const options = fwdom.edOptions[div.getAttribute('data-editable-id')];
    console.log(options);
    const type = options.type;
    const title = options.title;
    if (fwdom.inline !== null)
    {
        fwdom.popDispose();
    }
    let popover = new bootstrap.Popover(div, {
        title: title,
        container: 'body',
        html: true,
        sanitize: false,
        content: fwdom.makeEdit(div),
        placement: 'auto',
        template: '<div class="popover pop'+type+'" role="tooltip"><div class="popover-arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>'
    });
    popover.show();
    let tip = popover.tip;
    tip.querySelector('.edno').addEventListener('click', fwdom.popDispose);
    tip.querySelector('.edyes').addEventListener('click', function(e){
        fwdom.stop(e);
        let options =  fwdom.edOptions[fwdom.inline.getAttribute('data-editable-id')];
        let box = tip.querySelector('.edbox');
        if (box.value != fwdom.inline.innerText)
        {
            if (options.update == null)
            {
                fwdom.alert('No update function defined');
            }
            else
            {
                options.update(options, box.value).done(function(res){
                    console.log(res);
                    if (box.value === '')
                    {
                       fwdom.inline.innerText = options.emptytext;
                       fwdom.inline.classList.add('edempty');
                    }
                    else
                    {
                       fwdom.inline.innerText = box.value;
                       fwdom.inline.classList.remove('edempty');
                    }
                }).fail(function(jx){
                    console.log(jx);
                    fwdom.alert('Update failed');
                });
            }
        }
        fwdom.popDispose();
    });
    document.body.addEventListener('click', fwdom.outsideClick);
    fwdom.popover = popover;
    fwdom.inline = div;
};

fwdom.editable = function(div, options = null) {
    let nopt = {
        type: 'text',
        emptyText: '------',
        title: 'Edit String',
        update: null
    };
    if (options != null)
    {
        for (let fld in options)
        {
            nopt[fld] = options[fld];
        }
    }
    let datas = div.dataset;
    for (let fld in datas)
    {
        nopt[fld] = datas[fld];
    }
    fwdom.domid += 1;
    fwdom.edOptions[fwdom.domid] = nopt;
    div.setAttribute('data-editable-id', fwdom.domid);
    if (div.innerText === '')
    {
        div.innerText = nopt.emptyText;
        div.classList.add('edempty');
    }
    div.addEventListener('click', function(e){
        fwdom.stop(e);
        const domid = this.getAttribute('data-editable-id');
        const options = fwdom.edOptions[domid];
        const div = this;
        if (typeof options.source == 'string')
        {
            framework.getJSON(options.source, function(data){
               fwdom.edOptions[domid].source = data;
               fwdom.popClick(div);
            }, function(jx){
                fwdom.alert('Cannot fetch list');
            }, false);
        }
        else
        {
            fwdom.popClick(div);
        }
    });
 };