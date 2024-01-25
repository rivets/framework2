/* globals document, fwdom, bootstrap, framework, console, window, tinymce */
/* jshint undef: true, unused: false */

var fweditable = {
    popover : null,

    inline  : null,

    domid   : -1,
    
    taid    : '',

    edOptions: [],

    emptyText : '--------',
    
    emptyTiny: /<p><br[^>]*><\/p>/, // the text returned by an empty Tiny MCE editor

    makeEdit: function(d)
    {
        const options = fweditable.edOptions[d.getAttribute('data-editable-id')];
        let ctext = d.innerHTML;
        let box;
        fweditable.taid = '';
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
                    let vlu = opt.value;
                    if (vlu instanceof String && vlu.includes('"'))
                    {
                        vlu = vlu.replace(/"/, '&quot;');
                    }
                    box += '<option value="'+vlu+'"'+(opt.text == ctext ? ' selected' : '')+'>'+opt.text+'</option>';
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
        case 'text':
            if (ctext.includes('"'))
            {
                ctext = ctext.replace(/"/, '&quot;');
            }
            if (ctext.includes('&'))
            {
                ctext = ctext.replace(/"/, '&amp;');
            }
            box = '<form id="edboxfrm"><input type="'+options.type+'" value="' + ctext + '" class="edbox"/></form>';
            break;
        case 'html' :
            fweditable.taid = 'hta' + fweditable.domid;
            fweditable.domid += 1;
            box = '<textarea rows="5" cols="80" class="edbox" id="'+fweditable.taid+'">' + ctext + '</textarea>';
            break;
        default:
            if (ctext.includes('"'))
            {
                ctext = ctext.replace(/"/, '&quot;');
            }
            if (ctext.includes('&'))
            {
                ctext = ctext.replace(/"/, '&amp;');
            }
            box = '<input type="'+options.type+'" value="' + ctext + '" class="edbox"/>';
            break;
        }
        return box + '<i class="fad fa-times-circle edno"></i><i class="fad fa-check-circle edyes"></i>';
    },

    popDispose : function()
    {
       document.body.removeEventListener('click', fweditable.outsideClick);
       //document.querySelector('.popover-background').removeClass('visible');
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
            //console.log(e.target.classList.contains('tox-dialog-wrap__backdrop'));
            if (e.target.closest('div[class~="tox-dialog"]') != null || e.target.classList.contains('tox-dialog-wrap__backdrop'))
            {
                fwdom.stop(e);
            }
            else
            {
                //console.log(e.target, e.target.closest('div[class~="tox-dialog"]'), e.target.classList, e.target.classList.contains('tox-dialog-wrap__backdrop'));
                fweditable.popDispose(e);
            }
       }
    },

    editUpdate : function(options, value) {
        return framework.ajax(framework.buildFWLink('ajax', options.op, options.bean, options.key, options.field), {
            method: framework.putorpatch,
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
        div.addEventListener('shown.bs.popover', function(e){
            fwdom.stop(e);
            const tsta = document.querySelector('.popover').querySelector('.edbox');
            tsta.focus({preventScroll: true});
            if (tsta.nodeName != 'SELECT')
            {
                tsta.setSelectionRange(100000, 100000);
            }
            if (fweditable.taid !== '')
            {
                tinymce.init({selector: 'textarea#'+fweditable.taid,
                    branding: false,
                    toolbar : 'undo redo | bold italic | superscript subscript | bullist numlist | link | code | charmap',
                    menubar : false,
                    plugins: 'lists link code charmap'
                });
            }
            //document.querySelector('.popover-background').addClass('visible');
        });
        tip.querySelector('.edno').addEventListener('click', fweditable.popDispose);
        const tickFn = function(e){
            fwdom.stop(e);
            let options =  fweditable.edOptions[fweditable.inline.getAttribute('data-editable-id')];

            let box = tip.querySelector('.edbox');
            if (fweditable.taid !== '')
            {
                box.value = tinymce.activeEditor.getContent({format : 'raw'});
                if (box.value.match(fweditable.emptyTiny))
                {
                    box.value = '';
                }
                else
                {
                    box.value.replace(/<br[^>*]><\/p>/i, '</p>'); // get rid of spurious breaks
                }
            }

            if (box.value != fweditable.inline.innerText)
            {
                if (options.update == null)
                {
                    framework.alert('No update function defined');
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
                        framework.alert('Update failed');
                    });
                }
            }
            fweditable.popDispose();
        };
        tip.querySelector('.edyes').addEventListener('click', tickFn);
        if (type == 'text')
        {
            document.getElementById('edboxfrm').addEventListener('submit', tickFn);
        }
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
                    framework.alert('Cannot fetch list');
                }, false);
            }
            else
            {
                fweditable.popClick(div);
            }
        });
    }
 };
