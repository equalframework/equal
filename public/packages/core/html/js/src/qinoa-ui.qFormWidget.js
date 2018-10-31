/**
 * qinoa-ui.qFormwidget : A plugin generating editable widgets for view controls
 *
 * Author	: Cedric Francoys
 * Launch	: March 2015
 * Version	: 1.0
 *
 * Licensed under GPL Version 3 license
 * http://www.opensource.org/licenses/gpl-3.0.html
 *
 */

// require jquery-1.7.1.js (or later), ckeditor.js, jquery-ui.timepicker.js, easyObject.grid.js
// jquery.inputmask.js

// accepted types are: boolean, float, integer, string, short_text, text, date, time, datetime, timestamp, selection, binary, one2many, many2one, many2many
// and some additional types : password, image, code

(function($, qinoa){

/* This object holds the methods for rendering qForm widgets
*  and can be extended to handle additional widgets
*
* defines a .data('value') method
* triggers a 'ready' event when ready
*/
qinoa.FormWidgets = {
	'string': function ($this, conf) {
		var $widget = $('<input type="text"/>')
		// 'name' attribute will generate data at form submission
		.attr({id: conf.id, name: conf.name})
		// set layout and use jquery-UI css
		.addClass('ui-widget')
		.css({'box-sizing': 'border-box', 'width': '100%', 'text-align': conf.align})
		// assign the specified value
		.val(conf.value)
		// widget is not append yet, so we have to propagate 'ready' event manually
		.on('ready', function () { $this.trigger('ready'); })
		// define the expected .data('value') method
		.data('value', function() {return $widget.val();});
		if(conf.readonly) $widget.attr('disabled', 'disabled').addClass('ui-state-disabled');
		if(conf.required) {
            $widget.attr('aria-required', 'true');
            $widget.addClass('required');
        }
		return $widget.trigger('ready');
	},
	'integer': function ($this, conf) {
		var $widget = qinoa.FormWidgets.string($this, conf);
		return $widget.inputmask('integer',	{
			allowMinus: true
		});
	},
	'float': function ($this, conf) {
		var $widget = qinoa.FormWidgets.string($this, conf);
		return $widget.inputmask('decimal',	{
			radixPoint:	qinoa.conf.QN_NUMERIC_DECIMAL_POINT,
			digits:		qinoa.conf.QN_NUMERIC_DECIMAL_PRECISION,
			autoGroup:	false
		});
	},
	'date': function ($this, conf) {
		var $widget = qinoa.FormWidgets.string($this, conf);
		return $widget.datepicker({
			// dateFormat: 'dd/mm/yy',
			dateFormat: qinoa.conf.QN_DATE_FORMAT,
			yearRange: 'c-70:c+20',
			changeMonth: true,
			changeYear: true
		});
	},
	'datetime': function ($this, conf) {
		var $widget = qinoa.FormWidgets.string($this, conf);
		return $widget.datetimepicker({
			dateFormat: qinoa.conf.QN_DATE_FORMAT,
			timeFormat: qinoa.conf.QN_TIME_FORMAT,
			yearRange: 'c-70:c+20',
			changeMonth: true,
			changeYear: true
		});
	},
	'time': function ($this, conf) {
		var $widget = qinoa.FormWidgets.string($this, conf);
		return $widget.timepicker({timeFormat: qinoa.conf.QN_TIME_FORMAT});
	},
	'timestamp': function ($this, conf) {
//todo
	},
	'boolean': function ($this, conf) {
// todo : align checkbox left
		if(conf.mode == 'edit') {
			var $widget =	$('<input type="checkbox" value="1" />')
			.attr({id: conf.id, name: conf.name})
			.prop('checked', (parseInt(conf.value) > 0))
			.val((parseInt(conf.value) > 0)?1:0)
			.on('change', function () {
				this.value = +(this.checked);
			})
			.on('ready', function () {$this.trigger('ready');})
			.data('value', function() {return $widget.val();});
			if(conf.readonly) $widget.attr('disabled', 'disabled').addClass('ui-state-disabled');
			if(conf.required) $widget.addClass('required');
			return $widget.trigger('ready');
		}
		if(conf.mode == 'view'){
		}
	},
	'password': function ($this, conf) {
		var $widget = $('<input type="password"/>')
		.attr({id: conf.id, name: conf.name})
		.css({'box-sizing': 'border-box', 'width': '100%', 'text-align': conf.align})
		.val(conf.value)
		.on('ready', function () { $this.trigger('ready'); })
		.data('value', function() {return $widget.val();});
		if(conf.readonly) $widget.attr("disabled","disabled").addClass('ui-state-disabled');
		if(conf.required) $widget.addClass('required');
		return $widget.trigger('ready');
	},
	'short_text': function ($this, conf) {
		var $widget = $('<textarea/>')
		.attr({id: conf.id, name: conf.name})
		// set layout and use jquery-UI css
		.addClass('ui-widget')
		.css({'box-sizing': 'border-box', 'width': '100%', 'text-align': conf.align})
		.html(conf.value)
		// widget is not appended yet, so we have to propagate 'ready' event manually
		.on('ready', function () { $this.trigger('ready'); })
		.data('value', function() {return $widget.val();});
		if(conf.readonly) $widget.attr("disabled","disabled").addClass('ui-state-disabled');
		if(conf.required) $widget.addClass('required');
		return $widget.trigger('ready');
	},
	'text': function ($this, conf) {
		var $widget = $('<textarea/>')
		.hide()
		.attr({id: conf.id, name: conf.name})
//		.uniqueId()
		.html(conf.value)
		.on('ready', function () { $this.trigger('ready'); })
		.data('value', function() {return $widget.val();} );

		var $richtext =
		$('<div/>')
		.html(conf.value)
		.richtext({
			toolbar: [
				['Maximize'],['Source'],['Undo','Redo'],['Cut','Copy','Paste'],['Bold','Italic','Underline','Strike','-','Subscript','Superscript', '-', 'RemoveFormat'],
				'/',
				['TextColor'], ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],['NumberedList','BulletedList','-','Outdent','Indent','-','Blockquote'],['Anchor','Link','Image','Table']
			]
		})
		.on('change', function () {
			$widget.val($richtext.richtext('value')).trigger('change');
		})
		.appendTo($this);

		if(conf.required) $widget.addClass('required');
		return $widget.trigger('ready');
	}
};

$.fn.qFormWidget = function(conf){

	var default_conf = {
		mode:		'edit',
		name:		'',
		value:		'',
		type:		'string',
		format:		'',
		align:		'left',
		readonly:	false,
		required:	false
	};

	return this.each(function() {
		return (function ($this, conf) {
			try {
				if(typeof qinoa.FormWidgets[conf.type] == 'undefined') throw Error('Error raised in qinoa-ui.qFormWidget : unknown type '+conf.type);
				var $widget = qinoa.FormWidgets[conf.type]($this, conf);
				return $this.data('widget', $widget.appendTo($this));
			}
			catch(e) {
				qinoa.console.log(conf.type+' '+conf.name+e.message);
			}
		})($(this), $.extend(true, default_conf, conf));
	});
}
})(jQuery, qinoa);



