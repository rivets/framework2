 fwdom.makeEdit = function()
 {
    return '<form><input type="text" value="' + this.innerText + '"/></form>';
 };



 fwdom.editable = function(div) {
    div.style.cursor = 'pointer';
    let popover = new bootstrap.Popover(div, {
        title: 'Edit',
        html: true,
        content: fwdom.makeEdit,
        placement: 'auto',
        selector: div
    });
    popover.show();
 };