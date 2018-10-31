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
* file: packages/core/apps/objects/permissions.php
*
* Allows to manage users and groups access on classes.
*
*/
use config\QNLib;

// the dispatcher (index.php) is in charge of setting the context and should include the easyObject library
defined('__QN_LIB') or die(__FILE__.' cannot be executed directly.');
require_once('../qn.api.php');

QNlib::load_class('html/HtmlWrapper');


$html = new HtmlWrapper();
$html->addCSSFile('packages/core/html/css/easyobject/base.css');
$html->addCSSFile('packages/core/html/css/jquery.ui.grid/jquery.ui.grid.css');
$html->addCSSFile('packages/core/html/css/jquery/base/jquery.ui.easyobject.css');

$html->addJSFile('packages/core/html/js/jquery.min.js');
$html->addJSFile('packages/core/html/js/jquery-ui.min.js');
$html->addJSFile('packages/core/html/js/fckeditor/fckeditor.js');

//$html->addJSFile('packages/core/html/js/easyObject.min.js');
//$html->addJSFile('packages/core/html/js/easyObject.loader.js');

$html->addJSFile('packages/core/html/js/src/md5.js');
$html->addJSFile('packages/core/html/js/src/jquery.simpletip-1.3.1.js');
$html->addJSFile('packages/core/html/js/src/jquery.noselect-1.1.js');
$html->addJSFile('packages/core/html/js/src/jquery-ui.timepicker-1.0.1.js');
$html->addJSFile('packages/core/html/js/src/easyObject.utils.js');
$html->addJSFile('packages/core/html/js/src/easyObject.grid.js');
$html->addJSFile('packages/core/html/js/src/easyObject.tree.js');
$html->addJSFile('packages/core/html/js/src/easyObject.dropdownlist.js');
$html->addJSFile('packages/core/html/js/src/easyObject.choice.js');
$html->addJSFile('packages/core/html/js/src/easyObject.editable.js');
$html->addJSFile('packages/core/html/js/src/easyObject.form.js');
$html->addJSFile('packages/core/html/js/src/easyObject.api.js');
$html->addJSFile('packages/core/html/js/src/jquery.inputmask.bundle.js');



$js_packages = function () {
	$packages_directory = 'packages';
	$packages_list = array();
	if(is_dir($packages_directory) && ($list = scandir($packages_directory))) {
		foreach($list as $node) if (!in_array($node, array('.', '..')) && is_dir($packages_directory.'/'.$node)) $packages_list[] = "'$node'";
	}
	return '['.implode(',', $packages_list).']';
};

$html->addScript("

$(document).ready(function() {
	// vars
	var packages = {$js_packages()};
	var selection = $('body');

	// layout
	$('body')
	.append($('<div/>').attr('id', 'menu').css({'height': $(window).height()+'px', 'float':'left', 'width':'200px'})
			.append($('<label/>').css({'margin': '4px', 'font-weight': 'bold', 'display': 'block'}).html('Package: '))
			.append($('<select/>').attr('id', 'package').css({'margin': '4px'}))
			.append($('<label/>').css({'margin': '4px', 'font-weight': 'bold', 'display': 'block'}).html('Classes: '))
    		.append($('<div/>').attr('id', 'classes').css({'margin': '4px', 'height': ($(window).height()-100)+'px', 'width': '200px', 'overflow': 'auto'}))
	)
    .append($('<div/>').attr('id', 'main').css({'display': 'table', 'background-color': 'white', 'height': $(window).height()+'px', 'float':'left', 'width': ($(window).width()-240)+'px', 'padding': '10px'}));

	// feed
	$.each(packages, function(i,item){
		$('#package').append($('<option/>').val(item).html(item));
	});

	// events
	$('#package').on('change', function() {
		$.getJSON('index.php?get=core_packages_listing&package='+$(this).val(), function (json_data) {
				$('#classes').empty()
				$.each(json_data, function(i, item){
					$('#classes').append($('<span/>').css({'display': 'block', 'cursor': 'pointer'}).append(item)
						.click(function() {
							selection.removeClass('selected');
							selection = $(this);
							selection.addClass('selected');

							var predefined = {};
							predefined['object_class'] = $('#package').val()+'\\\\'+$(this).html();
							form = easyObject.UI.form({
														class_name: 'core\\\\Permission',
														object_id: 0,
														lang: easyObject.conf.user_lang,
														predefined: predefined
										});
							$('#main')
							.empty()
							.append(form);
						})
					);
				});
		});
	});

	// init
	$('#package').trigger('change');
});
");

print($html);