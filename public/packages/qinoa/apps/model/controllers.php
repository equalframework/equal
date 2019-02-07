<?php
/*
    This file is part of the qinoa framework <http://www.github.com/cedricfrancoys/qinoa>
    Some Rights Reserved, Cedric Francoys, 2018, Yegen
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
use qinoa\html\HtmlWrapper;

list($params, $providers) = announce([
    'description'   => 'UI for browsing controllers and their defintion amongst packages',
    'params'        => [
        'package'   => [
            'type'      => 'string',
            'required'  => true
        ]
    ],
    'response'      => [
        'content-type'  => 'text/html',
        'charset'       => 'UTF-8'
    ],
    'constants'     => [],
    'providers'     => ['context']
]);


$html = new HtmlWrapper();
$html->addCSSFile('packages/qinoa/assets/css/jquery-ui.min.css');
$html->addCSSFile('packages/qinoa/assets/css/bootstrap.css');

$html->addJSFile('packages/qinoa/assets/js/jquery.min.js');
$html->addJSFile('packages/qinoa/assets/js/jquery-ui.min.js');



$json = run('get', 'qinoa_config_controllers', ['package' => $params['package']]);

$data = json_decode($json, true);

$scripts_apps = json_encode([$params['package'] => $data['apps']], JSON_FORCE_OBJECT);
$scripts_actions = json_encode([$params['package'] => $data['actions']], JSON_FORCE_OBJECT);
$scripts_datas = json_encode([$params['package'] => $data['data']], JSON_FORCE_OBJECT);


$html->addScript("
$(document).ready(function() {
	// vars
	var model = '{$params['package']}';
	var apps = {$scripts_apps};
	var actions = {$scripts_actions};
	var datas = {$scripts_datas};	
	// layout
	$('body')
	.append($('<div/>').attr('id', 'menu').css({'height': $(window).height()+'px', 'float':'left', 'width':'200px'}) 		
            
			.append($('<label/>').css({'margin': '4px', 'font-weight': 'bold', 'display': 'block'}).html('Apps: '))
            .append($('<select/>').attr('id', 'app').css({'margin': '4px', 'width': '150px'}))    		
    		.append($('<button type=\"button\"/>').attr({'id': 'submit_app'}).html('ok'))

			.append($('<label/>').css({'margin': '4px', 'font-weight': 'bold', 'display': 'block'}).html('Action handlers: '))
            .append($('<select/>').attr('id', 'action').css({'margin': '4px', 'width': '150px'}))    		
    		.append($('<button type=\"button\"/>').attr({'id': 'submit_action'}).html('ok'))

			.append($('<label/>').css({'margin': '4px', 'font-weight': 'bold', 'display': 'block'}).html('Data providers: '))            
            .append($('<select/>').attr('id', 'data').css({'margin': '4px', 'width': '150px'}))    		
    		.append($('<button type=\"button\"/>').attr({'id': 'submit_data'}).html('ok'))            
            
	)
    .append($('<div/>').attr('id', 'main').css({'display': 'table', 'background-color': 'white', 'height': $(window).height()+'px', 'float':'left', 'width': ($(window).width()-240)+'px', 'padding': '10px'}));


    
	function request_script(type, name) {
		$.getJSON('index.php?'+type+'='+model+'_'+name+'&announce=1', function (json_data) {
				$('#main').empty();
				$('#main').append($('<div/>').css({'font-weight': 'bold', 'margin-bottom': '20px'}).append('Controller description :'));
				$('#main').append($('<div/>').attr('id', 'result').css({'width': '100%', 'height': '400px'}));
				if(typeof json_data.announcement == 'object') {
					// we received an announcement
					$('#result').append(json_data.announcement.description+'<br /><br />');
					$('#result').append($('<div/>').css({'font-weight': 'bold', 'margin-bottom': '20px'}).append('Controller parameters :'));					
					$('#result').append($('<table/>').attr('id', 'params').css({'border': 'solid 1px grey'})
						.append($('<tr/>')
							.append($('<th/>').css({'padding':'10px','text-align': 'left'}).text('name'))
							.append($('<th/>').css({'padding':'10px','text-align': 'left'}).text('type'))
							.append($('<th/>').css({'padding':'10px','text-align': 'left'}).text('description'))
						)
					);
					$.each(json_data.announcement.params, function(i, item){
						$('#params').append($('<tr/>')
							.append($('<td/>').css({'padding':'10px','width':'150px', 'font-style':'italic'}).text(i))
							.append($('<td/>').css({'padding':'10px'}).text(item.type))
							.append($('<td/>').css({'padding':'10px'}).text(item.description))
						);
					});
				}

		});	
	}
    
	$('#submit_app').click(function() {
        request_script('show', $('#app').val());
	});
    
	$('#submit_action').click(function() {
        request_script('do', $('#action').val());
	});
    
    $('#submit_data').click(function() {
        request_script('get', $('#data').val());
	});
	
	// init
    (function () {
        if(typeof apps[model] != 'undefined') {
            $.each(apps[model], function(i,item){
                $('#app').append($('<option/>').val(item).html(item));
            });
        }

        if(typeof actions[model] != 'undefined') {
            $.each(actions[model], function(i,item){
                $('#action').append($('<option/>').val(item).html(item));
            });
        }
        

        if(typeof datas[model] != 'undefined') {         
            $.each(datas[model], function(i,item){
                $('#data').append($('<option/>').val(item).html(item));
            });
        }        
    })();
	
});
");

$providers['context']->httpResponse()->body($html)->send();