/**
 * Some utility functions to make getting round the dom a bit less wordy
 */
    var fwdom = {
        handle: function(selector, op, func, parent = null) {
            for (let d of (parent !== null ? parent : document).querySelectorAll(selector))
            {
                d.addEventHandler(op, func, false);
            }
        }
    };