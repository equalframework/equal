<!DOCTYPE html>
<html>
<head>
<style>
html, body {
    margin: 0;
    padding: 0;
    bprder: 0;
}
iframe {
    border: 0;
}
</style>
</head>
<body>

<script>

var model;

function load() {
    var request = new XMLHttpRequest();
    var selected_package = model;
    request.onreadystatechange = function(event) {
        if (this.readyState === XMLHttpRequest.DONE) {
            if (this.status === 200) {
                var parser = new DOMParser();
                var xmlDoc = parser.parseFromString(this.responseText, "text/xml");
                var arranged = false;
                var tables = xmlDoc.getElementsByTagName("table");
                for (var i = 0 ; i < tables.length; i++) {
                    if( tables[i].getAttribute('x') && tables[i].getAttribute('y') ) {
                        arranged = true;
                    }
                }
                var frame = document.getElementById('sqldesigner');
                frame.contentWindow.SQL.Designer.fromXML(xmlDoc);
                if(!arranged) {
                    frame.contentWindow.SQL.Designer.arrangeTables();
                }
            } 
            else {
                console.log('error loading content');
            }
        }
    };
    request.open('GET', '/index.php?get=qinoa_utils_sqldesigner_schema&package='+selected_package, true);
    request.send();    
}


function save() {
    var frame = document.getElementById('sqldesigner');
    var xml = frame.contentWindow.SQL.Designer.toXML();
    console.log(xml);  


    var request = new XMLHttpRequest();

    var selected_package = model;
    request.onreadystatechange = function(event) {
        if (this.readyState === XMLHttpRequest.DONE) {
            if (this.status === 201) {

            } 
            else {
                console.log('error saving content');
            }
        }
    };
    request.open('POST', '/index.php?do=qinoa_utils_sqldesigner_update&package='+selected_package, true);
    request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");    
    request.send('xml='+escape(xml));        
}

(function init() {
    var url_string = window.location.href;
    var url = new URL(url_string);
    model = url.searchParams.get("package");
    if(!model) {
        console.log('no model found, stopping');
        return;
    }
    setTimeout(function(){ 
        load();    
    }, 1000);    

})();

</script>

<input style="display: none;" id="package" value="" />
<!--
<button onclick="load();">show</button>
-->
<button onclick="save();">save</button>
<iframe style="padding: 0; margin: 0; border: 0; margin-top: 20px; height: 1000px; width: 100%;" id="sqldesigner" src="/packages/qinoa/apps/model/sqldesigner/index.html" crolling="no" />
</body>
</html>