    var framework = {
        dejq: function(el) {
            console.log(el);
        },
/**
 * encode object into a query string
 *
 * @param {object} data   - the object to encode
 */
        makeQString: function(data) {
            var enc = '';
            let amp = '';
            for (var prop in data)
            {
                if (data.hasOwnProperty(prop))
                {
                    enc += amp + encodeURI(prop + '=' + data[prop]);
                    amp = '&';
                }
            }
            return enc;
        },

        onloaded: function(rq, options){
            if (rq.status >= 200 && rq.status < 400)
            { // Success!
                if (options.hasOwnProperty('success'))
                {
                    options.success(rq.response);
                }
            }
            else if (options.hasOwnProperty('fail'))
            { // something went wrong
                options.fail(rq.response);
            }
            if (options.hasOwnProperty('always'))
            { // always do this
                options.always(rq.response);
            }
        },
        onfailed: function(rq, options){
            // There was a connection error of some sort
              if (options.hasOwnProperty('fail'))
              {
                  options.fail(rq.response);
              }
              if (options.hasOwnProperty('always'))
              {
                  options.always(rq.response);
              }
        },
/**
 * non-jquery post function
 *
 * @param {string} op     - the operation to use
 * @param {string} url    - the URL to invoke
 * @param {object} data   - the data to pass
 */
        ajax: function (url, options) {
            let request = new XMLHttpRequest();
            let method = options.hasOwnProperty('method') ? options.method : 'GET';
            let data = options.hasOwnProperty('data') ? framework.makeQString(options.data) : '';
            let type = options.hasOwnProperty('type') ? options.type : (data !== '' ? 'application/x-www-form-urlencoded; charset=UTF-8' : 'text/plain; charset=UTF-8');
            request.open(method, url, true);
            request.setRequestHeader('Content-Type', type);
            request.onload = function() {
                framework.onloaded(this, options);
            };
            request.onerror = function() {
                framework.onfailed(this, options);
            };
            request.send(data);
        },
/**
 * get JSON
 */
        getJSON : function(url, success, fail){
            var request = new XMLHttpRequest();
            request.open('GET', url, true);
            request.onload = function() {
              if (this.status >= 200 && this.status < 400) {
                // Success!
                success(JSON.parse(this.response));

              } else {
                // We reached our target server, but it returned an error
                fail(this);
              }
            };
            request.onerror = function() {
              // There was a connection error of some sort
              fail(this);
            };
            request.send();
        },
/**
 * Generate HTML for a font-awesome toggle icon
 *
 * @param {string} tclass - class (or classes) to be added to this toggle
 * @param {bool}   v      - if true then toggle is on otherwise off
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
 * @param {object} x - a dom object
 *
 * @return {void}
 */
        toggle: function(x)
        {
            x.classList.toggle('fa-toggle-off');
            x.classList.toggle('fa-toggle-on');
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
            let cl = x.classList;
            if (!cl.contains('fadis'))
            {
                if (cl.contains('htick'))
                { // this is not yet created so tick the hidden box
                    const n = x.nextElementSibling;
                    n.value = n.value == 1 ? 0 : 1;
                    framework.toggle(x);
                }
                else
                { // toggle at the other end
                    //$.ajax(base+'/ajax/toggle/'+bean+'/'+pnode.getAttribute('data-id')+'/'+fld, {
                    //    method: putorpatch,
                    //}).done(function(){
                    //   framework.toggle(x);
                    //}).fail(function(jx){
                    //    bootbox.alert('<h3>Toggle failed</h3>'+jx.responseText);
                    //});
                    let pnode = x.closest('[data-id]');
                    if (pnode instanceof jQuery)
                    {
                        pnode = pnode[0];
                    }
                    framework.ajax(base+'/ajax/toggle/'+bean+'/'+pnode.getAttribute('data-id')+'/'+fld, {
                        method: putorpatch,
                        success: function(){ framework.toggle(x); },
                        fail: function(jx) { bootbox.alert('<h3>Toggle failed</h3>'+jx.responseText); }
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
            if (msg === '')
            {
                msg = 'this '+bean;
            }
            bootbox.confirm('Are you sure you you want to delete '+msg+'?', function(r){
                if (r)
                { // user picked OK
                    framework.ajax(base+'/ajax/bean/'+bean+'/'+id+'/', {
                        method: 'DELETE',
                        success: yes,
                        fail : function(jx){ bootbox.alert('<h3>Delete failed</h3>'+jx.responseText); },
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
 * Remove a node. If it has a rowspan then remove the next rows as well
 *
 * @param {object} tr - a dom object
 *
 * @return void
 */
        removeNode: function(node){
            var nodes = [node];
            if (node.hasAttribute('rowspan'))
            {
                let rs = parseInt(node.getAttribute('rowspan'))-1;
                while (rs > 0)
                {
                    nodes[rs] = nodes[rs-1].elementSibling;
                }
            }
            for (let x of nodes)
            {
                x.parentNode.removeChild(x);
            }
        },
/**
 * Turn background of the dom object yellow and then fade to white.
 *
 * @param {object} tr - a  dom object
 * @param {?function} atend - a function called after fade finishes or null
 *
 * @return void
 */
        fadetodel: function(tr, atend = null){
            tr.classList.add('fader');
            tr.style.opacity = '0';
            setTimeout(function(){
                framework.removeNode(tr);
                if (atend !== null)
                {
                    atend();
                }
            }, 1500);
        },
/**
 * Use deletebean to ask user and possibly delete a bean.
 * Provides a yes function and gets the id value.
 *
 * This function assumes that the delete button is embedded in a td inside a tr and
 * that the whole tr is to be removed from the screen.
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
        dodelbean: function(e, x, bean, msg = '', success = null)
        {
            let pnode = x.closest('[data-id]');
            if (pnode instanceof jQuery)
            {
                pnode = pnode[0];
            }
            framework.deletebean(e, x, bean, pnode.getAttribute('data-id'), function(){ framework.fadetodel(pnode, success);}, msg);
        },
/**
 * When a table detects a click call this. Expects there to be an
 * field in the event data called clicks which is an array of 3 element arrays
 * containing a classname, a function, and a paramter to pass  to the function.
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
                    fn(event, event.target, event.data.bean, par);
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
            let pnode = x.closest('[data-id]');
            if (pnode instanceof jQuery)
            {
                pnode = pnode[0];
            }
            window.location.href = base+'/admin/edit/'+t+'/' + pnode.getAttribute('data-id') + '/';
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
