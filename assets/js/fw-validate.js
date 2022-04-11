
var fwValid = {
    setup: function (form) { // setup things at the start
        form.querySelectorAll('data-fw-valid-trigger').each(function(){
            this.addEventHandler(this.getAttribute('data-fw-valid-trigger'), function(e){
                fwdom.stop(e);
            });
        });
    },

    validate: function(form) {
        let ok = true;
        form.querySelectorAll('required').each(function(){
            if (this.value === '')
            { // not specified - flag error
                ok = false;
                // set up warning message here
            }
        });
        return ok;
    }
}