/**
	Additional widgets definitions
*/
(function($, qinoa){
	qinoa.FormWidgets.many2one = function ($this, conf) {
		var default_conf = {
			view: 'list.default'
		};
		return (function ($this, conf) {
			var $widget =	$('<input type="hidden"/>')
			.attr({id: conf.id, name: conf.name})
			.val(conf.value)
			.data('value', function() {return $widget.val();});

			// obtain the fields from the specified view
			$.when(qinoa.get_fields(conf.class_name, conf.view))
			.done(function (result) {
				conf.fields = Object.keys(result);
				// create the UI

				$('<div/>')
				.css({'position': 'relative'})
				.addClass('ui-widget')
					.append(
						$('<input/>')
						.addClass('choice_input')
						.css({'box-sizing': 'border-box', 'width': 'calc(100% - 5em)', 'text-align': conf.align})
						.autocomplete({
							minLength: 4,
							delay: 500,
							source: function(request, response){
								// we limit the number of results to 25
								$.when(qinoa.search(conf.class_name, [[[conf.fields[0], 'ilike', '%' + request.term + '%']]], conf.fields[0], 'asc', 0, 25, conf.lang))
								.done(function (ids) {
									var list = [];
									if(Object.keys(ids).length > 0) {
										$.when(qinoa.read(conf.class_name, ids, conf.fields[0], conf.lang))
										.done(function (data) {
											// note: we use 'ids' order since 'data' might have been reordered by the browser
											$.each(ids, function(i, id) { list.push({label: data[id][conf.fields[0]], value: id }); });
											response(list);
										})
										.fail(function (code) { qinoa.console.log('Error raised by qinoa.read('+conf.class_name+') in qinoa.FormWidgets.many2one: '+qinoa.error_codes[code]); });
									}
								})
								.fail(function (code) { qinoa.console.log('Error raised by qinoa.search('+conf.class_name+','+conf.fields[0]+') in qinoa.FormWidgets.many2one: '+qinoa.error_codes[code]); });
							},
							select: function(event, ui) {
								// we intercept the selection in the autocomplete list in order to display the label
								// and store the id in a hidden input
								$widget.val(ui.item.value).trigger('change');
								// trigger a refresh of the displayed label and display first field in the meanwhile
								$(event.target).val(ui.item.label).trigger('feed');
								return false;
							}
						})
						.on('feed', function() {
							// render the content of the widget
							var m2o = $widget.val();
							if(m2o <= 0) $this.trigger('ready');
							else {
								// request values of the fields involved in the specified view for the selected foreign object
								$.when(qinoa.read(conf.class_name, m2o, conf.fields, conf.lang))
								.done(function (data) {
									var value = '';
									$.each(conf.fields, function (i, field) {
										if(value.length) value += ', ';
										value += data[m2o][field];
									});
									// display the resulting label
									$('.choice_input', $this).val(value).blur();
									$this.trigger('ready');
								});
							}
						})
						// initial feed
						.trigger('feed')
					)
					.append(
						// note : remember that by default buttons act like 'submit' (hence the 'type' attribute)
						$('<button type="button"/>').button({icons:{primary:'ui-icon-pencil'}, text: false})
						.attr('title', 'edit')
						.css({'position': 'absolute', 'right': '2.5em', 'top': '0', 'margin': '0', 'height': '100%'})
						.on('click', function() {
							// if an  item is selected, open an edition window
							var object_id = $widget.val();
							// var view = (conf.view != undefined)?conf.view:'form.default';
							// default view is probabily intended for grid view
							var view = 'form.default';
							var $form = $('<form/>')
							.qForm($.extend(true, conf, {
								view: view,
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
									title:		'Object edition: '+conf.class_name+' ('+object_id+') - '+view,
									buttons:	[]
								})
								.on('formclose', function(event, action) {
									if(typeof action != 'undefined' && action != 'cancel') {
										$('.choice_input', $this).trigger('feed');
									}
									$dia.trigger('dialogclose');
								});
							});
						})
					)
					.append(
						$('<button type="button"/>').button({icons:{primary:'ui-icon-search'}, text: false})
						.attr('title', 'search')
						.css({'position': 'absolute', 'right': '0', 'top': '0', 'margin': '0', 'height': '100%'})
						.on('click', function() {
	// todo : use the current content as a mask for searching among the item
							// create a domain with ilike operator
							// and display a selection list
							// copy the current config and set list for unique selection
							var grid_conf = $.extend(true, {'multiple': false}, conf);
							var $grid =
							$('<div/>')
							.qSearchGrid(grid_conf)
							.on('ready', function() {
								qinoa.dialog({
									content:	$grid,
									title:			'Choose item',
									buttons:	[{
										text: "Ok",
										click: function() {
											$.each($grid.data('conf').selection, function(id, state){
												$widget.val(id);
												$this.trigger('change');
												$('.choice_input', $this).trigger('feed');
											});
											$( this ).trigger('dialogclose');
										}
									}]
								});
							});
						})
					)
				.appendTo($this);
			});

			return $widget;
		})($this, $.extend(true, default_conf, conf));
	};


	qinoa.FormWidgets.one2many = function ($this, conf) {
		var $widget =	$('<input type="hidden"/>')
		.attr({id: conf.id, name: conf.name})
		.val(conf.value)
		.data('value', function() {return $widget.val();} );

		(function($this, conf) {
			$.when(qinoa.get_schema(conf.parent_class))
			.done(function (schema) {
				var domain = [[ [schema[conf.name]['foreign_field'], '=', conf.parent_id] ]];
				if(conf.domain != undefined) domain = merge_domains(domain, conf.domain);
				var grid_conf = $.extend(true, conf, {
					domain: 	domain,
					buttons:	{
						edit:	{
									text: 'edit',
									icon: 'ui-icon-pencil',
								},
						del:	{
									text: 'delete relation',
									icon: 'ui-icon-minus',
								},
						add:	{
									text: 'add relation',
									icon: 'ui-icon-plus',
								}
					},
					actions:	{
						add:	function($grid, conf) {
							var grid_conf = {class_name: conf.class_name, view: conf.views.add, lang: conf.lang};
//							if(conf.domain != undefined) grid_conf.domain = eval(conf.domain);
							// display only items not already present in relation
							grid_conf.domain = [[ [schema[conf.name]['foreign_field'], '<>', conf.parent_id] ]];
							var $sub_grid =
							$('<div/>')
							.qSearchGrid(grid_conf)
							.on('ready', function() {
								qinoa.dialog({
									content:	$sub_grid,
									title:		'Add relation',
									buttons:	[{
										text: "Ok",
										click: function() {
											$.each($sub_grid.data('conf').selection, function(id, state){
												conf.more = add_value(conf.more, id);
												conf.less = remove_value(conf.less, id);
											});
                                            // force grid to refresh its content
                                            $grid.trigger('reload');                                            
                                            // update the value of the widget
                                            $grid.trigger('change');                                            
											$( this ).trigger('dialogclose');
										}
									}]
								});
							});
					},
					del:	function($grid, conf) {
							var ids = Object.keys(conf.selection);
							$.when(qinoa.confirm({
									message:	'<p><b>'+ ids.length +' item(s) selected.</b></p>'+
												'Do you confirm deletion for selected relations(s) ?',
									title:		'Deletion'
							}))
							.done(function() {
								$.each(conf.selection, function(id, state){
									conf.less = add_value(conf.less, id);
									conf.more = remove_value(conf.more, id);
								});
								// force grid to refresh its content
								$grid.trigger('reload');
								// update the value of the widget
								$grid.trigger('change');
							});
					}
					}
				});
				var $grid =
				$('<div/>')
				.qGrid(grid_conf)
				.on('ready', function() {
					$this.trigger('ready');
				})
				.on('change', function () {
					var value = $grid.data('conf').more.toString();
					$.each($grid.data('conf').less, function() {
						if(value.length > 0) value += ',';
						value += '-'+conf.less[i];
					});
					$widget.val(value);
					$this.trigger('change');
				})
				.appendTo($this);
			});
		})($this, conf);
		return $widget;
	};

	qinoa.FormWidgets.many2many = function ($this, conf) {
		var $widget =	$('<input type="hidden"/>')
		.attr({id: conf.id, name: conf.name})
		.val(conf.value)
		.on('change', function () { $this.trigger('change'); })
		.data('value', function() {return $widget.val();});

		(function($this, conf) {
			$.when(qinoa.get_schema(conf.parent_class))
			.done(function (schema) {
				var domain = [[ [schema[conf.name]['foreign_field'], 'contains', conf.parent_id] ]];
				if(conf.domain != undefined) domain = merge_domains(domain, conf.domain);
				var grid_conf = $.extend(true, conf, {
					domain: 	domain,
					buttons:	{
						edit:	{
									text: 'edit',
									icon: 'ui-icon-pencil',
								},
						del:	{
									text: 'delete relation',
									icon: 'ui-icon-minus',
								},
						add:	{
									text: 'add relation',
									icon: 'ui-icon-plus',
								}
					},
					actions:	{
						add:	function($grid, conf) {
							var grid_conf = {class_name: conf.class_name, view: conf.views.add, lang: conf.lang};
//							if(conf.domain != undefined) grid_conf.domain = eval(conf.domain);
							// display only items not already present in relation
							// doesn't work this wat
							// grid_conf.domain = [[ [schema[conf.name]['foreign_field'], 'not in', [conf.parent_id]] ]];
							// we could manully prevent dislay of the objecs already in the relation
							// grid_conf.less =
							var $sub_grid =
							$('<div/>')
							.qSearchGrid(grid_conf)
							.on('ready', function() {
								qinoa.dialog({
									content:	$sub_grid,
									title:		'Add relation',
									buttons:	[{
										text: "Ok",
										click: function() {
											$.each($sub_grid.data('conf').selection, function(id, state){
												conf.more = add_value(conf.more, id);
												conf.less = remove_value(conf.less, id);
											});
                                            // force grid to refresh its content
                                            $grid.trigger('reload');                                            
                                            // update the value of the widget
                                            $grid.trigger('change');
											$( this ).trigger('dialogclose');
										}
									}]
								});
							});
                        },
                        del:	function($grid, conf) {
                            var ids = Object.keys(conf.selection);
                            $.when(qinoa.confirm({
                                    message:	'<p><b>'+ ids.length +' item(s) selected.</b></p>'+
                                                'Do you confirm deletion for selected relations(s) ?',
                                    title:		'Deletion'
                            }))
                            .done(function() {
                                $.each(conf.selection, function(id, state){
                                    conf.less = add_value(conf.less, id);
                                    conf.more = remove_value(conf.more, id);
                                });
                                // force grid to refresh its content
                                $grid.trigger('reload');
                                // update the value of the widget
                                $grid.trigger('change');
                            });
                        }
					}
				});
				var $grid =
				$('<div/>')
				.qGrid(grid_conf)
				.on('ready', function() {
					$this.trigger('ready');
				})
				.on('change', function () {
					var value = $grid.data('conf').more.toString();
					$.each($grid.data('conf').less, function() {
						if(value.length > 0) value += ',';
						value += '-'+conf.less[i];
					});
					$widget.val(value).trigger('change');
				})
				.appendTo($this);
			});
		})($this, conf);
		return $widget;
	};

	qinoa.FormWidgets.binary = function ($this, conf) {
		var $widget = $('<input type="file" />')
		.attr({id: conf.name, name: conf.name})
		.addClass('ui-widget')
		.css({'box-sizing': 'border-box', 'width': '100%', 'text-align': conf.align})
		// widget is not appended yet, so we have to propagate 'ready' event manually
		.on('ready', function () { $this.trigger('ready'); })
		// define the expected .data('value') method
		.data('value', function() {return $widget.val();});
		return $widget.trigger('ready');
	};

	qinoa.FormWidgets.image = function ($this, conf) {
		var $widget =	$('<input type="hidden"/>')
						.attr({id: conf.id, name: conf.name})
						.val(conf.value)
						.on('change ready', function (event) { $this.trigger(event.type);})
						.data('value', function() {return $widget.val();});
		return $widget.trigger('ready');
	};

})(jQuery, qinoa);