    var testing = {

        ajaxops : [ 'bean', 'config', 'hints', 'paging', 'pwcheck', 'shared', 'table', 'tablecheck', 'tablesearch', 'toggle', 'unique', 'uniquenl'],

        makecall : function (url, data, cdone, cfail){
            var rdata = '';
            var rcode = 200;
            data.async = true;
            $.ajax(base+'/ajax/'+url, data).done(cdone).fail(cfail)
        },
        testbean: function (){
            let t = $(this).parent();
            bootbox.alert('Bean operation test complete');
        },
    
        testconfig : function (){
            let t = $(this).parent();
            bootbox.alert('Config operation test complete');
        },
    
        testhints: function (){
            let t = $(this).parent();
            bootbox.alert('Hints operation test complete');
        },

        testpaging: function (){
            let t = $(this).parent();
            bootbox.alert('Paging operation test complete');
        },
    
        testpwcheck: function (){
            let t = $(this).parent();
            bootbox.alert('Pwcheck operation test complete');
        },
    
        testshared: function (){
            let t = $(this).parent();
            bootbox.alert('Shared operation test complete');
        },
    
        testtable: function (){
            let t = $(this).parent();
            bootbox.alert('Table operation test complete');
        },
    
        testtablecheck: function (){
            let t = $(this).parent();
            testing.makecall('tablecheck/'+testtable, { method: 'GET' }, function(){
                t.append('<p>Existing table fails - 200 on existing login</p>');
            }, function(jx){
                if (jx.status == 404)
                {
                   t.append('<p>Existing table OK</p>');
                }
                else
                {
                    t.append('<p>Existing table fails - '+jx.status+' '+jx.responseText+'</p>');
                }
            });
            testing.makecall('tablecheck/'+testtable+'XXXXX', { method: 'GET' }, function(){
                t.append('<p>Non-existent table OK</p>');
            }, function(jx) {
                t.append('<p>Non-existent table fails - '+jx.status+' '+jx.responseText+'</p>');
            });
        },
    
        testtablesearch: function (){
            let t = $(this).parent();
            testing.makecall('tablesearch/'+testtable+'/f1/4?value=string', { method: 'GET' }, function(data){
                t.append('<p>Search for string OK : '+data.length+'</p>');
            }, function(jx) {
                t.append('<p>Search for string Fails- '+jx.status+' '+jx.responseText+'</p>');
            });
            testing.makecall('tablesearch/'+testtable+'/f1/4?value=abcdefg', { method: 'GET' }, function(data){
                t.append('<p>Search for non-existent value OK : '+data.length+'</p>');
            }, function(jx) {
                t.append('<p>Search for non-existent value Fails - '+jx.status+' '+jx.responseText+'</p>');
            });
            testing.makecall('tablesearch/'+testtable+'/tog/1?value=1', { method: 'GET' }, function(data){
                t.append('<p>Search on no-access field fails with 200 : '+data.length+'</p>');
            }, function(jx) {
                if (jx.status == 403)
                {
                   t.append('<p>Search on no-access field OK - 403</p>');
                }
                else
                {
                    t.append('<p>Search on no-access field fails- '+jx.status+' '+jx.responseText+'</p>');
                }
            });
        },
    
        testtoggle : function(){
            let t = $(this).parent();
            testing.makecall('toggle/'+testtable+'/'+testbeanid+'/tog', { method: 'POST' }, function(data){
                t.append('<p>Toggle OK : '+data.length+'</p>');
            }, function(jx) {
                t.append('<p>Toggle Fails- '+jx.status+'</p>'+jx.responseText);
            });
            testing.makecall('toggle/'+testtable+'/'+testbeanid+'/f1', { method: 'POST' }, function(data){
                t.append('<p>Toggle non-toggleable field OK : '+data.length+'</p>');
            }, function(jx) {
                t.append('<p>Toggle non-toggleable field Fails - '+jx.status+' '+jx.responseText+'</p>');
            });
        },
    
        testunique: function (){
            let t = $(this).parent();
            testing.makecall('unique/'+userbean+'/login/'+goodlogin, { method: 'GET' }, function(){
                t.append('<p>Existing login fails - 200 on existing login</p>');
            }, function(jx){
                if (jx.status == 404)
                {
                   t.append('<p>Existing login OK</p>');
                }
                else
                {
                    t.append('<p>Existing login fails - '+jx.status+' '+jx.responseText+'</p>');
                }
            });
            testing.makecall('unique/'+userbean+'/login/'+goodlogin+'XXXXX', { method: 'GET' }, function(){
                t.append('<p>Non-existent login OK</p>');
            }, function(jx) {
                t.append('<p>Non-existent login fails - '+jx.status+' '+jx.responseText+'</p>');
            });
        },
    
        testuniquenl :function (bean){
            let t = $(this).parent();
            let res = testing.makecall('uniquenl/'+userbean+'/login/'+goodlogin, { method: 'GET' }, function(){
                t.append('<p>Existing login fails - 200 on existing login</p>');
            }, function(jx){
                if (jx.status == 404)
                {
                   t.append('<p>Existing login OK</p>');
                }
                else
                {
                    t.append('<p>Existing login fails - '+jx.status+' '+jx.responseText+'</p>');
                }
            });
            res = testing.makecall('uniquenl/'+userbean+'/login/'+goodlogin+'XXXXX', { method: 'GET' }, function(){
                t.append('<p>Non-existent login OK</p>');
            }, function(jx) {
                t.append('<p>Non-existent login fails - '+jx.status+' '+jx.responseText+'</p>');
            });
        },
    };