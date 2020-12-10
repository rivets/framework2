/**
 * Some utility functions to make getting round the dom a bit less wordy
 */
    var fwdom = {
        on: function(selector, op, func, parent = null) {
            for (let d of (parent !== null ? parent : document).querySelectorAll(selector))
            {
                d.addEventListener(op, func, false);
            }
        },

        data: function(element, tag) {
            const dv = 'data-'+tag;
            return element.closest('['+dv+']').getAttribute(dv);
        },

        stop: function(e) {
            e.preventDefault();
            e.stopPropagation();
        },

        mkjQ: function(sel){
            return $(sel);
        }
    };