/**
 * qinoa-ui.qGrid : Plugin for generating Grid view controls
 *
 * Author	: Cedric Francoys
 * Launch	: March 2015
 * Version	: 1.0
 *
 * Licensed under GPL Version 3 license
 * http://www.opensource.org/licenses/gpl-3.0.html
 *
 */
(function($, qinoa){

$.fn.qGrid = function(conf){

	var default_conf = {
	// internal params (
		columns:		[],									// holds, for each filed its name, display name and width
		fields:			[],									// fields of the specified class appearing in the view (used in 'feed' method)
		more:			[],									// ids to include to the domain
		less:			[],									// ids to exclude from the domain
		selection:		{},									// list of selected items (support seleciton upon several pages)
	// mandatory params
		class_name:		'',									// class of the objects to list
		view:			'list.default',						// name of the view to use
	// optional params
		multiple:		true,
		rp: 			20,									// number of results per page
		rp_choices:		[5, 10, 20, 40, 80, 160],			// allowed per-page values
		page:			1,									// default page to display
		total:			0,									// total number of pages (records/rp)
		records:		0,									// number of records matching the domain
		sortname:		'id',								// default field on which perform sort
		sortorder:		'asc',								// order for sorting
		domain:			[[]],								// domain (i.e. clauses to limit the results)
		lang:			qinoa.conf.content_lang,			// language in which request the content to server
		ui:				qinoa.conf.user_lang,				// language in which display UI items
		views :		{
					edit: 'form.default',
					add:  'list.default'
					},
		buttons:	{
					edit:	{
							text: 'edit',						// alternate text for the edit button
							icon: 'fa fa-pencil'				// icon for the edition button
							},
					del:
							{
							text: 'delete',						// alternate text for the delete button
							icon: 'fa fa-trash'				// icon for the delete button
							},
					add:
							{
							text: 'create new',					// alternate text for the add button
							icon: 'fa fa-file-o'			// icon for the add button
							}
					},
		actions:	{}											// functions related to buttons (default defined in listen method)
	};

	/**
	* Retrieves the html source of the requested view
	*
	*/
	load_view = function($grid, conf) {
		var deferred = $.Deferred();
		// init internal (we might have received values from conf of a parent widget)
		conf.columns	= [];
		conf.fields		= [];
		conf.more		= [];
		conf.less		= [];
		conf.selection	= {};
		
		var schema, fields;
		var $view;
		$({})
		.queue( function (next) { 
			$.when(qinoa.get_schema(conf.class_name))
			.done(function (result)	{ schema = result; next();})
			.fail(function (code)	{ deferred.reject(code); });
		})
		.queue(	function (next) { 
			$.when(qinoa.get_view(conf.class_name, conf.view))
			.done(function (result)	{ $view = $(result); next();})
			.fail(function (code)	{ deferred.reject(code); });
		})
		.queue( function (next) {
			$.when(qinoa.get_fields(conf.class_name, conf.view))
			.done(function (result)	{ fields = result; next();})
			.fail(function (code)	{ deferred.reject(code); });
		})
		.queue( function (next) {
			// extend the configuration object with view attributes, if any
			// note: attributes we should expect are: 'domain', 'sortname', 'sortorder'
			// note: this means that view attributes overwrites any conf-defined attributes
			$.each($view[0].attributes, function(i, attr) {
				switch(attr.name) {
				case 'domain':
					// check syntax validity
					try {
						conf.domain = JSON.parse(attr.value.replace(/\'/g, '"'));
					} catch (e) {
						qinoa.console.log("Error raised in qinoa-ui.qGrid::load_view ("+conf.view+"): attribute 'domain' has invalid syntax");
					}
					break;
				case 'views':
					try {
						conf.views = JSON.parse(attr.value.replace(/\'/g, '"'));
					} catch(e) {
						qinoa.console.log("Error raised in qinoa-ui.qGrid::load_view ("+conf.view+"): attribute 'views' has invalid syntax");
					}
					break;
				default:
					conf[attr.name] = attr.value;
					break;
				}
			});
			var fieldsQueue = $({});
			// extract the fields from the view and generate the columns model			
			$.each(fields, function (field, attributes) {
				fieldsQueue.queue( function (next) {
					// build the columns and fields arrays
					var name = field;
					var width = attributes['width'];
					if(parseInt(width) > 0) {
						var column = $.extend({
							name: name,
							fields:	[]				// array holding names of fields whose value is required by widgets related to the column
						}, attributes);
						column.fields.push(field);
						conf.fields.push(field);						
						if(typeof schema[field]['foreign_object'] != 'undefined' && attributes['view'] != undefined) {
							// load specified view and add related fields
							$.when(qinoa.get_fields(schema[field]['foreign_object'], attributes['view']))
							.done(function (result) {
								$.each(result, function (field, attributes) {
									var related = name+'.'+field;
									conf.fields.push(related);
									column.fields.push(related);
								});
								conf.columns.push(column);
								next();
							})
							.fail(function (code) { 
								// deferred.reject(code); 
								console.log('failed');
							});
						}
						else {
							conf.columns.push(column);
							next();
						}
					}
				});
			});
			fieldsQueue.queue(function (next) {
				deferred.resolve();
			});
		});
		return deferred.promise();
	};

	render = function($grid, conf) {
		var deferred = $.Deferred();
		// create table
		var $table = $('<table/>').addClass('grid_table ui-widget-content');
		var $thead = $('<thead/>').addClass('grid_table_head ui-widget-content');
		var $tbody = $('<tbody/>').addClass('grid_table_body ui-widget-content');

		// instanciate header row
		var $hrow = $('<tr/>').addClass('grid_table_head_row');

		// create the first column, containing the 'select-all' checkbox
		var $cell = $('<th/>')
		.addClass('ui-state-default')
		.css({'width': '30px'})
		.append(
			$('<div/>').css({'width': '30px'})
			.append(
				$('<input type="checkbox" />')
				.addClass('checkbox')
				.css({'width': '20px'})
				// click triggers de/select all
				.on('click', function() {
					var checked = this.checked;
					$("input:checkbox", $tbody).each(function(i, elem) {
						var $parent = $(this).parents('tr.grid_table_body_row').first();
						var id = $parent.attr('id');
						if(checked) {
							$parent.addClass('ui-state-active');
							elem.checked = true;
							conf.selection[id] = true;
						}
						else {
							$parent.removeClass('ui-state-active');
							elem.checked = false;
							delete conf.selection[id];
						}

					});
				})
			)
		).appendTo($hrow);

		if(!conf.multiple) $('input', $cell).attr('disabled', 'disabled');

		// create other columns, based on the columns given in the configuration
		$.each(conf.columns, function(i, column) {
			$cell = $('<th/>').attr('name', column.id)
			.addClass('ui-state-default')
			.css({'width': column.width, 'text-align': 'left'})
			.append($('<div/>').append($('<label/>').attr('for', column.id)))
			.append($('<span/>').addClass('ui-icon'))
			.hover(
				/** The div style attr 'asc' or 'desc' is for the display of the arrow
				  * the th style attr 'asc' or 'desc' is to memorize the current order
				  * so, when present, both attributes should always be inverted
				  */
				function() {
					// set hover
					$this = $(this).addClass('ui-state-hover');
					$div = $('div', $this);
					$span = $('span', $this);
					if($('.sorted', $thead).attr('name') == $this.attr('name') && conf.sortorder == 'asc') {
						$div.removeClass('asc').addClass('desc');
						$span.removeClass('ui-icon-triangle-1-n').addClass('ui-icon-triangle-1-s');
					}
					else {
						$div.removeClass('desc').addClass('asc');
						$span.removeClass('ui-icon-triangle-1-s').addClass('ui-icon-triangle-1-n');
					}
				},
				function() {
					// unset hover
					$this = $(this).removeClass('ui-state-hover');
					$div = $('div', $this);
					$span = $('span', $this);
					$div.removeClass('asc').removeClass('desc');
					$span.removeClass('ui-icon-triangle-1-n').removeClass('ui-icon-triangle-1-s');
					if($('.sorted', $thead).attr('name') == $this.attr('name')) {
						if($this.hasClass('asc')) {
							$div.addClass('asc');
							$span.addClass('ui-icon-triangle-1-n');
						}
						else {
							$div.addClass('desc');
							$span.addClass('ui-icon-triangle-1-s');
						}
					}
			})
			.on('click', function() {
					// change sortname and/or sortorder
					$this = $(this);
					$sorted = $('.sorted', $thead);
					$div = $('div', $this);
					$span = $('span', $this);
					if($sorted.attr('name') == $this.attr('name')) {
						if($div.hasClass('asc')) {
							$div.removeClass('asc').addClass('desc');
							$span.removeClass('ui-icon-triangle-1-n').addClass('ui-icon-triangle-1-s');
							$this.removeClass('desc').addClass('asc');
							conf.sortorder = 'asc';
						}
						else {
							$div.removeClass('desc').addClass('asc');
							$span.removeClass('ui-icon-triangle-1-s').addClass('ui-icon-triangle-1-n');
							$this.removeClass('asc').addClass('desc');
							conf.sortorder = 'desc';
						}
					}
					else {
						$this.addClass('sorted').addClass('asc');
						$div.removeClass('asc').addClass('desc');
						$span.removeClass('ui-icon-triangle-1-n').addClass('ui-icon-triangle-1-s');
						$sorted.removeClass('sorted asc desc');
						$('div', $sorted).removeClass('asc desc');
						$('span', $sorted).removeClass('ui-icon-triangle-1-n ui-icon-triangle-1-s');
						conf.sortorder = 'asc';
					}
					conf.sortname = $this.attr('name');
					// uncheck selection box
					$("input:checkbox", $thead)[0].checked = false;
					// refresh list
					feed($grid, conf);
				}
			);
			if(column.id == conf.sortname) {
				$cell.addClass('sorted').addClass(conf.sortorder);
				$('div', $cell).addClass(conf.sortorder);
			}
			$hrow.append($cell);
		});

		$grid.addClass('ui-grid ui-front ui-widget ui-widget-content ui-corner-all').append($table.append($thead.append($hrow)).append($tbody));
		deferred.resolve();
		return deferred.promise();
	};



	/**
	* translate terms of the form
	* into the lang specified in the configuration object
	*/
	translate = function($grid, conf) {
		var deferred = $.Deferred();
		var schema, lang;
		$({})
		.queue( function (next) { $.when(qinoa.get_schema(conf.class_name)).done(function (result) {schema = result; next();}) })
		.queue(	function (next) { $.when(qinoa.get_lang(conf.class_name, conf.ui)).done(function (result) {lang = result; next(); }) })
		.queue( function (next) {
			if(typeof lang != 'object' || $.isEmptyObject(lang)) {
				// 1) stand-alone labels, legends, buttons (refering to the current view)
				$('label[name],legend[name],button[name]', $grid).each(function() {
					$(this).text(ucfirst($(this).attr('name')));
				});
				// 2) field labels
				$('label[for]', $grid).each(function() {
					$(this).text(ucfirst($(this).attr('for')));
				});
			}
			else {
				// 1) stand-alone labels, legends, buttons (refering to the current view)
				$('label[name],legend[name],button[name]', $grid).each(function() {
					var name = $(this).attr('name');
					if(typeof name != 'undefined') {
						if(typeof lang['view'][name] != 'undefined') {
							var value = lang['view'][name]['label'];
							$(this).text(value);
						}
						else $(this).text(ucfirst(name));
					}
				});
				// 2) field labels
				$('label[for]', $grid).each(function() {
					var value;
					var field = $(this).attr('for');
					if(field != undefined) {
						if(typeof lang['model'][field] != 'undefined' && typeof lang['model'][field]['label'] != 'undefined') {
							$(this).text(lang['model'][field]['label']);
							if(typeof lang['model'][field]['help'] != 'undefined') {
								$(this).append(
									$('<sup/>')
									.attr('title', lang['model'][field]['help'].replace(/\n/g,'<br />'))
									.addClass('help').text('?').tooltip()
								);
							}
						}
						else $(this).text(ucfirst(field));
					}
				});
			}
			deferred.resolve();			
		});
		return deferred.promise();
	};

	init = function($grid, conf) {
		var deferred = $.Deferred();
		$.when(qinoa.search(conf.class_name, conf.domain, conf.sortname, conf.sortorder, 0, 0, conf.lang))
		.done(function(ids) {
			conf.records = Object.keys(ids).length;
			conf.total = Math.ceil(conf.records/conf.rp);
			$.when(feed($grid, conf))
			.done(function() { deferred.resolve(); })
			.fail(function (code) { console.log('feed failed'); });
		})
		.fail(function (code) {
			qinoa.console.log('Error raised in qinoa-ui.qGrid::init by qinoa.search(): '+qinoa.error_codes[code]);
            deferred.reject(code);
		});
		return deferred.promise();
	};

	feed = function($grid, conf) {
		var deferred = $.Deferred();
		// get body and display the loader
		$tbody = $('.grid_table_body', $grid);
		qinoa.loader.show($grid);
		// create a temporary domain with the config domain and, if necessary, do some changes to it
		var domain = $.extend(true, [], conf.domain);
		// add an inclusive OR clause
		if(conf.more.length) domain.push([['id','in', conf.more]]);
		// add an exclusive AND clause
		if(conf.less.length) domain[0].push(['id','not in', conf.less]);

		var start = (conf.page-1) * conf.rp;

		var schema;
		$({})
		.queue( function (next) { $.when(qinoa.get_schema(conf.class_name)).done(function (result) {schema = result; next();}) })
		.queue(	function (next) {  
			$.when(qinoa.find(conf.class_name, conf.fields, domain, conf.sortname, conf.sortorder, start, conf.rp, conf.lang))
			.done(function (data) {
				// remove previous content
				$tbody.empty();

				$.each(data, function(i, values) {
					// make sure id is numeric
					var id = values.id;
					$row =
					$('<tr/>')
					.addClass('grid_table_body_row ui-state-default')
					.attr('id', id)
					.append(
						$('<td/>')
						.addClass('grid_table_body_row_checkbox')
						.append(
							$('<input type="checkbox" />')
							.addClass('checkbox')
							.css({'width': '20px'})
	//						.on('dblclick', function() {conf.edit.func($grid, id);})
							.on('click', function () {
								var $parent = $(this).parents('tr.grid_table_body_row').first();
								var id = $parent.attr('id');
								if(this.checked) {
									if(!conf.multiple) {
										// remove previous selection, if any
										$('.grid_table_body_row_checkbox > input', $grid).prop('checked', false );
										$('.grid_table_body_row', $grid).removeClass('ui-state-active');
										conf.selection = {};
									}
									this.checked = true;
									conf.selection[id] = true;
								}
								else delete conf.selection[id];
								$parent.toggleClass('ui-state-active');
							})
						)
					)
					.toggleClass('erow', i%2 == 1);
					$.each(conf.columns, function(j, column) {
//						$row.append($('<td/>').text(values[column.id]));

						var value = {};
						// export fields required by the widget
						$.each(column.fields, function (i, field) {
							value[field] = values[field];
						});					
						var type = (schema[column.id]['type'] == 'function')?schema[column.id]['result_type']:schema[column.id]['type'];
						var cell_conf = $.extend({
							mode:	'view',
							type:	(typeof column.widget != 'undefined')?column.widget:type,
							value:	value,
							lang:	conf.lang
						}, column);	
						if(cell_conf.widget != undefined) cell_conf.type = cell_conf.widget;
						if(typeof schema[column.id]['foreign_object'] != 'undefined') cell_conf.class_name = schema[column.id]['foreign_object'];

//						console.log(cell_conf);
				
						$row.append($('<td/>').append(
						$('<div/>').qGridCell(cell_conf)					
						));

					});
					// select items present in current selection
					if(typeof conf.selection[id] != 'undefined') {
						$('.checkbox', $row).prop('checked', true);
						$row.toggleClass('ui-state-focus');
					}
					$tbody.append($row);
				});

				// add pager at the top and bottom of the grid
				$('.ui-grid-pager', $grid).remove();
				$('.ui-grid-footer', $grid).remove();


				$grid.prepend(pager($grid, conf).addClass('ui-corner-top'));
				$grid.append(pager($grid, conf));
				$grid.append(footer($grid, conf).addClass('ui-corner-bottom'));

				qinoa.loader.hide($grid);
				deferred.resolve();
			})
			.fail(function (code) {
				qinoa.console.log('Error raised in qinoa-ui.qGrid::feed by qinoa.find(): '+qinoa.error_codes[code]);
			});
		});		
		return deferred.promise();
	};


	pager = function($grid, conf) {
		var $separator = $('<span/>').addClass('separator').text(' | ');

		
		var $buttons = $('<div/>').css({'left': '5px'});
		$.each(conf.buttons, function (id, button) {
			if(!$.isEmptyObject(button)) {
				$('<span/>')
				.attr('title', button.text)
				.button({icons:{primary:button.icon}, text: false})
				.on('click', function() {
					if(typeof conf.actions[id] == 'function') conf.actions[id]($grid, conf);
				})
				.appendTo($buttons);
			}
		});


		var $results =
		$('<div/>')
		.addClass('ui-front')
		.css('right', '10px')
		// current view info
		.append(
			$('<span/>')
			.append(function(index, html) {
				var start = (conf.page-1) * conf.rp;
				if(start > 0) start++;
				var end = Math.min(start + parseInt(conf.rp) - 1, conf.records);
				return 'Results ' + start + ' - ' + end + ' of ' + conf.records;
			})
		)
		// separator
		.append($separator.clone())
		.append($('<span/>').html('Show&nbsp;&nbsp;'));
		// number of results selection box
		$select = $('<select/>')
		.css('width', '45px');
		$.each(conf.rp_choices, function(i, val) {
			$option = $('<option/>').attr({'value':val}).html(val);
			if(conf.rp == val) {
				$option.prop('selected', true);
			}
			$option.appendTo($select);
		});								
		$select.appendTo($results).selectmenu({
			change: function( event, ui ) {
				conf.rp = this.value;
				conf.page = 1;
				conf.total = Math.ceil(conf.records/conf.rp);
				$grid.trigger('reload');					
			}
		});

		
		var $navigator = 
		$('<div/>')
		.css({'left': '50%', 'margin-left': '-190px'})
		// first page button
		.append(
			$('<span/>')
			.attr('title', 'first')
			.button({icons:{primary:'ui-icon-seek-start'}, text: false})
			.on('click', function() {
				var first = 1;
				if(conf.page != first) {
					conf.page = first;
					$grid.trigger('reload');
				}
			})
		)
		// previous page button
		.append(
			$('<span/>')
			.attr('title', 'prev')
			.button({icons:{primary:'ui-icon-seek-prev'}, text: false})
			.on('click', function() {
				var previous = Math.max(parseInt(conf.page)-1, 1);
				if(conf.page != previous) {
					conf.page = previous;
					$grid.trigger('reload');
				}
			})
		)
		// separator
		.append($separator.clone())
		// current page among total number of result pages
		.append($('<span/>').append('Page ' + conf.page + ' of '+ conf.total))
		// separator
		.append($separator.clone())
		// next page button
		.append(
			$('<span/>')
			.attr('title', 'next')
			.button({icons:{primary:'ui-icon-seek-next'}, text: false})
			.on('click', function() {
				var next = Math.min(parseInt(conf.page)+1, conf.total);
				if(conf.page != next) {
					conf.page = next;
					$grid.trigger('reload');
				}
			})
		)
		// last page button
		.append(
			$('<span/>')
			.attr('title', 'last')
			.button({icons:{primary:'ui-icon-seek-end'}, text: false})
			.on('click', function() {
				var last = conf.total
				if(conf.page != last) {
					conf.page = last;
					$grid.trigger('reload');
				}
			})
		);


		// create pager
		return $('<div/>')
		.addClass('ui-grid-pager ui-widget-header ui-front')
		// 1) action buttons
		.append($buttons)
		// 2) results & info
		.append($results)
		// 3) page navigator
		.append($navigator);
	};

	footer = function($grid, conf) {
		var params = {
			show: 'core_objects_view',
			view: conf.view_name,
			object_class: conf.class_name,
			domain: conf.domain,
			rp: conf.rp,
			page: conf.page,
			sortname: conf.sortname,
			sortorder: conf.sortorder,
			fields: conf.fields
		};

		// create extra widgets at the bottom of the grid
		return $('<div/>').addClass('ui-grid-footer ui-state-default')
			.append($('<div/>').css('margin-left',  '7px')
				.append($('<span/>').text('Export:'))
				.append($('<a/>').css({'margin': '0px 5px'}).attr('href', '?index.php&'+$.param($.extend(params, {output: 'pdf'}))).attr('target', '_blank').append('pdf'))
				.append($('<span/>').text('|'))
				.append($('<a/>').css({'margin': '0px 5px'}).attr('href', '?index.php&'+$.param($.extend(params, {output: 'xls'}))).attr('target', '_blank').append('xls'))
				.append($('<span/>').text('|'))
				.append($('<a/>').css({'margin': '0px 5px'}).attr('href', '?index.php&'+$.param($.extend(params, {output: 'csv'}))).attr('target', '_blank').append('csv'))
			);
	};

	listen = function ($grid, conf) {
		// if actions are not defined yet, we do it now
		// note: these are the actions for default buttons (edit, add, delete)

		if(typeof conf.actions.edit == 'undefined') {
			conf.actions.edit = function ($grid, conf) {
				if(Object.keys(conf.selection).length <= 0) {
					qinoa.alert({
						message:	"<p><b>No item selected.</b></p>"+
									"<p>To edit an object, click its checkbox prior to the 'edit' button.</p>"
					});
					return false;
				}
				var object_id = Object.keys(conf.selection)[0];
/*
				var $form = $('<form/>')
				.qForm($.extend(true, conf, {
					view: conf.views.edit,
					object_id: object_id,
					predefined: {
						class_name: conf.class_name,
						ids: object_id,
						lang: conf.lang
					}
				}))
*/
				var $form = qinoa.form($.extend(true, conf, {
					view: conf.views.edit,
					object_id: object_id,
					predefined: {
						class_name: conf.class_name,
						ids: object_id,
						lang: conf.lang
					}
				}))
				.on('ready', function() {
					$dia = qinoa.dialog({
						content:	$form,
						title:		'Object edition: '+conf.class_name+' ('+object_id+') - '+conf.views.edit,
						buttons:	[]
					})
					.on('formclose', function(event, action) {
						if(typeof action != 'undefined' && action != 'cancel') {
							$grid.trigger('reload');
						}
						$dia.trigger('dialogclose');
					});
				});
			};
		}

		if(typeof conf.actions.del == 'undefined') {
			conf.actions.del = function ($grid, conf) {
				var ids = Object.keys(conf.selection);
				if(ids.length <= 0) {
					qinoa.alert({
						message:	"<p><b>No item selected.</b></p>"+
									"<p>To delete an object, click its checkbox prior to the 'delete' button.</p>"
					});
					return false;
				}
				$.when(qinoa.confirm({
					message:		'<p><b>'+ ids.length +' items selected.</b></p>'+
									'Do you confirm deletion for selected item(s) ?',
					title:			'Deletion'
				}))
				.done(function() {
					$.when(qinoa.remove(conf.class_name, ids, false))
					.done(function() {
						$grid.trigger('reload');
					});
				});
			};
		}
		if(typeof conf.actions.add == 'undefined') {
			conf.actions.add = function ($grid, conf) {
                // request a new object
				var object_id = 0;

                $.when(qinoa.create(conf.class_name, {}, conf.lang))
                .done(function(result) {
                    object_id = result;
                   // instanciate form
                    var $form = qinoa.form($.extend(true, conf, {
                        view: conf.views.edit,
                        object_id: object_id,
                        predefined: {
                            class_name: conf.class_name,
                            ids: object_id,
                            lang: conf.lang
                        }
                    }))
                    .on('ready', function() {
                        $dia = qinoa.dialog({
                            content:	$form,
                            title:		'New object: '+conf.class_name+' - '+conf.views.edit,
                            buttons:	[]
                        })
                        .on('formclose', function(event, action) {
                            if(typeof action != 'undefined' && action != 'cancel') {
                                $grid.trigger('reload');
                            }
                            $dia.trigger('dialogclose');
                        });
                    });                    
                })
                .fail(function(result){
                    qinoa.console.log('Error raised by qinoa.create(): '+qinoa.error_codes[result]);
                });             

			};
		}
	};


	return this.each(function() {
		return (function ($this, conf) {
			$this.hide();

			$.when(load_view($this, conf))
			.then(function () { return render($this, conf); })
			.then(function () { return translate($this, conf); })
			.then(function () { return init($this, conf); })
			.then(function () { listen($this, conf); $this.trigger('ready'); })

			return $this
			.on('reload', function () {
				feed($this, conf);
			})
			.on('ready', function() {
				// we leave an access to internal params (domain, selection, ...)
				$this.data('conf', conf);
				$this.show();
			});

		})($(this), $.extend(true, default_conf, conf));
	});
}
})(jQuery, qinoa);