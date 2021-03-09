<!DOCTYPE html>
<!--
	WWW SQL Designer, (C) 2005-2015 Ondrej Zara, ondras@zarovi.cz
	Version: 2.7
	See license.txt for licencing information.
-->
<html>
<head>
	<title>WWW SQL Designer</title>
	<meta name="viewport" content="initial-scale=1,maximum-scale=1" />
 	<meta charset="utf-8" />
	<link rel="stylesheet" href="/packages/qinoa/apps/model/sqldesigner/styles/style.css" media="all" />     
	<!--[if IE 6]><link rel="stylesheet" type="text/css" href="styles/ie6.css" /><![endif]-->
	<!--[if IE 7]><link rel="stylesheet" type="text/css" href="styles/ie7.css" /><![endif]-->
	<link rel="stylesheet" href="/packages/qinoa/apps/model/sqldesigner/styles/print.css" type="text/css" media="print" />
	<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/dropbox.js/0.10.2/dropbox.min.js"></script>

	<script src="/packages/qinoa/apps/model/sqldesigner/js/oz.js"></script>
	<script src="/packages/qinoa/apps/model/sqldesigner/js/config.js"></script>
	<script src="/packages/qinoa/apps/model/sqldesigner/js/globals.js"></script>
	<script src="/packages/qinoa/apps/model/sqldesigner/js/visual.js"></script>
	<script src="/packages/qinoa/apps/model/sqldesigner/js/row.js"></script>
	<script src="/packages/qinoa/apps/model/sqldesigner/js/table.js"></script>
	<script src="/packages/qinoa/apps/model/sqldesigner/js/relation.js"></script>
	<script src="/packages/qinoa/apps/model/sqldesigner/js/key.js"></script>
	<script src="/packages/qinoa/apps/model/sqldesigner/js/rubberband.js"></script>
	<script src="/packages/qinoa/apps/model/sqldesigner/js/map.js"></script>
	<script src="/packages/qinoa/apps/model/sqldesigner/js/toggle.js"></script>
	<script src="/packages/qinoa/apps/model/sqldesigner/js/io.js"></script>
	<script src="/packages/qinoa/apps/model/sqldesigner/js/tablemanager.js"></script>
	<script src="/packages/qinoa/apps/model/sqldesigner/js/rowmanager.js"></script>
	<script src="/packages/qinoa/apps/model/sqldesigner/js/keymanager.js"></script>
	<script src="/packages/qinoa/apps/model/sqldesigner/js/window.js"></script>
	<script src="/packages/qinoa/apps/model/sqldesigner/js/options.js"></script>
	<script src="/packages/qinoa/apps/model/sqldesigner/js/wwwsqldesigner.js"></script>
    
    
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

                d.fromXML(xmlDoc);
                if(!arranged) {
                    d.arrangeTables();
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

    var xml = d.toXML();
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
</head>

<body>
    <input style="display: none;" id="package" value="" />
    <!--
    <button onclick="load();">show</button>
    -->
    <button onclick="save();">save</button>

	<div id="area"></div>

	<div id="controls">

		<div id="bar" style="display: none !important;">
			<div id="toggle"></div>
			<input type="button" id="saveload" />

			<hr/>

			<input type="button" id="addtable" />
			<input type="button" id="edittable" />
			<input type="button" id="tablekeys" />
			<input type="button" id="removetable" />
			<input type="button" id="aligntables" />
			<input type="button" id="cleartables" />
		
			<hr/>
		
			<input type="button" id="addrow" />
			<input type="button" id="editrow" />
			<input type="button" id="uprow" class="small" /><input type="button" id="downrow" class="small"/>
			<input type="button" id="foreigncreate" />
			<input type="button" id="foreignconnect" />
			<input type="button" id="foreigndisconnect" />
			<input type="button" id="removerow" />
		
			<hr/>
		
			<input type="button" id="options" />
			<a href="https://github.com/ondras/wwwsqldesigner/wiki" target="_blank"><input type="button" id="docs" value="" /></a>
		</div>
	
		<div id="rubberband"></div>

		<div id="minimap" style="display: none !important;"></div>
	
		<div id="background"></div>
	
		<div id="window">
			<div id="windowtitle"><img id="throbber" src="/packages/qinoa/apps/model/sqldesigner/images/throbber.gif" alt="" title=""/></div>
			<div id="windowcontent"></div>
			<input type="button" id="windowok" />
			<input type="button" id="windowcancel" />
		</div>
	</div> <!-- #controls -->
	
	<div id="opts">
		<table>
			<tbody>
				<tr>
					<td>
						* <label id="language" for="optionlocale"></label>
					</td>
					<td>
						<select id="optionlocale"><option></option></select>
					</td>
				</tr>
				<tr>
					<td>
						* <label id="db" for="optiondb"></label> 
					</td>
					<td>
						<select id="optiondb"><option></option></select>
					</td>
				</tr>
				<tr>
					<td>
						<label id="snap" for="optionsnap"></label> 
					</td>
					<td>
						<input type="text" size="4" id="optionsnap" />
						<span class="small" id="optionsnapnotice"></span>
					</td>
				</tr>
				<tr>
					<td>
						<label id="pattern" for="optionpattern"></label> 
					</td>
					<td>
						<input type="text" size="6" id="optionpattern" />
						<span class="small" id="optionpatternnotice"></span>
					</td>
				</tr>
				<tr>
					<td>
						<label id="hide" for="optionhide"></label> 
					</td>
					<td>
						<input type="checkbox" id="optionhide" />
					</td>
				</tr>
				<tr>
					<td>
						* <label id="vector" for="optionvector"></label> 
					</td>
					<td>
						<input type="checkbox" id="optionvector" />
					</td>
				</tr>
				<tr>
					<td>
						* <label id="showsize" for="optionshowsize"></label> 
					</td>
					<td>
						<input type="checkbox" id="optionshowsize" />
					</td>
				</tr>
				<tr>
					<td>
						* <label id="showtype" for="optionshowtype"></label> 
					</td>
					<td>
						<input type="checkbox" id="optionshowtype" />
					</td>
				</tr>
			</tbody>
		</table>

		<hr />

		* <span class="small" id="optionsnotice"></span>
	</div>
	
	<div id="io">
		<table>
			<tbody>
				<tr>
					<td style="width:60%">
						<fieldset>
							<legend id="client"></legend>
							<div id="singlerow">
							<input type="button" id="clientsave" /> 
							<input type="button" id="clientload" />
							</div>
							<div id="singlerow">
							<input type="button" id="clientlocalsave" />
							<input type="button" id="clientlocalload" />
							<input type="button" id="clientlocallist" />
							</div>
							<div id="singlerow">
								<input type="button" id="dropboxsave" /><!-- may get hidden by dropBoxInit() -->
								<input type="button" id="dropboxload" /><!-- may get hidden by dropBoxInit() -->
								<input type="button" id="dropboxlist" /><!-- may get hidden by dropBoxInit() -->
							</div>
							<hr/>
							<input type="button" id="clientsql" />
						</fieldset>
					</td>
					<td style="width:40%">
						<fieldset>
							<legend id="server"></legend>
							<label for="backend" id="backendlabel"></label> <select id="backend"><option></option></select>
							<hr/>
							<input type="button" id="serversave" /> 
							<input type="button" id="quicksave" /> 
							<input type="button" id="serverload" /> 
							<input type="button" id="serverlist" /> 
							<input type="button" id="serverimport" /> 
						</fieldset>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<fieldset>
							<legend id="output"></legend>
							<textarea id="textarea" rows="1" cols="1"></textarea><!--modified by javascript later-->
						</fieldset>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	
	<div id="keys">
		<fieldset>
			<legend id="keyslistlabel"></legend> 
			<select id="keyslist"><option></option></select>
			<input type="button" id="keyadd" />
			<input type="button" id="keyremove" />
		</fieldset>
		<fieldset>
			<legend id="keyedit"></legend>
			<table>
				<tbody>
					<tr>
						<td>
							<label for="keytype" id="keytypelabel"></label>
							<select id="keytype"><option></option></select>
						</td>
						<td></td>
						<td>
							<label for="keyname" id="keynamelabel"></label>
							<input type="text" id="keyname" size="10" />
						</td>
					</tr>
					<tr>
						<td colspan="3"><hr/></td>
					</tr>
					<tr>
						<td>
							<label for="keyfields" id="keyfieldslabel"></label><br/>
							<select id="keyfields" size="5" multiple="multiple"><option></option></select>
						</td>
						<td>
							<input type="button" id="keyleft" value="&lt;&lt;" /><br/>
							<input type="button" id="keyright" value="&gt;&gt;" /><br/>
						</td>
						<td>
							<label for="keyavail" id="keyavaillabel"></label><br/>
							<select id="keyavail" size="5" multiple="multiple"><option></option></select>
						</td>
					</tr>
				</tbody>
			</table>
		</fieldset>
	</div>
	
	<div id="table">
		<table>
			<tbody>
				<tr>
					<td>
						<label id="tablenamelabel" for="tablename"></label>
					</td>
					<td>
						<input id="tablename" type="text" />
					</td>
				</tr>
				<tr>
					<td>
						<label id="tablecommentlabel" for="tablecomment"></label> 
					</td>
					<td>
						<textarea rows="5" cols="40" id="tablecomment"></textarea>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	
	<script type="text/javascript">
		var d = new SQL.Designer();
	</script>
</body>
</html>
