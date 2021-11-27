var testing={ajaxops:["bean","config","hints","paging","pwcheck","shared","table","tablecheck","tablesearch","toggle","unique","uniquenl"],makecall:function(e,t,n,a){t.async=!0,framework.ajax(framework.base+"/ajax/"+e,t).done(n).fail(a)},testbean:function(){fwdom.alert("Bean operation test complete")},testconfig:function(){let e=this.parentNode;testing.makecall("config/testconfig",{method:"POST",data:{value:123,type:"string"}},(function(){e.append("<p>Create config item OK</p>"),testing.makecall("config/testconfig",{method:"GET"},(function(t){e.append("<p>Read config item OK "+t+"</p>"),testing.makecall("config/testconfig",{method:putorpatch,data:{value:345}},(function(t){e.append("<p>Update config item OK returns "+t.value+"</p>"),testing.makecall("config/testconfig",{method:"GET"},(function(t){345==t?e.append("<p>Read config item OK "+t+"</p>"):e.append("<p>Read config item unexpected result "+t+"</p>"),testing.makecall("config/testconfig",{method:"DELETE"},(function(){e.append("<p>Delete config item OK</p>")}),(function(t){e.append("<p>Delete config item fails - "+t.status+" "+t.responseText+"</p>")}))}),(function(t){e.append("<p>Read config item fails - "+t.status+" "+t.responseText+"</p>"),testing.makecall("config/testconfig",{method:"DELETE"},(function(){e.append("<p>Delete config item OK</p>")}),(function(t){e.append("<p>Delete config item fails - "+t.status+" "+t.responseText+"</p>")}))}))}),(function(t){e.append("<p>Update config item fails - "+t.status+" "+t.responseText+"</p>"),testing.makecall("config/testconfig",{method:"DELETE"},(function(){e.append("<p>Delete config item OK</p>")}),(function(t){e.append("<p>Delete config item fails - "+t.status+" "+t.responseText+"</p>")}))}))}),(function(t){e.append("<p>Read config item fails - "+t.status+" "+t.responseText+"</p>"),testing.makecall("config/testconfig",{method:"DELETE"},(function(){e.append("<p>Delete config item OK</p>")}),(function(t){e.append("<p>Delete config item fails - "+t.status+" "+t.responseText+"</p>")}))}))}),(function(t){e.append("<p>Create config item fails - "+t.status+" "+t.responseText+"</p>")}))},testhints:function(){let e=this.parentNode;testing.makecall("hints/"+testtable+"/f1?search=a%",{method:"GET"},(function(t){e.append("<p>Hints OK: "+t.length+" "+t[0].value+" "+t[0].text+"</p>")}),(function(t){e.append("<p>Hints failed - "+t.status+" "+t.responseText+"</p>")})),testing.makecall("hints/"+testtable+"/f1/text?search=a%",{method:"GET"},(function(t){e.append("<p>Hints OK: "+t.length+" "+t[0].value+" "+t[0].text+"</p>")}),(function(t){e.append("<p>Hints failed - "+t.status+" "+t.responseText+"</p>")})),testing.makecall("hints/"+testtable+"/tog/text",{method:"GET"},(function(t){e.append("<p>Toggle non-hintable field FAILS returns 200: "+t.length+"</p>")}),(function(t){403==t.status?e.append("<p>Toggle non-hintable field OK - "+t.status+" "+t.responseText+"</p>"):e.append("<p>Toggle non-hintable field fails - "+t.status+" "+t.responseText+"</p>")}))},testpaging:function(){fwdom.alert("Paging operation test complete")},testpwcheck:function(){fwdom.alert("Pwcheck operation test complete")},testshared:function(){fwdom.alert("Shared operation test complete")},testtable:function(){fwdom.alert("Table operation test complete")},testtablecheck:function(){let e=this.parentNode;testing.makecall("tablecheck/"+testtable,{method:"GET"},(function(){e.append("<p>Existing table fails - 200 on existing login</p>")}),(function(t){404==t.status?e.append("<p>Existing table OK</p>"):e.append("<p>Existing table fails - "+t.status+" "+t.responseText+"</p>")})),testing.makecall("tablecheck/"+testtable+"XXXXX",{method:"GET"},(function(){e.append("<p>Non-existent table OK</p>")}),(function(t){e.append("<p>Non-existent table fails - "+t.status+" "+t.responseText+"</p>")}))},testtablesearch:function(){let e=this.parentNode;testing.makecall("tablesearch/"+testtable+"/f1/4?value=string",{method:"GET"},(function(t){e.append("<p>Search for string OK : "+t.length+"</p>")}),(function(t){e.append("<p>Search for string Fails- "+t.status+" "+t.responseText+"</p>")})),testing.makecall("tablesearch/"+testtable+"/f1/4?value=abcdefg",{method:"GET"},(function(t){e.append("<p>Search for non-existent value OK : "+t.length+"</p>")}),(function(t){e.append("<p>Search for non-existent value Fails - "+t.status+" "+t.responseText+"</p>")})),testing.makecall("tablesearch/"+testtable+"/tog/1?value=1",{method:"GET"},(function(t){e.append("<p>Search on no-access field fails with 200 : "+t.length+"</p>")}),(function(t){403==t.status?e.append("<p>Search on no-access field OK - 403</p>"):e.append("<p>Search on no-access field fails- "+t.status+" "+t.responseText+"</p>")}))},testtoggle:function(){let e=this.parentNode,t=2;testing.makecall("toggle/"+testtable+"/"+testbeanid+"/tog",{method:"POST"},(function(n){e.append("<p>Toggle OK : "+n+"</p>"),t=n,testing.makecall("toggle/"+testtable+"/"+testbeanid+"/tog",{method:"POST"},(function(n){t!=n?e.append("<p>Toggle OK : "+n+"</p>"):e.append("<p>Toggle fails, have "+t+" got "+n+"</p>")}),(function(t){e.append("<p>Toggle Fails- "+t.status+' "'+t.responseText.trim()+'"</p>')}))}),(function(t){e.append("<p>Toggle Fails- "+t.status+' "'+t.responseText.trim()+'"</p>')})),testing.makecall("toggle/"+testtable+"/"+testbeanid+"/f1",{method:"POST"},(function(t){e.append("<p>Toggle non-toggleable field FAILS returns 200: "+t.length+"</p>")}),(function(t){e.append("<p>Toggle non-toggleable field OK - "+t.status+' "'+t.responseText.trim()+'"</p>')}))},testunique:function(){let e=this.parentNode;testing.makecall("unique/"+userbean+"/login/"+goodlogin,{method:"GET"},(function(){e.append("<p>Existing login fails - 200 on existing login</p>")}),(function(t){404==t.status?e.append("<p>Existing login OK</p>"):e.append("<p>Existing login fails - "+t.status+" "+t.responseText+"</p>")})),testing.makecall("unique/"+userbean+"/login/"+goodlogin+"XXXXX",{method:"GET"},(function(){e.append("<p>Non-existent login OK</p>")}),(function(t){e.append("<p>Non-existent login fails - "+t.status+" "+t.responseText+"</p>")}))},testuniquenl:function(){let e=this.parentNode;testing.makecall("uniquenl/"+userbean+"/login/"+goodlogin,{method:"GET"},(function(){e.append("<p>Existing login fails - 200 on existing login</p>")}),(function(t){404==t.status?e.append("<p>Existing login OK</p>"):e.append("<p>Existing login fails - "+t.status+" "+t.responseText+"</p>")})),testing.makecall("uniquenl/"+userbean+"/login/"+goodlogin+"XXXXX",{method:"GET"},(function(){e.append("<p>Non-existent login OK</p>")}),(function(t){e.append("<p>Non-existent login fails - "+t.status+" "+t.responseText+"</p>")}))}};
//# sourceMappingURL=testajax-min.js.map