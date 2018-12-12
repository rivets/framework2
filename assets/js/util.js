    function mktoggle(tclass, v)
    {
        return '<i class="'+tclass+' fas fa-toggle-'+(v ? 'on' : 'off')+'"></i>';
    }

    function tick(v)
    {
        return mktoggle('', v);
    }

    function toggle(x)
    {
        x.toggleClass('fa-toggle-off').toggleClass('fa-toggle-on');
    }

    function dotoggle(e, x, bean, fld)
    {
        e.preventDefault();
        e.stopPropagation();
        if (x.hasClass('fadis'))
        {
            return;
        }
        if (x.hasClass('htick'))
        { // this is not yet created so tick the hidden box
            var n = x.next();
            n.val(n.val() == 1 ? 0 : 1);
            toggle(x);
        }
        else
        { // toggle at the other end
            var tr = x.parent().parent();
            $.post(base+'/ajax/toggle/'+bean+'/'+tr.data('id')+'/'+fld, {}).done(function(data){ toggle(x);}).
                fail(function(jx){
                    bootbox.alert('<h3>Toggle failed</h3>'+jx.responseText);
                });
        }
    }

    function dodelbean(e, x, bean)
    {
        e.preventDefault();
        e.stopPropagation();
        bootbox.confirm('Are you sure you you want to delete this '+bean+'?', function(r){
            if (r)
            { // user picked OK
                var tr = $(x).parent().parent();
                $.ajax(base+'/ajax/bean/'+bean+'/'+tr.data('id')+'/', {
                    method: 'DELETE',
                }).done(function(){
                    tr.css('background-color', 'yellow').fadeOut(1500, function(){ tr.remove(); });
                }).fail(function(jx){
                    bootbox.alert('<h3>Delete failed</h3>'+jx.responseText);
                });
            }
        });
    }


