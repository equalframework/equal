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
* file: packages/core/apps/utils.php
*
* App for using utility plugins
*
*/

// the dispatcher (index.php) is in charge of setting the context and should include the easyObject library
defined('__QN_LIB') or die(__FILE__.' cannot be executed directly.');
require_once('../qn.api.php');

use easyobject\orm\I18n as I18n;
use html\HtmlWrapper as HtmlWrapper;


$html = new HtmlWrapper();
$html->addCSSFile('packages/core/html/css/jquery-ui.min.css');
$html->addCSSFile('packages/core/html/css/qinoa-ui.min.css');

$html->addJSFile('packages/core/html/js/jquery.min.js');
$html->addJSFile('packages/core/html/js/jquery-ui.min.js');
$html->addJSFile('packages/core/html/js/qinoa.api.min.js');
$html->addJSFile('packages/core/html/js/qinoa-ui.min.js');


$js_plugins = function () {
	$plugins_directory = 'packages/utils/data';
	$plugins_list = array();
	if(is_dir($plugins_directory) && ($list = scandir($plugins_directory))) {
		foreach($list as $node) {
			if (in_array($node, array('.', '..')) || !is_file($plugins_directory.'/'.$node)) continue;
			$parts = explode('.', $node);
			if(!count($parts)) continue;
			$ext = strtolower($parts[count($parts)-1]);
			if($ext != "php") continue;
			$plugins_list[] = "'{$parts[0]}'";
		}
	}
	return '['.implode(',', $plugins_list).']';
};

$html->addScript("
$(document).ready(function() {
	// vars
	var plugins = {$js_plugins()};

	// layout
	$('body')
	.append($('<div/>').attr('id', 'menu').css({'height': $(window).height()+'px', 'float':'left', 'width':'200px'})
			.append($('<label/>').css({'margin': '4px', 'font-weight': 'bold', 'display': 'block'}).html('Plugin: '))
    		.append($('<select/>').attr('id', 'plugin').css({'margin': '4px'}))
    		.append($('<button type=\"button\"/>').attr({'id': 'submit'}).html('ok'))
	)
    .append($('<div/>').attr('id', 'main').css({'display': 'table', 'background-color': 'white', 'height': $(window).height()+'px', 'float':'left', 'width': ($(window).width()-240)+'px', 'padding': '10px'}));

	// feed
	$.each(plugins, function(i,item){
		$('#plugin').append($('<option/>').val(item).html(item));
	});

	// events
	$('#submit').click(function() {
		$.getJSON('index.php?get=utils_'+$('#plugin').val()+'&'+$('#params').serialize(), function (json_data) {
				$('#main').empty();
				$('#main').append($('<div/>').css({'font-weight': 'bold', 'margin-bottom': '20px'}).append('Plugin results :'));
				$('#main').append($('<div/>').attr('id', 'result').css({'width': '100%', 'height': '400px'}));
				if(typeof json_data.result == 'object') {
					// result is an object					
					$('#result').append($('<textarea/>').attr('id', 'output').css({'width': '100%', 'height': '100%'}));
					$.each(json_data.result, function(i, item){
						$('#output').append(item+'\\r\\n');
					});
				}
				else {
					// result is an error code
					if(json_data.result == -2) {
						// MISSING_PARAM
						$('#result').append($('<div/>').css({'font-size': '90%', 'margin-bottom': '30px'}).html('This script requires one or more paramters.<br />(Seeing this message after sumbitting a form means that at least one parameter is missing or has invalid value.)'));
						// try to build a form matching requirements
						if(typeof json_data.announcement == 'object') {
							// we received an announcement
							$('#result').append(json_data.announcement.description+'<br /><br />');
							$('#result').append($('<form/>').attr('id', 'params').append($('<table/>').addClass('widgets').css({'border': 'solid 1px grey'})));
							$.each(json_data.announcement.params, function(i, item){
								var conf = {
									type: item.type,
									name: i
								};
								if(typeof item.selection != 'undefined') {
									conf.type = 'selection';
									conf.selection = item.selection;
								}
								$('#params .widgets').append($('<tr/>')
									.append($('<td/>').css({'padding':'10px','width':'150px', 'font-weight':'bold'}).text(i+':'))
									.append($('<td/>').css({'padding':'10px',}).editable(conf))
									.append($('<td/>').css({'padding':'10px',}).html(item.description))
								);
							});
							$('#params .widgets').append($('<tr/>')
								.append($('<td/>').attr('colspan', '3').css({'padding':'10px','text-align': 'right'})
									.append(
										$('<button type=\"button\"/>').html('submit').on('click', function() {
										$('#submit').click();
										})
									)
								)
							);
						}
						else $('#result').append($('<div/>').html('No announcement received from the script.'));
						
						/*						
						if(typeof json_data.announcement == 'object') {
							$('#result').append(JSON.stringify(json_data.announcement, null, 4));
						}
						*/
					}
				}
		});
	});
});
");

print($html);
