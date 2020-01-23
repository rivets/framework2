    'use strict';

    var ajaxops = [ 'bean', 'config', 'hints', 'paging', 'pwcheck', 'shared', 'table', 'tablecheck', 'tablesearch', 'toggle', 'unique', 'uniquenl'];

    function makecall(url, data, succ, err)
    {
        data.async = true;
        $.ajax(base+'/ajax/'+url, data).done(succ).fail(err);
    }
    function testbean()
    {
        let t = $(this).parent();
        bootbox.alert('Bean operation test complete');
    }

    function testconfig()
    {
        let t = $(this).parent();
        bootbox.alert('Config operation test complete');
    }

    function testhints()
    {
        let t = $(this).parent();
        bootbox.alert('Hints operation test complete');
    }

    function testpaging()
    {
        let t = $(this).parent();
        bootbox.alert('Paging operation test complete');
    }

    function testpwcheck()
    {
        let t = $(this).parent();
        bootbox.alert('Pwcheck operation test complete');
    }

    function testshared()
    {
        let t = $(this).parent();
        bootbox.alert('Shared operation test complete');
    }

    function testtable()
    {
        let t = $(this).parent();
        bootbox.alert('Table operation test complete');
    }

    function testtablecheck()
    {
        let t = $(this).parent();
        bootbox.alert('Tablecheck operation test complete');
    }

    function testtablesearch()
    {
        let t = $(this).parent();
        bootbox.alert('Tablesearch operation test complete');
    }

    function testtoggle()
    {
        let t = $(this).parent();
        bootbox.alert('Toggle operation test complete');
    }

    function testunique()
    {
        let t = $(this).parent();
        bootbox.alert('Unique operation test complete');
    }

    function testuniquenl()
    {
        let t = $(this).parent();
        makecall('uniquenl/user/login/'+goodlogin, { method: 'GET' }, function(e){
            t.append('<p>Existing login fails - 200 on existing login</p>');
        }, function(jx){
            if (jx.status == 404)
            {
               t.append('<p>Existing login OK</p>');
            }
            else
            {
                t.append('<p>Existing login fails - '+jx.status+'</p>'+jx.responseText);
            }
        });
        makecall('uniquenl/user/login/'+goodlogin+'XXXXX', { method: 'GET' }).done(function(e){
            t.append('<p>Non-existent login OK</p>');
        }, function(jx){
            t.append('<p>Non-existent login fails - '+jx.status+'</p>'+jx.responseText);
        });
        t.append('<p>Test Complete</p>'));
    }