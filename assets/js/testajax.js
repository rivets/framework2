    var testing = {

        ajaxops : [ 'bean', 'config', 'hints', 'paging', 'pwcheck', 'shared', 'table', 'tablecheck', 'tablesearch', 'toggle', 'unique', 'uniquenl'],

        makecall : function (url, data){
            var rdata = '';
            var rcode = 200;
            data.async = true;
            $.ajax(base+'/ajax/'+url, data).done(function(data){ rdata = data; }).fail(function(jx){
                rcode = jx.status;
                rdata = jx.responseText;
            });
            return [rdata, rcode];
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
            var res = testing.makecall('uniquenl/'+userbean+'/login/'+goodlogin, { method: 'GET' });
console.log($res);
            if (res[0] == 200)
            {
                t.append('<p>Existing login fails - 200 on existing login</p>');
            }
            else if (res[0] == 404)
            {
               t.append('<p>Existing login OK</p>');
            }
            else
            {
                t.append('<p>Existing login fails - '+res[0]+'</p>'+res[1]);
            }
            res = testing.makecall('uniquenl/'+userbean+'/login/'+goodlogin+'XXXXX', { method: 'GET' });
            if (res[0] == 200)
            {
                t.append('<p>Non-existent login OK</p>');
            }
            else
            {
                t.append('<p>Non-existent login fails - '+res[0]+'</p>'+res[1]);
            }
            t.append('<p>Test Complete</p>');
        },
    };