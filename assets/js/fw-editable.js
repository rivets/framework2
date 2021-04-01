 fwdom.editable = function(e) {
    let popover = new bootstrap.Popover(e.target, {
        title: 'Edit',
        container: 'body',
        html: true,
        content: '<input type="text" value="' + e.target.innerText + '"/>',
    });
    popover.show();
 };