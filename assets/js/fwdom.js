/* globals document, jQuery */
/* jshint undef: true, unused: false */
/*
 * Some utility functions to make getting round the dom a bit less wordy
 */
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