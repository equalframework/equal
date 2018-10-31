/*! 
* qinoa-ui.qForm - v1.0.0
* https://github.com/cedricfrancoys/qinoa
* Copyright (c) 2015 Cedric Francoys; Licensed GPLv3 */

/**
 * qinoa-ui.qForm : A plugin generating Form view controls
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
"use strict";
	$.fn.qForm = function(conf){

		var default_conf = {
		// mandatory params
			class_name: '',						// class of the object to edit
			object_id: 0,						// id of the object to edit
		// optional params
			view: 'form.default',				// view to use for object edition
			lang: qinoa.conf.content_lang,	    // language in which request the content to server
			ui: qinoa.conf.user_lang,			// language in which display UI texts
			predefined: {},						// assign predefined values to some fields or insert hidden controls when those fields are not present in selected view
			autosave: true,						// autosaving drafts of the object being edited
			success_handler: null,				// bypass the standard action listener and execute some function in case of success
		// internal params
			modified: false						// status of the object being edited
		};

		var methods = {

			/**
			* Retrieves the html source of the requested view
			*
			*/
			load_view : function($form, conf) {
				var deferred = $.Deferred();
				$.when(qinoa.get_view(conf.class_name, conf.view))
				.done(function (view_html) {
					var $view = $(view_html);
					// extend the configuration object with 'view' tag attributes, if any
					// note: attributes we should expect are: 'action', 'domain', 'orientation'
					$.each($view[0].attributes, function(i, attr) { conf[attr.name] = attr.value; });
					// we'll need the form id in the 'adapt_view' method
					$view.attr('id', $form.attr('id'));
					var $result = methods.adapt_view($view);
					// if an 'action' is defined
					if(conf.action !== undefined) {
						// append buttons to the form
						$result
						.append($('<div/>').attr('align', 'right').attr('width', '100%')
							.append($('<button type="button" />').attr('name', 'save').attr('action', conf.action).attr('default', 'true'))
							.append($('<button type="button" />').attr('name', 'cancel').attr('action', 'cancel'))
						);
	/*
						if(conf.autosave)
							$form.append($('<button type="button" />').css('display', 'none').attr('name', 'autosave').attr('action', 'core_draft_save'));
	*/
					}
					// append the view to the form but don't show it yet
					$form.addClass('ui-form ui-front').append($result.addClass('qView').hide());
					deferred.resolve();
				});
				return deferred.promise();
			},



			/**
			* Transforms the html source if necessary (convert DIVs and SPANs to tables)
			* Returns a jQuery object
			*/
			adapt_view : function($view) {
				//we use tables for easier rendering
				var convert_to_table = function($item) {
					var html = $item.html();
					// labels
					html = html.replace(/(<label[^>]*>[^<]*<\/label>)/gi, '<td class="label">$1</td>');
					// vars
					html = html.replace(/(<var[^>]*>[^<]*<\/var>)/gi, '<td class="field">$1</td>');
					// table wrap + newlines
					html = '<table><tr>' + html.replace(/<br[^\/>]*>/gi, '</tr><tr>') + '</tr></table>';
					$item.html(html);
					// colspan check
					$item.find('label,var').each(function() {
						var colspan = $item.attr('colspan');
						if(typeof(colspan) != 'undefined') $item.parent().attr('colspan', colspan);
					});
					return $item.html();
				};

				// we use a recusive function to enrich html templates
				var transform_html = function($elem) {
					var $result = $('<div/>');
					// 1) process the sections of the current node
					var tabs_already_added = false;
					var $tabs_list = $('<ul/>');
					var $tabs_pane = $('<div/>').attr('id', $view.attr('id')+'_tabs').addClass('qtab');

					var $sections = $elem.children('section').each(function() {
						var name = $(this).attr('name');
						// we put a label inside the tab for later translation
						var $new_item = $('<li/>').append($('<a/>').attr('href','#'+name+'_tab').append($('<label/>').attr('name', name)));
						var $inner_item = transform_html($(this));
						var $new_tab = $('<div/>').attr('id', name+'_tab');
						$new_tab.append($('<table/>').append($('<tr/>').append($('<td/>').addClass('field').append($inner_item))));
						$tabs_list.append($new_item);
						$tabs_pane.append($new_tab);
					});
					$tabs_pane.prepend($tabs_list);

					// 2) process other elements
					$elem.children().each(function () {
						switch($(this).prop('nodeName').toLowerCase()) {
							case 'fieldset':
								var $new_fieldset = $('<fieldset/>');
								var title = $(this).attr('title');
								if(title !== undefined) $new_fieldset.append($('<legend/>').attr('name', title));
								$result.append($new_fieldset.append(transform_html($(this))));
								break;
							case 'span':
								var $new_div = $('<div/>');
								var width = $(this).attr('width');
								var align = $(this).attr('align');
								width = (width === undefined)?100:parseInt(width);
								align = (align === undefined)?'left':align;
								$new_div.css('float', 'left').css('text-align', align).css('width', (width-2) + '%').css('padding-left', '1%').css('padding-right', '1%');
								$result.append($new_div.append(convert_to_table($(this))));
								break;
							case 'div':
								var $new_div = $('<div/>');
								var width = $(this).attr('width');
								var align = $(this).attr('align');
								var id = $(this).attr('id');
								width = (width === undefined)?100:parseInt(width);
								align = (align === undefined)?'left':align;
								if(id !== undefined) $new_div.attr('id', id);
								$new_div.css('float', 'left').css('text-align', align).css('width', width + '%');
								$result.append($new_div.append(transform_html($(this))));
								break;
							case 'section':
								if(tabs_already_added) break;
								$result.append($tabs_pane);
								tabs_already_added = true;
								break;
							default:
								$result.append($(this));
								break;
						}
					});
					return $result.children();
				};
				return transform_html($view);
			},



			/**
			* Loads and inserts object values into the form
			*
			*/
			feed : function($form, conf) {
				var deferred = $.Deferred();
				var schema, fields;
				$({})
				// get the object schema
				.queue( function (next) { $.when(qinoa.get_schema(conf.class_name)).done(function (result) {schema = result; next();}); })
				// get the list of the fields present in the specified view
				.queue(	function (next) { $.when(qinoa.get_fields(conf.class_name, conf.view)).done(function (result) {fields = result; next(); }); })
				.queue( function (next) {
					var requested_fields = [];
					// we request only simple fields : complex fields loading is handled by their related widget
					$.each(fields, function (field, attributes) {
						if($.inArray(schema[field].type, qinoa.simple_types) >= 0) requested_fields.push(field);
					});
					$.when(qinoa.read(conf.class_name, conf.object_id, requested_fields, conf.lang))
					.done(function (result) {
						if(typeof result[conf.object_id] != 'undefined') {
							$.each(result[conf.object_id], function (field, value) {
								// store temporarily value as a data property (will be fetched by 'render' method)
								$('#'+field, $form).data('value', value);
							});
						}
						deferred.resolve();
						next();
					});
				});
				return deferred.promise();
			},



			/**
			* Generates form widgets
			* Convert VARs tag into widgets, add buttons and hide invisible items
			*
			*/
			render: function($form, conf) {
				var deferred = $.Deferred();


				// handle visibilty attribute
				$('var,label,button,section,div,span', $form).each(function() {
					var attr_visible = $(this).attr('visible');
					// hide non-visible items
					if(attr_visible !== undefined && !eval(attr_visible)) $(this).hide();

				});


				// enable jQuery UI widgets

				// enable tabs
				$('.qtab', $form).tabs();
				// enable buttons
				$('button[action],button[show]', $form).each(function() {
					var $this = $(this).button();
					if($this.attr('action') !== undefined) {
						$this.on('click', function () { $form.trigger('submit', $this.attr('action')); });
					}
					if($this.attr('show') !== undefined) {
						var view_attr	= ($this.attr('view') === undefined)?'form.default':$this.attr('view');
						var output_attr	= ($this.attr('output') === undefined)?'html':$this.attr('output');
						$this.on('click', function () {
							// open new window and transmit the current context
							window.open('index.php?show='+$this.attr('show')+'&'+$.param({
									view: view_attr,
									id: conf.object_id,
									object_class: conf.class_name,
									output: output_attr
								})
							);
						});
					}
					if($this.attr('default') == 'true') {
						$this.focus();
					}

/*
					// if we need to auto-save drafts, set the timeout handle
					if($(this).attr('name') == 'autosave') {
						var autosaving = function(){
							if(conf.modified) {
								// we simulate a click on the button
								$(this).trigger('click');
								// and reset the modification flag
								conf.modified = false;
							}
							conf.timer_id = setTimeout(autosaving, easyObject.conf.auto_save_delay * 60000);
						}
						// init timer
						conf.timer_id = setTimeout(autosaving, easyObject.conf.auto_save_delay * 60000);
					}
*/
				});


				// generate widgets
				var schema, fields;
				$({})
				// load schema
				.queue( function (next) { $.when(qinoa.get_schema(conf.class_name)).done(function (result) {schema = result; next();}); })
				// load fields and their attributes
				.queue(	function (next) { $.when(qinoa.get_fields(conf.class_name, conf.view)).done(function (result) {fields = result; next(); }); })
				// instanciate the widgets
				.queue( function (next) {

					// initialize form callbacks handler
					conf.onSubmitCallbacks = $.Callbacks();
					conf.onSubmitResult = true;

					// insert some hidden controls (predefined fields not present in the specified view)
					// and set pedefined values if any
					if(typeof conf.predefined == 'object') {
						$.each(conf.predefined, function(field, value){
							if($.inArray(field, Object.keys(fields)) < 0) {
								if(typeof value == 'object') field += '[]';
								$form.append($('<input type="hidden"/>').attr({id: field+(new Date()).getTime(), name: field, value: value}));
							}
						});
					}
					// define an array to keep track of the widgets ready to be displayed
					conf.ready = {};
					// generate widgets
					$.each(fields, function (field, attributes){
						// copy var attributes to a configuration object
						// note: possible attributes are : 'readonly', 'required', 'onchange', 'onsubmit', 'view', 'domain', 'widget'
						var config = $.extend({}, attributes);
						var $item = $('#'+field, $form)
						// listen to ready event
						// note : we have to listen before calling qFormWidget since some widgets can be immediately ready
						.on('ready', function(event) {
							// don't trigger the ready event twice for the same widget
							if(typeof conf.ready[field] != 'undefined') return false;
							conf.ready[field] = true;
							// prevent propagation while some widget are still missing
							if(Object.keys(conf.ready).length < Object.keys(fields).length) return false;
						});
						// extend config with class name for complex types
						if(typeof schema[field].foreign_object != 'undefined') config.class_name = schema[field].foreign_object;
						// create the widget (extend/erase some properties)
						$item.qFormWidget($.extend(config, {
								id:				field+(new Date()).getTime(),
								name:			field,
								type: 			(typeof attributes.widget != 'undefined')?attributes.widget:schema[field].type,
								value:			$item.data('value'),
								parent_class:	conf.class_name,
								parent_id:		conf.object_id,
								lang: 			conf.lang,
								ui:				conf.ui
							})
						)
						.one('change', function() {
							conf.modified = true;
							qinoa.console.log('Change made to edited object ('+conf.class_name+', '+conf.object_id+') on field '+field);
							if(typeof attributes.onchange != 'undefined') {
								// we don't use $.globalEval because we need access to the current context
								eval(attributes.onchange);
							}
						});

						// re-submission of empty binary fields would result in erasing existing data!
						if(schema[field].type == 'binary') {
							conf.onSubmitCallbacks.add(function() {
								if($item.data('widget').val().length === 0) $item.data('widget').attr({id: '', name: ''});
							});
						}

						// add onSubmit callback to the form, if any
						if(attributes.onsubmit) {
							conf.onSubmitCallbacks.add(function() {
								// we don't use $.globalEval because we need access to the current context
								eval(attributes.onsubmit);
							});
						}
						if(attributes.required) {
// note : marking a binary field as required might be a problem when re-editing
							// add a callback to ensure field is not empty when submitting
							conf.onSubmitCallbacks.add(function() {
//								if($.proxy($item.data('value'), $item)().length <= 0) {
								if($item.data('widget').data('value')().length <= 0) {
									// if a required field is empty at submission, mark it as invalid
									$item.data('widget').addClass('invalid');
									conf.onSubmitResult = false;
								}
								else $item.data('widget').removeClass('invalid');
							});
						}


						// remove attributes that might cause undesired effects
						$item.removeAttr('onsubmit');
						$item.removeAttr('onchange');
						$item.removeAttr('id');
					});
					deferred.resolve();
					next();
				});
				return deferred.promise();
			},


			/**
			* Translates displayed labels, legends and buttons into user's lang (if defined in i18n folder)
			* and adds help tips in selected lanuage
			*/
			translate: function($form, conf){
				var deferred = $.Deferred();
				$.when(qinoa.get_lang(conf.class_name, conf.ui))
				.done(function (lang) {
					if(typeof lang != 'object' || $.isEmptyObject(lang)) {
						// 1) stand-alone labels, legends, buttons (refering to the current view)
						$('label[name],legend[name],button[name]', $form).each(function() {
							$(this).text(ucfirst($(this).attr('name')));
						});
						// 2) field labels
						$('label[for]', $form).each(function() {
							$(this).text(ucfirst($(this).attr('for')));
						});
					}
					else {
						// 1) stand-alone labels, legends, buttons (refering to the current view)
						$('label[name],legend[name],button[name]', $form).each(function() {
							var name = $(this).attr('name');
							if(typeof name != 'undefined') {
								if(typeof lang.view[name] != 'undefined') {
									$(this).text(lang.view[name].label);
								}
								else $(this).text(ucfirst(name));

							}
						});
						// 2) field labels
// todo : not necesarily related to the object being edited : may also be of a subitem (what if parent and child have a field of the same name ?)
						$('label[for]', $form).each(function() {
							var value;
							var field = $(this).attr('for');
							if(field !== undefined) {
								if(typeof lang.model[field] != 'undefined' && typeof lang.model[field].label != 'undefined') {
									$(this).text(lang.model[field].label);
									if(typeof lang.model[field].help != 'undefined') {
										$(this).append($('<sup/>').attr('title', lang.model[field].help.replace(/\n/g,'<br />')).addClass('help').text('?').tooltip());
									}
								}
								else $(this).text(ucfirst(field));
							}
						});
					}
					deferred.resolve();
				});
				return deferred.promise();
			},


			/**
			* Starts listener for the submit event and handle form actions
			*
			*/
			listen: function($form, conf) {
				var deferred = $.Deferred();
				// we hijack the default submit event to handle actions and to be able to submit files ('binary' type) by posting multipart/form-data content
				$form.on('submit', function(event, action){
					// flag telling if we have to execute action silently
					var silent = false;
					var close = function(action, msg) {
						if($form.parent().parent().hasClass('ui-dialog')) { // form is inside a dialog
							$form.parent().trigger('formclose', action);
							$form.remove();
							// go to top of page
							$('html, body').animate({ scrollTop: 0 }, 0);
						}
						else if(typeof msg != 'undefined') qinoa.console.log(msg);
						return false;
					};

					// check requested action
					switch(action) {
						case 'apply':
						case 'core_draft_save':
										silent = true;
										break;
						case 'core_objects_write':
						case 'core_objects_update':
										silent = false;
										break;
						case 'cancel':
										return close('cancel');
						default:
										alert('No action is attached to this button.');
										return false;
					}

					// 1) check submission callbacks (tasks that must be processed before the form submission)
					// onSubmit callbacks are used to :
					// - check fields validity
					// - execute user defined functions (set in views, using 'onSubmit' attribute) that could modify some data
					conf.onSubmitResult = true;
					conf.onSubmitCallbacks.fire();
					if(!conf.onSubmitResult) {
						// something went wrong : stop the form submission
						alert('A mandatory field is left blank.');
						qinoa.console.log('One of the submission callbacks failed');
						return false;
					}


					// 2) POST the form data
					// force vars to synchronize with their widget if necessary (mandatory for textarea)
					$form.serialize();
					$.ajax({
						url: 'index.php?do='+action,
						type: 'POST',
						async: true,
                        dataType: 'json',
						data: new FormData($form[0]),
						cache: false,
						contentType: false,
						processData: false
					})
					.done(function(data) {
						// convert returned string to js object
						if(typeof data.result == 'number') {
							qinoa.console.log('Error raised in qinoa-ui.qForm by action ('+action+'): '+qinoa.error_codes[data.result]);
							if(data.result == qinoa.conf.INVALID_PARAM) {
								$.when(qinoa.get_lang(conf.class_name, conf.ui))
								.done(function (lang) {
									// get an array of messages for the current language
									var message = qinoa.error_codes[data.result];
									$.each(data.error_message_ids, function (index, item) {
										if(typeof lang == 'object' && typeof lang.view[item] != 'undefined') message += lang.view[item].label + "\n";
										else message += item + "\n";
									});
// todo : translation
									qinoa.alert(message, 'Validation error');
								});
							}
						}
						else {
							if(typeof conf.success_handler == 'function') {
								conf.success_handler(data);
							}
							else {
								if(silent){
									qinoa.console.log('Action ' + action + ' successfuly executed');
								}
								else {
									// if action indicates a redirection, go to the new location
									if(typeof data.url != 'undefined' && data.url.length > 0) window.location.href = data.url;
									// otherwise request parent dialog to close, if any
									else close(action, 'Action '+ action +' successfuly executed');
								}
							}
						}

					})
					.fail(function(data){
						qinoa.console.log('Error raised by action ('+action+'): '+qinoa.error_codes[data.result]);
					});
					// prevent original event handler
					return false;
				});
				deferred.resolve();
				return deferred.promise();
			}
		};


		return this.each(function() {
			return (function ($this, conf) {

				$.when(methods.load_view($this, conf))
				.done(function () { return methods.translate($this, conf); })
				.then(function () { return methods.feed($this, conf); })
				.then(function () { return methods.render($this, conf); })
				.then(function () { return methods.listen($this, conf); });

				return $this.on('ready', function() {
					$('.qView', $this).show();
				});
			})($(this), $.extend(true, default_conf, conf));
		});
	};

	/**
	* Extend qinoa with a .form method
	* Calling this method will display a loading indicator while building the form.
	*/
	$.extend(true, qinoa, {
		form: function(conf) {
			qinoa.loader.show($('body'));
			var $form = $('<form/>')
			.qForm(conf)
			.on('ready', function() {
				qinoa.loader.hide();
			});
			return $form;
		}
	});

})(jQuery, qinoa);
