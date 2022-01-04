/* globals document, fwdom, bootstrap, framework, putorpatch, console */
/* jshint undef: true, unused: false */

var fweditable = {
    popover : null,

    inline  : null,

    domid   : -1,

    edOptions: [],

    emptyText : '--------',

    makeEdit: function(d)
    {
       const options = fweditable.edOptions[d.getAttribute('data-editable-id')];
       let ctext = d.innerHTML;
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
    },

    popDispose : function()
    {
       document.body.removeEventListener('click', fweditable.outsideClick);
       if (fweditable.popover !== null)
       {
           fweditable.popover.dispose();
           fweditable.popover = null;
       }
    },

    outsideClick : function(e)
    {
       if (fweditable.inline != e.target && fweditable.inline != fweditable.popover.tip && !fweditable.popover.tip.contains(e.target))
       {
           fweditable.popDispose(e);
       }
    },

    editUpdate : function(options, value) {
        return framework.ajax(framework.buildFWLink('ajax', options.op, options.bean, options.key, options.field), {
            method: putorpatch,
            data: { value: value }
        });
    },

    popClick : function(div)
    {
        if (div.classList.contains('disabled'))
        {
            return;
        }
        const options = fweditable.edOptions[div.getAttribute('data-editable-id')];
        const type = options.type;
        const title = options.title;
        if (fweditable.inline !== null)
        {
            fweditable.popDispose();
        }
        let popover = new bootstrap.Popover(div, {
            title: title,
            container: 'body',
            html: true,
            sanitize: false,
            content: fweditable.makeEdit(div),
            placement: 'auto',
            template: '<div class="popover pop'+type+'" role="tooltip"><div class="popover-arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>'
        });
        popover.show();
        let tip = popover.tip;
        tip.querySelector('.edno').addEventListener('click', fweditable.popDispose);
        tip.querySelector('.edyes').addEventListener('click', function(e){
            fwdom.stop(e);
            let options =  fweditable.edOptions[fweditable.inline.getAttribute('data-editable-id')];
            let box = tip.querySelector('.edbox');
            if (box.value != fweditable.inline.innerHTML)
            {
                if (options.update == null)
                {
                    fwdom.alert('No update function defined');
                }
                else
                {
                    options.update(options, box.value).done(function(){
                        if (options.type == 'select')
                        {
                            for (let x of options.source)
                            {
                                if (typeof x == 'object')
                                {
                                    if (box.value == x.value)
                                    {
                                        fweditable.inline.innerText = x.text;
                                        break;
                                    }
                                }
                                else if (box.value == x)
                                {
                                    fweditable.inline.innerText = x;
                                    break;
                                }
                            }
                        }
                        else if (box.value === '')
                        { // empty string so indicate this
                           fweditable.inline.innerText = options.emptyText;
                           fweditable.inline.classList.add('edempty');
                        }
                        else
                        { // not empty
                           fweditable.inline.innerText = box.value;
                           fweditable.inline.classList.remove('edempty');
                        }
                    }).fail(function(jx){
                        fwdom.alert('Update failed');
                    });
                }
            }
            fweditable.popDispose();
        });
        document.body.addEventListener('click', fweditable.outsideClick);
        fweditable.popover = popover;
        fweditable.inline = div;
    },

    editable : function(div, options = null) {
        let nopt = {
            type: 'text',
            emptyText: fweditable.emptyText,
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
        fweditable.domid += 1;
        fweditable.edOptions[fweditable.domid] = nopt;
        div.setAttribute('data-editable-id', fweditable.domid);
        if (div.innerHTML === '')
        {
            div.innerHTML = nopt.emptyText;
            div.classList.add('edempty');
        }
        div.addEventListener('click', function(e){
            fwdom.stop(e);
            const domid = this.getAttribute('data-editable-id');
            const options = fweditable.edOptions[domid];
            const div = this;
            if (typeof options.source == 'string')
            {
                framework.getJSON(options.source, function(data){
                   fweditable.edOptions[domid].source = data;
                   fweditable.popClick(div);
                }, function(jx){
                    fwdom.alert('Cannot fetch list');
                }, false);
            }
            else
            {
                fweditable.popClick(div);
            }
        });
    }
 };