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
            $.post(base+'/ajax/toggle/', {
                field : fld,
                bean : bean,
                id : tr.data('id')
            }, function(data){
                toggle(x);
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
                }).fail(function(){
                    bootbox.alert('Delete failed');
                });
            }
        });
    }

    function editcall(params)
    {
        var url = base+'/ajax/bean/'+params.bean+'/'+params.pk+'/'+params.name+'/';
        return $.ajax(url,{
            method: 'PATCH',
            data: {
                value: params.value
            }
        });
    }

    function mkinline(type, name, msg, id, value)
    {
        return '<a href="#" class="ppedit" data-name="'+name+'" data-type="'+type+'" data-pk="'+id+'" data-url="'+base+'/ajax.php" data-title="'+msg+'">'+value+'</a>';
    }
