    var framework = {
        mktoggle: function (tclass, v)
        {
            return '<i class="'+tclass+' fas fa-toggle-'+(v ? 'on' : 'off')+'"></i>';
        },

        tick: function(v)
        {
            return framework.mktoggle('', v);
        },

        toggle: function(x)
        {
            x.toggleClass('fa-toggle-off').toggleClass('fa-toggle-on');
        },

        dotoggle: function(e, x, bean, fld)
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
                framework.toggle(x);
            }
            else
            { // toggle at the other end
                var tr = x.parent().parent();
                $.ajax(base+'/ajax/toggle/'+bean+'/'+tr.data('id')+'/'+'/'+fld, {
                    method: 'PATCH',
                }).done(function(){
                   framework.toggle(x);
                }).fail(function(jx){
                    bootbox.alert('<h3>Toggle failed</h3>'+jx.responseText);
                });
            }
        },

        dodelbean: function(e, x, bean)
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
        },

        tableClick: function(event)
        {
            event.preventDefault();
            var x = $(event.target);
            event.data.clicks.forEach(function(value){
                if (x.hasClass(value[0]))
                {
                    value[1](event, x, event.data.bean, value[2]);
                }
            });
        },

        goedit: function(e, x, t)
        {
            window.location.href = base+'/admin/edit/'+t+'/' + x.parent().parent().data('id') + '/';
        },

        goview: function(e, x, t)
        {
            window.location.href = base+'/admin/view/'+t+'/' + x.parent().parent().data('id') + '/';
        },

        beanCreate: function(bean, data, fn, button)
        {
            $.post(base+'/ajax/bean/'+bean+'/', data).done(fn).fail(function(jx){
                bootbox.alert('<h3>Failed to create new '+bean+'<h3>'+jx.responseText);
            }).always(function(){
                $(button).attr('disabled', false);
            });
        },

        addMore: function(e)
        {
            e.preventDefault();
            $('#mrow').before($('#example').clone());
            $('input,textarea', $('#mrow').prev()).val(''); // clear the new inputs
            $('option', $('#mrow').prev()).prop('selected', false); // clear any selections
        },

        easeInOut: function(minValue, maxValue, totalSteps, actualStep, powr)
        {
            return Math.ceil(minValue + (Math.pow(((1 / totalSteps) * actualStep), powr) * (maxValue - minValue)));
        },

        doBGFade: function(elem, startRGB, endRGB, finalColor, steps, intervals, powr)
        {
            if (elem.bgFadeInt)
            {
                window.clearInterval(elem.bgFadeInt);
            }
            var actStep = 0;
            elem.bgFadeInt = window.setInterval(
                function() {
                    elem.css('backgroundColor', 'rgb(' +
                        framework.easeInOut(startRGB[0], endRGB[0], steps, actStep, powr) + ',' +
                        framework.easeInOut(startRGB[1], endRGB[1], steps, actStep, powr) + ',' +
                        framework.easeInOut(startRGB[2], endRGB[2], steps, actStep, powr) + ')'
                    );
                    actStep += 1;
                    if (actStep > steps)
                            {
                        elem.css('backgroundcolor', finalColor);
                        window.clearInterval(elem.bgFadeInt);
                    }
                },
                intervals
            );
        },
    };



