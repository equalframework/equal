/*! 
* qinoa-ui.qSearch - v1.0.0
* https://github.com/cedricfrancoys/qinoa
* Copyright (c) 2015 Cedric Francoys; Licensed GPLv3 */

/**
 * qinoa-ui.qSearch : A plugin generating a list of objects with search options (this plugin is a wrapper for qGrid)
 *
 * Author	: Cedric Francoys
 * Launch	: March 2015
 * Version	: 1.0
 *
 * Licensed under GPL Version 3 license
 * http://www.opensource.org/licenses/gpl-3.0.html
 *
 */
(function($){

$.fn.qSearchGrid = function(conf){
	var default_conf = {
	// mandatory params
		class_name:		'',									// class of the objects to list
		view:			'list.default',						// name of the view to use
        ui:             qinoa.conf.user_lang
	};

	var methods = {
		layout: function($this, conf) {
			var deferred = $.Deferred();

			var schema, fields, lang;
			$({})
			.queue( function (next) { $.when(qinoa.get_schema(conf.class_name)).done(function (result) {schema = result; next();}) })            
            .queue(	function (next) { $.when(qinoa.get_fields(conf.class_name, conf.view)).done(function (result) {fields = result; next(); }) })
			.queue(	function (next) { $.when(qinoa.get_lang(conf.class_name, conf.ui)).done(function (result) {lang = result; next(); }) })
			.queue( function (next) {
				// create inputs for critereas (simple fields only)
				// (we make it very basic for now)
				var $search_criterea = $('<div/>').css('width', '100%');

				$.each(fields, function(field, attributes){
					// copy var attributes to a configuration object
					var config = $.extend({}, attributes);
					if(	($.inArray(schema[field]['type'], qinoa.simple_types) >= 0)
						||
						(schema[field]['type'] == 'function' >= 0 && $.inArray(schema[field]['result_type'], qinoa.simple_types) >= 0 && schema[field]['store'] == true)) {

						var type = (schema[field]['type'] == 'function')?schema[field]['result_type']:schema[field]['type'];
// todo : move to translate method
						var field_label = field;
						if(!$.isEmptyObject(lang) && typeof lang['model'][field] != 'undefined' && typeof lang['model'][field]['label'] != 'undefined') {
							field_label = lang['model'][field]['label'];
						}

						$widget = $('<span/>').qSearchWidget({
						id:		field+(new Date()).getTime(),
						name:	field,
						type:	type
						});

						$search_criterea.append(
							$('<div/>')
							.css({'float': 'left', 'margin-bottom': '2px'})
							.append(
								$('<div/>')
								.append(
									$('<label/>')
									.attr('for', field)
									.css({
										'float': 'left',
										'text-align': 'right',
										'width': '80px',
										'margin-right': '4px'
									})
									.text(field_label)
								)
								.append($widget)
							)
						);

					}
				});
				// create the grid
				var $grid = $('<div/>')
				.qGrid(conf)
				.on('ready', function() {
					// remember the original domain
					var grid_domain_orig = $.extend(true, [], $grid.data('conf').domain);
					// create the search button and the associated action when clicking
					var $search = $('<div/>')
					.append($('<table/>')
							.append($('<tr/>')
									.append($('<td>')
											.attr('width', '90%')
											.append($search_criterea))
									.append($('<td>')
											.append($('<button type="button"/>')
													.css('margin-bottom', '2px')
													.text('search')
													.button()
													.on('click', function(){
														// 1) generate the new domain (array of conditions)
														var grid_conf = $grid.data('conf');
														// reset the domain to its original state
														grid_conf.domain = $.extend(true, [], grid_domain_orig);
														$('input', $search).each(function(){
															var $item = $(this);
															var field = $item.attr('name');
															var value = $item.val();
															if(value.length) {
																// reset the number ofmatching records
																grid_conf.records = '';
																// create the new domain to filter the results of the grid
																type = schema[field]['type'];
																if(schema[field]['type'] == 'function') type = schema[field]['result_type'];
																switch(type) {
																	case 'boolean':
																	case 'integer':
																	case 'many2one':
																	case 'selection':
																	case 'time':
																	case 'timestamp':
																		grid_conf.domain[0].push([ field, '=', value]);
																		break;
																	case 'datetime':
																	case 'date':
																		// may be a date range (separator: '-')
																		var date_array = value.split(" - ");
																		switch(date_array.length) {
																			case 1:
																				// only one date
																				grid_conf.domain[0].push([ field, '=', value ]);
																				break;
																			case 2:
																				// date range
																				grid_conf.domain[0].push([ field, '>=', date_array[0] ]);
																				grid_conf.domain[0].push([ field, '<=', date_array[1] ]);
																				break;
																		}
																		break;
																	case 'string':
																	case 'short_text':
																	case 'text':
																	case 'binary':
																		// note: remember that binary type may hold field translation
																		grid_conf.domain[0].push([ field, 'ilike', '%' + value + '%']);
																		break;
																}
															}
															});

															// 2) force grid to refresh
															$grid.trigger('reload');
														})
												)
										)
								)
						);
					$this.append($search).append($grid);
					// if conf is requested, return grid conf
					$this.data('conf', $grid.data('conf'));
					deferred.resolve();
				});

			});
			return deferred.promise();
		},

		translate: function($this, conf) {
		}
	};

	return this.each(function() {
		return (function ($this, conf) {
			$.when(methods.layout($this, conf))
			.done(function () {
				$this.trigger('ready');
			});

			return $this;
		})($(this), $.extend(true, default_conf, conf));
	});
}
})(jQuery);