 fwdom.makeEdit = function()
 {
    return '<form><input type="text" value="' + this.innerText + '"/></form>';
 };

 fwdom.editable = function(div) {
    div.style.cursor = 'pointer';
    let popover = new bootstrap.Popover(e.target, {
        title: 'Edit',
        html: true,
        content: fwdom.makeEdit,
        placement: 'auto',
        selectgor: e.target
    });
    popover.show();
 };