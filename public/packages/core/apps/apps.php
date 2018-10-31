<?php
/**
*    This file is part of the easyObject project.
*    http://www.cedricfrancoys.be/easyobject
*
*    Copyright (C) 2012  Cedric Francoys
*
*    This program is free software: you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation, either version 3 of the License, or
*    (at your option) any later version.
*
*    This program is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License
*    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/*
* file: packages/core/apps/apps.php
*
* App for using utility plugins
*
*/

// the dispatcher (index.php) is in charge of setting the context and should include the easyObject library
defined('__QN_LIB') or die(__FILE__.' cannot be executed directly.');
require_once('../qn.api.php');

load_class('utils/HtmlWrapper');


$html = new HtmlWrapper();
$html->addCSSFile('packages/core/html/css/jquery-ui.min.css');
$html->addCSSFile('packages/core/html/css/qinoa-ui.min.css');

$html->addJSFile('packages/core/html/js/jquery.min.js');
$html->addJSFile('packages/core/html/js/jquery-ui.min.js');
$html->addJSFile('packages/core/html/js/qinoa.api.min.js');
$html->addJSFile('packages/core/html/js/qinoa-ui.min.js');

$js_packages = function () {
	return json_encode(get_packages());
};


function recurse_dir($directory, $parent_name='') {
    $result = array();
    if( is_dir($directory) && ($list = scandir($directory)) ) {
        foreach($list as $node) {
            if(is_dir($directory.'/'.$node) && !in_array($node, array('.', '..'))) {
                $result = array_merge($result, recurse_dir($directory.'/'.$node, (strlen($parent_name)?$parent_name.'_'.$node:$node)));
            }
            else if(!is_dir($directory.'/'.$node) && !in_array($node, array('.', '..'))) $result[] = (strlen($parent_name)?$parent_name.'_':'').(explode('.', $node)[0]);
        }
    }
    return $result;
}

$php_scripts = function ($directory) {
	$result = array();
	$packages_list = get_packages();
	foreach($packages_list as $package) {
        $result[$package] = recurse_dir("packages/$package/$directory");
	}
	return json_encode($result, JSON_FORCE_OBJECT);
};


$html->addScript("
$(document).ready(function() {
	// vars
	var packages = {$js_packages()};
	var apps = {$php_scripts('apps')};
	var actions = {$php_scripts('actions')};
	var datas = {$php_scripts('data')};	
	// layout
	$('body')
	.append($('<div/>').attr('id', 'menu').css({'height': $(window).height()+'px', 'float':'left', 'width':'200px'})
            .append($('<label/>').css({'margin': '4px', 'font-weight': 'bold', 'display': 'block'}).html('Package: '))
    		.append($('<select/>').attr('id', 'package').css({'margin': '4px'}))
            
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

	// feed
	$.each(packages, function(i,item){
		$('#package').append($('<option/>').val(item).html(item));
	});

	// events
	$('#package').on('change', function() {
		$('#app').empty();		
		$.each(apps[$('#package').val()], function(i,item){
			$('#app').append($('<option/>').val(item).html(item));
		});
        
		$('#action').empty();
        if(typeof actions[$('#package').val()] != 'undefined') {        
            $.each(actions[$('#package').val()], function(i,item){
                $('#action').append($('<option/>').val(item).html(item));
            });
        }
        
		$('#data').empty();
        if(typeof datas[$('#package').val()] != 'undefined') {         
            $.each(datas[$('#package').val()], function(i,item){
                $('#data').append($('<option/>').val(item).html(item));
            });
        }
	});
    
	function request_script(type, name) {
		$.getJSON('index.php?'+type+'='+$('#package').val()+'_'+name+'&announce=1', function (json_data) {
				$('#main').empty();
				$('#main').append($('<div/>').css({'font-weight': 'bold', 'margin-bottom': '20px'}).append('Plugin description :'));
				$('#main').append($('<div/>').attr('id', 'result').css({'width': '100%', 'height': '400px'}));
				if(typeof json_data.announcement == 'object') {
					// we received an announcement
					$('#result').append(json_data.announcement.description+'<br /><br />');
					$('#result').append($('<div/>').css({'font-weight': 'bold', 'margin-bottom': '20px'}).append('Plugin params :'));					
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
	$('#package').trigger('change');
	
});
");

print($html);