 fwdom.makeEdit = function()
 {
    return '<input type="text" value="' + this.innerText + '"/>';
 };

 fwdom.editable = function(e) {
    e.target.style.cursor = 'pointer';
    let popover = new bootstrap.Popover(e.target, {
        title: 'Edit',
        container: false,
        html: true,
        content: fwdom.makeEdit,
        placement: 'auto',
    });
    popover.show();
 };