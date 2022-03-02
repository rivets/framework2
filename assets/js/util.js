/* globals FormData*/
/* globals XMLHttpRequest, window, putorpatch, setTimeout, document, jQuery, bootstrap */
/* jshint undef: true, unused: false */
    class FWAjaxRQ
    {
        constructor(rq)
        {
            this.request = rq;
        }
/**
 * called when loaded
 */
        onloaded(){
            if (this.status >= 200 && this.status < 400)
            { // Success!
                let val = this.options.hasOwnProperty('accept') && this.options.accept == 'application/json' ? JSON.parse(this.response) : this.response;
                if (this.options.hasOwnProperty('success'))
                {
                    for (let fn of this.options.success)
                    {
                        val = fn(val, this);
                    }
                }
            }
            else if (this.options.hasOwnProperty('fail'))
            { // something went wrong
                for (let fn of this.options.fail)
                {
                    fn(this);
                }
            }
            if (this.options.hasOwnProperty('always'))
            { // always do this
                for (let fn of this.options.always)
                {
                    fn(this);
                }
            }
        }
/**
 * called when there is a send error
 */
        onfailed(){
            // There was a connection error of some sort
              if (this.options.hasOwnProperty('fail'))
              {
                for (let fn of this.options.fail)
                {
                    fn(this);
                }
              }
              if (this.options.hasOwnProperty('always'))
              {
                for (let fn of this.options.always)
                {
                    fn(this);
                }
              }
        }

        done(fn)
        {
            if (!this.request.options.hasOwnProperty('success'))
            {
                this.request.options.success = [];
            }
            this.request.options.success.push(fn);
            return this;
        }

        fail(fn)
        {
            if (!this.request.options.hasOwnProperty('fail'))
            {
                this.request.options.fail = [];
            }
            this.request.options.fail.push(fn);
            return this;
        }

        always(fn)
        {
            if (!this.request.options.hasOwnProperty('always'))
            {
                this.request.options.always = [];
            }
            this.request.options.always.push(fn);
            return this;
        }
    }

    var fwdom = {
        on: function(selector, op, func, parent = null) {
            (parent !== null ? parent : document).querySelectorAll(selector).forEach(function(d){
                d.addEventListener(op, func, false);
            });
        },

        data: function(element, tag) {
            const dv = 'data-'+tag;
            return element.closest('['+dv+']').getAttribute(dv);
        },

        stop: function(e) {
            e.preventDefault();
            e.stopPropagation();
        },

        toggleClass: function(elements, classes) {
            for (let el of elements)
            {
                for (let c of classes)
                {
                    el.classList.toggle(c);
                }
            }
        },

        mkjQ: function(sel){
            return jQuery(sel);
        },

        nosubmit: function(e) {
            fwdom.stop(e);
            return false;
        }
    };

    var framework = {
/**
 * The base directory value to add to all local URLs - set in the initialisation javascript
 */
        base : '',
 /**
  * Some routers do not support PUT so use PATCH - set in the initialisation javascript
  */
        putorpatch : 'PUT',
/**
 * any modal that is popped up
 */
        currentModal : null,
/**
 * encode object into a query string
 *
 * @param {object} data   - the object to encode
 */
        makeQString: function(data) {
            let enc = '';
            let amp = '';
            for (var prop in data)
            {
                if (data.hasOwnProperty(prop))
                {
                    enc += amp + prop + '=' + encodeURIComponent(data[prop]);
                    amp = '&';
                }
            }
            return enc;
        },
/*
 * non-jquery ajax function
 *
 * @param {string} url       - the URL to invoke
 * @param {object} options   - options specifying what to do
 */
        ajax: function (url, options) {
            let request = new XMLHttpRequest();
            let method = options.hasOwnProperty('method') ? options.method : 'GET';
            let accept = options.hasOwnProperty('accept') ? options.accept : '';
            let data = '';
            let dtype = 'text/plain; charset=UTF-8';
            if (options.hasOwnProperty('data'))
            {
                if (options.data instanceof FormData || typeof options.data !== 'object')
                {
                    data = options.data;
                    dtype = ''; // dont set content type for this
                }
                else
                {
                    data = framework.makeQString(options.data);
                    if (method.toUpperCase() == 'GET')
                    {
                        dtype = 'text/plain';
                        url += '?' + data;
                        data = '';
                    }
                    else
                    {
                        dtype = 'application/x-www-form-urlencoded; charset=UTF-8';
                    }
                }
            }
            let type = options.hasOwnProperty('type') ? options.type : dtype;
            request.options = options;
            request.open(method, url, options.hasOwnProperty('async') ? options.async : true);
            if (type !== '' && type != 'multipart/form-data')
            {
                request.setRequestHeader('Content-Type', type);
            }
            if (accept != '')
            {
                request.setRequestHeader('Accept', accept);
            }
            let ajaxObj = new FWAjaxRQ(request);
            request.onload = ajaxObj.onloaded;
            request.onerror = ajaxObj.onfailed;
            request.send(data);
            return ajaxObj;
        },
/**
 * get JSON
 */
        getJSON : function(url, success, fail){
            var request = new XMLHttpRequest();
            let ajaxObj = new FWAjaxRQ(request);
            request.open('GET', url, true);
            request.setRequestHeader('Accept', 'application/json');
            request.onload = function() {
                if (this.status >= 200 && this.status < 400)
                { // Success!
                    success(JSON.parse(this.response));
                }
                else
                { // We reached our target server, but it returned an error
                    fail(this);
                }
            };
            request.onerror = function() {
                // There was a connection error of some sort
                fail(this);
            };
            request.send();
            return ajaxObj;
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
            return '<i class="'+tclass+' fa-solid fa-toggle-'+(v ? 'on' : 'off')+'"></i>';
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
            fwdom.toggleClass([x], ['fa-toggle-off', 'fa-toggle-on']);
        },
/**
 * build a link from base
 *
 * @param (arguments)
 *
 * @return string
 */
        buildFWLink: function (){
            let link = framework.base;
            for (let item of arguments)
            {
                link += '/' + item;
            }
            return link + '/';
        },
/**
 * Send a toggle operation to the server using AJAX to toggle a given field in a bean.
 * The id of the bean is taken from the data-id field of the parent of the parent of the toggle
 * which is assumed to be in a td in a tr.
 *
 * @todo generalise the position of the data-id field
 *
 * @param {object} e - an event
 * @param {object} x - a dom object
 * @param {string} bean - a RedBean bean name
 * @param {string} fld - the name of the field in the bean to toggle
 *
 * @return {void}
 */
        dotoggle: function(e, x, bean, fld)
        {
            fwdom.stop(e);
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
                    let pnode = x.closest('[data-id]');
                    if (pnode instanceof jQuery)
                    {
                        pnode = pnode[0];
                    }
                    framework.ajax(framework.buildFWLink('ajax/toggle', bean, pnode.getAttribute('data-id'), fld), {method: framework.putorpatch})
                    .done(function(){ framework.toggle(x); }).fail(function(jx) { framework.alert('<h3>Toggle failed</h3>'+jx.responseText); });
                }
            }
        },
