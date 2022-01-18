/* global goodlogin, userbean */
/* global framework, testbeanid, testtable */

    var testing = {

        ajaxops : [ 'bean', 'config', 'hints', 'js', 'paging', 'pwcheck', 'shared', 'table', 'tablecheck', 'tablesearch', 'toggle', 'unique', 'uniquenl'],

        makecall : function (url, data, cdone, cfail){
            data.async = true;
            framework.ajax(framework.base+'/ajax/'+url, data).done(function(data){
                console.log('done 1');
                return data;
            }).done(cdone).fail(cfail);
        },
        testbean: function (){
            framework.alert('Bean operation test complete');
        },

        testconfig : function (){
            let t = this.parentNode;
            testing.makecall('config/testconfig', { method: 'POST', data: {value: 123, type: 'string'} }, function(){
                t.insertAdjacentHTML('beforeend', '<p>Create config item OK</p>');
                testing.makecall('config/testconfig', { method: 'GET' }, function(data){
                    t.insertAdjacentHTML('beforeend', '<p>Read config item OK '+data+'</p>');
                    testing.makecall('config/testconfig', { method: framework.putorpatch, data: {value: 345} }, function(data){
                        t.insertAdjacentHTML('beforeend', '<p>Update config item OK returns '+data.value+'</p>');
                        testing.makecall('config/testconfig', { method: 'GET' }, function(data){
                            if (data == 345)
                            {
                                t.insertAdjacentHTML('beforeend', '<p>Read config item OK '+data+'</p>');
                            }
                            else
                            {
                                t.insertAdjacentHTML('beforeend', '<p>Read config item unexpected result '+data+'</p>');
                            }
                            testing.makecall('config/testconfig', { method: 'DELETE' }, function(){
                                t.insertAdjacentHTML('beforeend', '<p>Delete config item OK</p>');
                            }, function(jx){
                                t.insertAdjacentHTML('beforeend', '<p>Delete config item fails - '+jx.status+' '+jx.responseText+'</p>');
                            });
                        }, function(jx){
                            t.insertAdjacentHTML('beforeend', '<p>Read config item fails - '+jx.status+' '+jx.responseText+'</p>');
                            testing.makecall('config/testconfig', { method: 'DELETE' }, function(){
                                t.insertAdjacentHTML('beforeend', '<p>Delete config item OK</p>');
                            }, function(jx){
                                t.insertAdjacentHTML('beforeend', '<p>Delete config item fails - '+jx.status+' '+jx.responseText+'</p>');
                            });
                        });
                    }, function(jx){
                        t.insertAdjacentHTML('beforeend', '<p>Update config item fails - '+jx.status+' '+jx.responseText+'</p>');
                        testing.makecall('config/testconfig', { method: 'DELETE' }, function(){
                            t.insertAdjacentHTML('beforeend', '<p>Delete config item OK</p>');
                        }, function(jx){
                            t.insertAdjacentHTML('beforeend', '<p>Delete config item fails - '+jx.status+' '+jx.responseText+'</p>');
                        });
                    });
                }, function(jx){
                    t.insertAdjacentHTML('beforeend', '<p>Read config item fails - '+jx.status+' '+jx.responseText+'</p>');
                    testing.makecall('config/testconfig', { method: 'DELETE' }, function(){
                        t.insertAdjacentHTML('beforeend', '<p>Delete config item OK</p>');
                    }, function(jx){
                        t.insertAdjacentHTML('beforeend', '<p>Delete config item fails - '+jx.status+' '+jx.responseText+'</p>');
                    });
                });
            }, function(jx){
                t.insertAdjacentHTML('beforeend', '<p>Create config item fails - '+jx.status+' '+jx.responseText+'</p>');
            });
        },

        testhints: function (){
            let t = this.parentNode;
            testing.makecall('hints/'+testtable+'/f1?search=a%', { method: 'GET' }, function(data){
                t.insertAdjacentHTML('beforeend', '<p>Hints OK: '+data.length+' '+data[0].value+' '+data[0].text+'</p>');
            }, function(jx) {
                t.insertAdjacentHTML('beforeend', '<p>Hints failed - '+jx.status+' '+jx.responseText+'</p>');
            });
            testing.makecall('hints/'+testtable+'/f1/text?search=a%', { method: 'GET' }, function(data){
                t.insertAdjacentHTML('beforeend', '<p>Hints OK: '+data.length+' '+data[0].value+' '+data[0].text+'</p>');
            }, function(jx) {
                t.insertAdjacentHTML('beforeend', '<p>Hints failed - '+jx.status+' '+jx.responseText+'</p>');
            });
            testing.makecall('hints/'+testtable+'/tog/text', { method: 'GET' }, function(data){
                t.insertAdjacentHTML('beforeend', '<p>Toggle non-hintable field FAILS returns 200: '+data.length+'</p>');
            }, function(jx) {
                if (jx.status == 403)
                {
                    t.insertAdjacentHTML('beforeend', '<p>Toggle non-hintable field OK - '+jx.status+' '+jx.responseText+'</p>');
                }
                else
                {
                    t.insertAdjacentHTML('beforeend', '<p>Toggle non-hintable field fails - '+jx.status+' '+jx.responseText+'</p>');
                }
            });
        },

        testpaging: function (){
            framework.alert('Paging operation test complete');
        },

        testpwcheck: function (){
            framework.alert('Pwcheck operation test complete');
        },

        testshared: function (){
            framework.alert('Shared operation test complete');
        },

        testtable: function (){
            framework.alert('Table operation test complete');
        },

        testtablecheck: function (){
            let t = this.parentNode;
            testing.makecall('tablecheck/'+testtable, { method: 'GET' }, function(){
                t.insertAdjacentHTML('beforeend', '<p>Existing table fails - 200 on existing login</p>');
            }, function(jx){
                if (jx.status == 404)
                {
                   t.insertAdjacentHTML('beforeend', '<p>Existing table OK</p>');
                }
                else
                {
                    t.insertAdjacentHTML('beforeend', '<p>Existing table fails - '+jx.status+' '+jx.responseText+'</p>');
                }
            });
            testing.makecall('tablecheck/'+testtable+'XXXXX', { method: 'GET' }, function(){
                t.insertAdjacentHTML('beforeend', '<p>Non-existent table OK</p>');
            }, function(jx) {
                t.insertAdjacentHTML('beforeend', '<p>Non-existent table fails - '+jx.status+' '+jx.responseText+'</p>');
            });
        },

        testtablesearch: function (){
            let t = this.parentNode;
            testing.makecall('tablesearch/'+testtable+'/f1/4?value=string', { method: 'GET' }, function(data){
                t.insertAdjacentHTML('beforeend', '<p>Search for string OK : '+data.length+'</p>');
            }, function(jx) {
                t.insertAdjacentHTML('beforeend', '<p>Search for string Fails- '+jx.status+' '+jx.responseText+'</p>');
            });
            testing.makecall('tablesearch/'+testtable+'/f1/4?value=abcdefg', { method: 'GET' }, function(data){
                t.insertAdjacentHTML('beforeend', '<p>Search for non-existent value OK : '+data.length+'</p>');
            }, function(jx) {
                t.insertAdjacentHTML('beforeend', '<p>Search for non-existent value Fails - '+jx.status+' '+jx.responseText+'</p>');
            });
            testing.makecall('tablesearch/'+testtable+'/tog/1?value=1', { method: 'GET' }, function(data){
                t.insertAdjacentHTML('beforeend', '<p>Search on no-access field fails with 200 : '+data.length+'</p>');
            }, function(jx) {
                if (jx.status == 403)
                {
                   t.insertAdjacentHTML('beforeend', '<p>Search on no-access field OK - 403</p>');
                }
                else
                {
                    t.insertAdjacentHTML('beforeend', '<p>Search on no-access field fails- '+jx.status+' '+jx.responseText+'</p>');
                }
            });
        },

        testtoggle : function(){
            let t = this.parentNode;
            let tstate = 2;
            testing.makecall('toggle/'+testtable+'/'+testbeanid+'/tog', { method: 'POST' }, function(data){
                t.insertAdjacentHTML('beforeend', '<p>Toggle OK : '+data+'</p>');
                tstate = data;
                testing.makecall('toggle/'+testtable+'/'+testbeanid+'/tog', { method: 'POST' }, function(data){
                    if (tstate != data)
                    {
                        t.insertAdjacentHTML('beforeend', '<p>Toggle OK : '+data+'</p>');
                    }
                    else
                    {
                        t.insertAdjacentHTML('beforeend', '<p>Toggle fails, have '+tstate+' got '+data+'</p>');
                    }
                }, function(jx) {
                    t.insertAdjacentHTML('beforeend', '<p>Toggle Fails- '+jx.status+' "'+jx.responseText.trim()+'"</p>');
                });
            }, function(jx) {
                t.insertAdjacentHTML('beforeend', '<p>Toggle Fails- '+jx.status+' "'+jx.responseText.trim()+'"</p>');
            });
            testing.makecall('toggle/'+testtable+'/'+testbeanid+'/f1', { method: 'POST' }, function(data){
                t.insertAdjacentHTML('beforeend', '<p>Toggle non-toggleable field FAILS returns 200: '+data.length+'</p>');
            }, function(jx) {
                t.insertAdjacentHTML('beforeend', '<p>Toggle non-toggleable field OK - '+jx.status+' "'+jx.responseText.trim()+'"</p>');
            });
        },

        testunique: function (){
            let t = this.parentNode;
            testing.makecall('unique/'+userbean+'/login/'+goodlogin, { method: 'GET' }, function(){
                t.insertAdjacentHTML('beforeend', '<p>Existing login fails - 200 on existing login</p>');
            }, function(jx){
                if (jx.status == 404)
                {
                   t.insertAdjacentHTML('beforeend', '<p>Existing login OK</p>');
                }
                else
                {
                    t.insertAdjacentHTML('beforeend', '<p>Existing login fails - '+jx.status+' '+jx.responseText+'</p>');
                }
            });
            testing.makecall('unique/'+userbean+'/login/'+goodlogin+'XXXXX', { method: 'GET' }, function(){
                t.insertAdjacentHTML('beforeend', '<p>Non-existent login OK</p>');
            }, function(jx) {
                t.insertAdjacentHTML('beforeend', '<p>Non-existent login fails - '+jx.status+' '+jx.responseText+'</p>');
            });
        },

        testuniquenl :function (){
            let t = this.parentNode;
            testing.makecall('uniquenl/'+userbean+'/login/'+goodlogin, { method: 'GET' }, function(){
                t.insertAdjacentHTML('beforeend', '<p>Existing login fails - 200 on existing login</p>');
            }, function(jx){
                if (jx.status == 404)
                {
                   t.insertAdjacentHTML('beforeend', '<p>Existing login OK</p>');
                }
                else
                {
                    t.insertAdjacentHTML('beforeend', '<p>Existing login fails - '+jx.status+' '+jx.responseText+'</p>');
                }
            });
            testing.makecall('uniquenl/'+userbean+'/login/'+goodlogin+'XXXXX', { method: 'GET' }, function(){
                t.insertAdjacentHTML('beforeend', '<p>Non-existent login OK</p>');
            }, function(jx) {
                t.insertAdjacentHTML('beforeend', '<p>Non-existent login fails - '+jx.status+' '+jx.responseText+'</p>');
            });
        },

        testjs :function (){
            let t = this.parentNode;
            framework.ajax(framework.base+'/ajax/nosuchop', { method: 'GET', data: {test: 'testdata'} }).done(function(){
                t.insertAdjacentHTML('beforeend', '<p>Should fail but succeeded</p>');
            }).fail(function(){
                t.insertAdjacentHTML('beforeend', '<p>Fail 1 called</p>');
            })
            .fail(function(){
                t.insertAdjacentHTML('beforeend', '<p>Fail 2 called</p>');
            })
            .always(function(){
                t.insertAdjacentHTML('beforeend', '<p>Always 1 called</p>');
            })
            .always(function(){
                t.insertAdjacentHTML('beforeend', '<p>Always 2 called</p>');
            });
        }
    };