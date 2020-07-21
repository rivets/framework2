    var testing = {

        ajaxops : [ 'bean', 'config', 'hints', 'paging', 'pwcheck', 'shared', 'table', 'tablecheck', 'tablesearch', 'toggle', 'unique', 'uniquenl'],

        makecall : function (url, data, cdone, cfail){
            var rdata = '';
            var rcode = 200;
            data.async = true;
            $.ajax(base+'/ajax/'+url, data).done(cdone).fail(cfail)
            return [rcode, rdata];
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
            bootbox.alert('Tablecheck operation test complete');
        },
    
        testablesearch: function (){
            let t = $(this).parent();
            bootbox.alert('Tablesearch operation test complete');
        },
    
        testoggle : function(){
            let t = $(this).parent();
            bootbox.alert('Toggle operation test complete');
        },
    
        testunique: function (){
            let t = $(this).parent();
            bootbox.alert('Unique operation test complete');
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
                    t.append('<p>Existing login fails - '+jx.status+'</p>'+jx.responseText);
                }
            });
            res = testing.makecall('uniquenl/'+userbean+'/login/'+goodlogin+'XXXXX', { method: 'GET' }, function(){
                t.append('<p>Non-existent login OK</p>');
            }, function(jx) {
                t.append('<p>Non-existent login fails - '+jx.status+'</p>'+js.responseText);
            });
        },
    };