/**
 * Ask user if they want to delete a bean and if so, send an AJAX call to the server to do the deletion
 *
 * @param {object} e - an event
 * @param {object} x - a dom object
 * @param {string} bean - a RedBean bean name
 * @param {int} id - the id of the bean to be deleted
 * @param {function} yes - the function to call on success
 * @param {string} msg - included in part of the "do you want to delete" prompt
 *
 * @return {void}
 */
        deletebean: function(e, x, bean, id, yes, msg = '')
        {
            fwdom.stop(e);
            if (msg === '')
            {
                msg = 'this '+bean;
            }
            framework.confirm('Are you sure you you want to delete '+msg+'?', function(r){
                if (r)
                { // user picked OK
                    framework.ajax(framework.buildFWLink('ajax/bean', bean, id), {method: 'DELETE'})
                    .done(yes)
                    .fail(function(jx){ framework.alert('<h3>Delete failed</h3>'+jx.responseText); });
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
            return framework.ajax(framework.buildFWLink('ajax', params.op, params.bean, params.pk, params.name), {
                method: framework.putorpatch,
                data: { value: params.value }
            });
        },
/**
 * Remove a node. If it has a rowspan then remove the next rows as well
 *
 * @param {object} node - a dom object
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
 * @param {object} el - a dom object
 * @param {?function} atend - a function called after fade finishes or null
 *
 * @return void
 */
        fadetodel: function(el, atEnd = null){
            el.classList.add('fader');
            el.style.opacity = '0';
            setTimeout(function(){
                framework.removeNode(el);
                if (atEnd !== null)
                {
                    atEnd();
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
 * @param {object} e - an event
 * @param {object} x - a dom object
 * @param {string} bean - a RedBean bean name
 * @param {string} msg - included in part of the "do you want to delete" prompt
 *
 * @return {void}
 */
        dodelbean: function(e, x, bean, msg = '', success = null)
        {
            let pnode = x.closest('[data-id]');
            if (typeof jQuery != 'undefined' && pnode instanceof jQuery)
            {
                pnode = pnode[0];
            }
            framework.deletebean(e, x, bean, pnode.getAttribute('data-id'), function(){ framework.fadetodel(pnode, success);}, msg);
        },
/**
 * When a container detects a click call this. Expects there to be an
 * field in the event data called clicks which is an array of 3 element arrays
 * containing a classname, a function, and a paramter to pass  to the function.
 * If the item within the table that was clicked has the class name then the function is called.
 *
 * @param {object} event - an event object
 *
 * @return void
 */
        containerClick: function(event)
        {
            fwdom.stop(event);
            const clist = event.target.classList;
            event.data.clicks.forEach(function(value){
                let [cls, fn, par] = value;
                if (clist.contains(cls))
                {
                    fn(event, event.target, event.data.bean, par);
                }
            });
        },
/**
 * Relocate to link inside the framework with an id added in
 *
 * @see toggle above for info on use of data-id field
 *
 * @param {object} x     - a dom object
 * @param {string} t     - a RedBean bean name
 *
 * @return void
 */
        goFWLink: function(x, pre, t, post = '/')
        {
            let pnode = x.closest('[data-id]');
            if (pnode instanceof jQuery)
            {
                pnode = pnode[0];
            }
            window.location.href = framework.buildFWLink(pre, t, pnode.getAttribute('data-id'), post);
        },
/**
 * Relocate to an admin edit URL - used by the framework admin interface
 *
 * @see toggle above for info on use of data-id field
 *
 * @param {object} event - an event (not used - for compatibility when used with containerClick)
 * @param {object} x     - a dom object
 * @param {string} t     - a RedBean bean name
 *
 * @return void
 */
        goedit: function(event, x, t)
        {
            framework.goFWLink(x, 'admin/edit', t);
        },
/**
 * Handle a click on a link
 *
 * @param {object} e - an event
 *
 * @return void
 */
        goLink: function(e)
        {
            window.location.href = e.target.getAttribute('href');
        },
/**
 * Relocate to an admin view URL - used by the framework admin interface
 *
 * @see toggle above for info on use of data-id field
 *
 * @param {object} event - an event (not used - for compatibility when used with containerClick)
 * @param {object} x     - a dom object
 * @param {string} t     - a RedBean bean name
 *
 * @return void
 */
        goview: function(event, x, t)
        {
            framework.goFWLink(x, 'admin/view', t);
        },
/**
 * Use AJAX to create a new RedBean bean.
 * This always re-enables the button that was clicked to get here.
 *
 * @param {string} bean - a bean name
 * @param {object} data - data to pass to the bean creation: the fields to set
 * @param {function} fn - called on success
 * @param {string|object} button - the id attribute value for the button that was used to initiate the operation
 *
 * @return void
 */
        beanCreate: function(bean, data, fn, button)
        {
            framework.ajax(framework.buildFWLink('ajax/bean', bean), {method: 'POST', data: data})
            .done(fn)
            .fail(function(jx){
                framework.alert('<h3>Failed to create new '+bean+'</h3>'+jx.responseText);
            })
            .always(function(){
                (button instanceof Object ? button : document.getElementById(button)).disabled = false;
            });
        },
/**
 * Duplicate the #example item of a form. Allows users to send more data if they need to.
 *
 * @param {object} e - an event
 *
 * @return void
 */
        addMore: function(e)
        {
            fwdom.stop(e);
            const mrow = document.getElementById('mrow');
            const clone = mrow.previousElementSibling.cloneNode(true);
            for (var node of clone.getElementsByTagName('input'))
            { // empty inputs
                if (node.getAttribute('type') == 'checkbox' || node.getAttribute('type') == 'radio')
                {
                    node.checked = false;
                }
                else
                {
                    node.value = '';
                }
            }
            for (node of clone.getElementsByTagName('textarea'))
            { // empty textareas
                node.innerHTML = '';
            }
            for (node of clone.getElementsByTagName('option'))
            { // clear all selections
                node.selected = false;
            }
            for (node of clone.getElementsByTagName('select'))
            { // select first element
                node.children[0].selected = true;
            }
            mrow.parentNode.insertBefore(clone, mrow);
            //$('input,textarea', $('#mrow').prev()).val(''); // clear the new inputs
            //$('option', $('#mrow').prev()).prop('selected', false); // clear any selections
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
/**
 * Make a new element and add it in the right place in a container.
 *
 * @param {object} container - a Dom element
 * @param {string} element - what we want to insert
 * @param {object} attr - any atributes we want to add
 * @param {string} content - what goes inside the new element
 * @param {object} position - where to put it before or null
 *
 * @return void
 */
        addElement: function(container, element, attr, content, position = null) {
            const el = document.createElement(element);
            const keys = Object.keys(attr);
            keys.forEach(function(key){
                el.setAttribute(key, attr[key]);
            });
            el.innerHTML = content;
            if (position === null)
            {
                container.appendChild(el);
            }
            else
            {
                container.insertBefore(el, position);
            }
        },
/**
 * Pop up an alert
 */
        alert: function(message, title = '') {
            document.querySelector('body').insertAdjacentHTML('beforeend', '<div class="modal" id="_fwalert" tabindex="-1"><div class="modal-dialog">'+
                '<div class="modal-content"><div class="modal-header"><h5 class="modal-title">'+title+'</h5>'+
                '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>'+
                '<div class="modal-body"><p>'+message+'</p></div>'+
                '<div class="modal-footer">'+
                '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">OK</button>'+
                '</div></div></div></div>');
            framework.currentModal = document.getElementById('_fwalert');
            framework.currentModal.addEventListener('hide.bs.modal', function(){
                framework.currentModal.remove();
                framework.currentModal = null;
            });
            bootstrap.Modal.getOrCreateInstance(framework.currentModal).show();
        },
/**
 * Pop up a confirmation
 */
        confirm: function(message, handle, title = '') {
            document.querySelector('body').insertAdjacentHTML('beforeend', '<div class="modal" id="_fwconfirm" tabindex="-1">'+
                '<div class="modal-dialog"><div class="modal-content"><div class="modal-header">'+
                '<h5 class="modal-title">'+title+'</h5>'+
                '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>'+
                '<div class="modal-body"><p>'+message+'</p></div>'+
                '<div class="modal-footer">'+
                '<button type="button" id="_fwno" class="btn btn-secondary">No</button>'+
                '<button type="button" id="_fwyes" class="btn btn-primary">Yes</button>'+
                '</div></div></div></div>');
            framework.currentModal = document.getElementById('_fwconfirm');
            framework.currentModal.addEventListener('hide.bs.modal', function(e){
                framework.currentModal.remove();
                framework.currentModal = null;
            });
            document.getElementById('_fwyes').addEventListener('click', function(e) {
                e.preventDefault();
                bootstrap.Modal.getOrCreateInstance(framework.currentModal).hide();
                handle(true);
            });
            document.getElementById('_fwno').addEventListener('click', function(e){
                e.preventDefault();
                bootstrap.Modal.getOrCreateInstance(framework.currentModal).hide();
                handle(false);
            });
            bootstrap.Modal.getOrCreateInstance(framework.currentModal).show();
        }
    };
    framework.tableClick = framework.containerClick; // just for some backward compatibility....
