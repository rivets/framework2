 fwdom.editable = function(e) {
    let popover = new bootstrap.Popover(this, {
        title: 'Edit',
        container: 'body',
        html: true,
        content: '<input type="text" value="' + this.innerHTML + '"/>',
    });
    popover.show();
 }