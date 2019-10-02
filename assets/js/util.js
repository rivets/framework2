    var framework = {
/**
 * Generate HTML for a font-awesome toggle icon
 *
 * @param {string} tclass - class (or classes) to be added to this toggle
 * @param {bool} v - if true then toggle is on otherwise off
 *
 * @return {string}
 */
        mktoggle: function (tclass, v)
        {
            return '<i class="'+tclass+' fas fa-toggle-'+(v ? 'on' : 'off')+'"></i>';
        },
/**
 * Use mktoggle to generate a toggle without any extra classes.
 *
 * @param {bool} v - if true then toggle is on otherwise off
 *
 * @return {string}
 */
        tick: function(v)
        {
            return framework.mktoggle('', v);
        },
/**
 * Turn a toggle from on to off or vice-versa
 *
 * @param {object} x - a jQuery object
 *
 * @return {void}
 */
        toggle: function(x)
        {
            x.toggleClass('fa-toggle-off').toggleClass('fa-toggle-on');
        },
/**
 * Send a toggle operation to the server using AJAX to toggle a given field in a bean.
 * The id of the bean is taken from the data-id field of the parent of the parent of the toggle
 * which is assumed to be in a td in a tr.
 *
 * @todo generalise the position of the data-id field
 *
 * @param {object} e - a jQuery event
 * @param {object} x - a jQuery object
 * @param {string} bean - a RedBean bean name
 * @param {string} fld - the name of the field in the bean to toggle
 *
 * @return {void}
 */
        dotoggle: function(e, x, bean, fld)
        {
            e.preventDefault();
            e.stopPropagation();
            if (!x.hasClass('fadis'))
            {
                if (x.hasClass('htick'))
                { // this is not yet created so tick the hidden box
                    const n = x.next();
                    n.val(n.val() == 1 ? 0 : 1);
                    framework.toggle(x);
                }
                else
                { // toggle at the other end
                    const tr = x.parent().parent();
                    $.ajax(base+'/ajax/toggle/'+bean+'/'+tr.data('id')+'/'+'/'+fld, {
                        method: putorpatch,
                    }).done(function(){
                       framework.toggle(x);
                    }).fail(function(jx){
                        bootbox.alert('<h3>Toggle failed</h3>'+jx.responseText);
                    });
                }
            }
        },
/**
 * Ask user if they want to delete a bean and if so, send an AJAX call to the server to do the deletion
 *
 * @param {object} e - a jQuery event
 * @param {object} x - a jQuery object
 * @param {string} bean - a RedBean bean name
 * @param {int} id - the id of the bean to be deleted
 * @param {function} yes - the function to call on success
 * @param {string} msg - included in part of the "do you want to delete" prompt
 * 
 * @return {void}
 */
        deletebean: function(e, x, bean, id, yes, msg = '')
        {
            e.preventDefault();
            e.stopPropagation();
            if (msg == '')
            {
                msg = 'this '+bean;
            }
            bootbox.confirm('Are you sure you you want to delete '+msg+'?', function(r){
                if (r)
                { // user picked OK
                    $.ajax(base+'/ajax/bean/'+bean+'/'+id+'/', {
                        method: 'DELETE',
                    }).done(yes).fail(function(jx){
                        bootbox.alert('<h3>Delete failed</h3>'+jx.responseText);
                    });
                }
            });
        },
/**
 * AJAX caller used by the code supporting in place editing
 *
 * @param {object} params - an object containing various parameters controlling the operation
 *
 * @return {void}
 */
        editcall: function(params) {
            const url = base + '/ajax/' + params.op + '/' + params.bean + '/' + params.pk + '/'+params.name+'/';
            return $.ajax(url, {
                method: putorpatch,
                data: { value: params.value }
            });
        },
/**
 * Turn background of the jQuery dom object yellow and then fade to white.
 *
 * @param {object} tr - a jQuery dom object
 *
 * @return void
 */
        fadetodel: function(tr){
               tr.css('background-color', 'yellow').fadeOut(1500, function(){ tr.remove(); });
        },
/**
 * Use deletebean to ask user and possibly delete a bean.
 * Provides a yes function and gets the id value.
 *
 * @see dotoggle above for info about data-id usage
 *
 * @param {object} e - a jQuery event
 * @param {object} x - a jQuery object
 * @param {string} bean - a RedBean bean name
 * @param {string} msg - included in part of the "do you want to delete" prompt
 *
 * @return {void}
 */
        dodelbean: function(e, x, bean, msg = '')
        {
            let tr = $(x).parent().parent();
            framework.deletebean(e, x, bean, tr.data('id'), function(){framework.fadetodel(tr);}, msg);
        },
/**
 * When a table detects a click call this. Expects there to be an
 * field in the event data called clicks which is an array of 3 element arrays
 * containing a classname, a function, and a paaramter to pass  to the function.
 * If the item within the table that was clicked has the class name then the function is called.
 *
 * @param {object} event - a jQuery event object
 *
 * @return void
 */
        tableClick: function(event)
        {
            event.preventDefault();
            const x = $(event.target);
            event.data.clicks.forEach(function(value){
                let [cls, fn, par] = value;
                if (x.hasClass(cls))
                {
                    fn(event, x, event.data.bean, par);
                }
            });
        },
/**
 * Relocate to an admin edit URL - used by the framework admin interface
 *
 * @see toggle above for info on use of data-id field
 *
 * @param {object} e - a jQuery event
 * @param {object} x - a jQuery dom object
 * @param {string} t - a RedBean bean name
 *
 * @return void
 */
        goedit: function(e, x, t)
        {
            window.location.href = base+'/admin/edit/'+t+'/' + x.parent().parent().data('id') + '/';
        },
/**
 * Relocate to an admin view URL - used by the framework admin interface
 *
 * @see toggle above for info on use of data-id field
 *
 * @param {object} e - a jQuery event
 * @param {object} x - a jQuery dom object
 * @param {string} t - a RedBean bean name
 *
 * @return void
 */
        goview: function(e, x, t)
        {
            window.location.href = base+'/admin/view/'+t+'/' + x.parent().parent().data('id') + '/';
        },
/**
 * Use AJAX to create a new RedBean bean.
 * This always re-enables the button that was clicked to get here.
 *
 * @param {string} bean - a bean name
 * @param {object} data - data to pass to the bean creation: the fields to set
 * @param {function} fn - called on success
 * @param {string} button - the id attribute value for the button that was used to initiate the operation
 *
 * @return void
 */
        beanCreate: function(bean, data, fn, button)
        {
            $.post(base+'/ajax/bean/'+bean+'/', data).done(fn).fail(function(jx){
                bootbox.alert('<h3>Failed to create new '+bean+'</h3>'+jx.responseText);
            }).always(function(){
                $(button).attr('disabled', false);
            });
        },
/**
 * Duplicate the #example item of a form. Allows users to send more data if they need to.
 *
 * @param {object} e - a jQuery event
 *
 * @return void
 */
        addMore: function(e)
        {
            e.preventDefault();
            $('#mrow').before($('#example').clone());
            $('input,textarea', $('#mrow').prev()).val(''); // clear the new inputs
            $('option', $('#mrow').prev()).prop('selected', false); // clear any selections
        },
/**
 * Used by doBGFade to calculate the next value in a progressive fade
 *
 * @return float
 */
        easeInOut: function(minValue, maxValue, totalSteps, actualStep, powr)
        {
            return Math.ceil(minValue + (Math.pow(((1 / totalSteps) * actualStep), powr) * (maxValue - minValue)));
        },
/**
 * Fade the background colour of an element from one colcour to another.
 * Used to create the "fade to yellow" effect that the Framework uses to show when
 * something has changed.
 *
 * @param {object} elem - a jQuery dom element
 * @param {string} startRGB - the RGB colour to start with
 * @param {string} endRGB - the RGB colour to end with
 * @param {int} steps - now many steps to take
 * @param {int} interval - the time interval between steps in millisecs
 * @param {float} powr - used in easeInOut
 *
 * @return void
 */
        doBGFade: function(elem, startRGB, endRGB, finalColor, steps, intervals, powr)
        {
            if (elem.bgFadeInt)
            {
                window.clearInterval(elem.bgFadeInt);
            }

            let actStep = 0;
            elem.bgFadeInt = window.setInterval(
                function() {
                    elem.css('backgroundcolor', 'rgb(' +
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
