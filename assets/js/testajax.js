    var testing = {

        ajaxops : [ 'bean', 'config', 'hints', 'paging', 'pwcheck', 'shared', 'table', 'tablecheck', 'tablesearch', 'toggle', 'unique', 'uniquenl'],

        makecall : function (url, data, cdone, cfail){
            data.async = true;
            $.ajax(base+'/ajax/'+url, data).done(cdone).fail(cfail);
        },
        testbean: function (){
            bootbox.alert('Bean operation test complete');
        },
    
        testconfig : function (){
            let t = $(this).parent();
            testing.makecall('config/testconfig', { method: 'POST', data: {value: 123, type: 'string'} }, function(){
                t.append('<p>Create config item OK</p>');
                testing.makecall('config/testconfig', { method: 'GET' }, function(data){
                    t.append('<p>Read config item OK '+data+'</p>');
                    testing.makecall('config/testconfig', { method: putorpatch, data: {value: 345} }, function(data){
                        t.append('<p>Update config item OK returns '+data.value+'</p>');
                        testing.makecall('config/testconfig', { method: 'GET' }, function(data){
                            if (data == 345)
                            {
                                t.append('<p>Read config item OK '+data+'</p>');
                            }
                            else
                            {
                                t.append('<p>Read config item unexpected result '+data+'</p>');
                            }
                            testing.makecall('config/testconfig', { method: 'DELETE' }, function(){
                                t.append('<p>Delete config item OK</p>');
                            }, function(jx){
                                t.append('<p>Delete config item fails - '+jx.status+' '+jx.responseText+'</p>');
                            });
                        }, function(jx){
                            t.append('<p>Read config item fails - '+jx.status+' '+jx.responseText+'</p>');
                            testing.makecall('config/testconfig', { method: 'DELETE' }, function(){
                                t.append('<p>Delete config item OK</p>');
                            }, function(jx){
                                t.append('<p>Delete config item fails - '+jx.status+' '+jx.responseText+'</p>');
                            });
                        });
                    }, function(jx){
                        t.append('<p>Update config item fails - '+jx.status+' '+jx.responseText+'</p>');
                        testing.makecall('config/testconfig', { method: 'DELETE' }, function(){
                            t.append('<p>Delete config item OK</p>');
                        }, function(jx){
                            t.append('<p>Delete config item fails - '+jx.status+' '+jx.responseText+'</p>');
                        });
                    });
                }, function(jx){
                    t.append('<p>Read config item fails - '+jx.status+' '+jx.responseText+'</p>');
                    testing.makecall('config/testconfig', { method: 'DELETE' }, function(){
                        t.append('<p>Delete config item OK</p>');
                    }, function(jx){
                        t.append('<p>Delete config item fails - '+jx.status+' '+jx.responseText+'</p>');
                    });
                });
            }, function(jx){
                t.append('<p>Create config item fails - '+jx.status+' '+jx.responseText+'</p>');
            });
        },
    
        testhints: function (){
            let t = $(this).parent();
            testing.makecall('hints/'+testtable+'/f1?search=a%', { method: 'GET' }, function(data){
                t.append('<p>Hints OK: '+data.length+' '+data[0].value+' '+data[0].text+'</p>');
            }, function(jx) {
                t.append('<p>Hints failed - '+jx.status+' '+jx.responseText+'</p>');
            });
            testing.makecall('hints/'+testtable+'/f1/text?search=a%', { method: 'GET' }, function(data){
                t.append('<p>Hints OK: '+data.length+' '+data[0].value+' '+data[0].text+'</p>');
            }, function(jx) {
                t.append('<p>Hints failed - '+jx.status+' '+jx.responseText+'</p>');
            });
            testing.makecall('hints/'+testtable+'/tog/text', { method: 'GET' }, function(data){
                t.append('<p>Toggle non-hintable field FAILS returns 200: '+data.length+'</p>');
            }, function(jx) {
                if (jx.status == 403)
                {
                    t.append('<p>Toggle non-hintable field OK - '+jx.status+' '+jx.responseText+'</p>');
                }
                else
                {
                    t.append('<p>Toggle non-hintable field fails - '+jx.status+' '+jx.responseText+'</p>');
                }
            });
        },

        testpaging: function (){
            bootbox.alert('Paging operation test complete');
        },
    
        testpwcheck: function (){
            bootbox.alert('Pwcheck operation test complete');
        },
    
        testshared: function (){
            bootbox.alert('Shared operation test complete');
        },
    
        testtable: function (){
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
            let tstate = 2;
            testing.makecall('toggle/'+testtable+'/'+testbeanid+'/tog', { method: 'POST' }, function(data){
                t.append('<p>Toggle OK : '+data+'</p>');
                tstate = data;
                testing.makecall('toggle/'+testtable+'/'+testbeanid+'/tog', { method: 'POST' }, function(data){
                    if (tstate != data)
                    {
                        t.append('<p>Toggle OK : '+data+'</p>');
                    }
                    else
                    {
                        t.append('<p>Toggle fails, have '+tstate+' got '+data+'</p>');
                    }
                }, function(jx) {
                    t.append('<p>Toggle Fails- '+jx.status+' '+jx.responseText+'</p>');
                });
            }, function(jx) {
                t.append('<p>Toggle Fails- '+jx.status+' '+jx.responseText+'</p>');
            });
            testing.makecall('toggle/'+testtable+'/'+testbeanid+'/f1', { method: 'POST' }, function(data){
                t.append('<p>Toggle non-toggleable field FAILS returns 200: '+data.length+'</p>');
            }, function(jx) {
                t.append('<p>Toggle non-toggleable field OK - '+jx.status+' '+jx.responseText+'</p>');
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
    
        testuniquenl :function (){
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