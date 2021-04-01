 fwdom.editable = function(e) {
    e.target.style.cursor = 'pointer';
    let popover = new bootstrap.Popover(e.target, {
        title: 'Edit',
        container: false,
        html: false,
        content: '<input type="text" value="' + e.target.innerText + '"/>',
        placement: 'auto',
    });
    popover.show();
 };