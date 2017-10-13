    function mktoggle(tclass, v)
    {
        return '<i class="'+tclass+' fa fa-toggle-'+(v ? 'on' : 'off')+'"></i>';
    }

    function tick(v)
    {
        return mktoggle('', v);
    }

    function toggle(x)
    {
        if (x.hasClass('fa-toggle-off'))
        {
           x.removeClass('fa-toggle-off');
           x.addClass('fa-toggle-on');
        }
        else
        {
           x.removeClass('fa-toggle-on');
           x.addClass('fa-toggle-off');
        }
    }

    function dotoggle(e, x, bean, fld)
    {
        e.preventDefault();
        e.stopPropagation();
        if (x.hasClass('htick'))
        { // this is not yet created so tick the hidden box
            var n = x.next();
            n.val(n.val() == 1 ? 0 : 1);
            toggle(x);
        }
        else
        { // toggle at the other end
            var tr = x.parent().parent();
            $.post(base+'/ajax.php', {
                op : 'toggle',
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
                $.post(base+'/ajax.php', {
                    op :'delbean',
                    'bean' : bean,
                    id : tr.data('id')
                    },
                    function(data){
                        tr.css('background-color', 'yellow').fadeOut(1500, function(){ tr.remove(); });
                    }
                );
            }
        });
    }
    
    function mkinline(type, name, msg, id, value)
    {
        return '<a href="#" class="ppedit" data-name="'+name+'" data-type="'+type+'" data-pk="'+id+'" data-url="'+base+'/ajax.php" data-title="'+msg+'">'+value+'</a>';
    }
