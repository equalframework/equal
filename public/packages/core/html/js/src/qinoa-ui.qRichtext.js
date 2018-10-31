/**
 * qinoa-ui.qRichtext : A plugin generating rich text editor
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
"use strict";

/**
* Gets the value of a property from the style attribute for the first element in the set
* This differs from $.css() which uses getComputedStyle to retrieve the final style applied to the element (which might be inherited)
*
*/
$.fn.style = function(property) {
	var style = '';
	$.each(this, function() {
		var style_attr = $(this).attr('style');
		// if node has a style attribute set
		if(style_attr !== undefined) {
			// extract all properties and look for the specified one
			$.each($.fn.style.explode(';', style_attr.replace(/ /g, '')), function(i, tuple) {
				// extract property name and value
				var style_arr = $.fn.style.explode(':', tuple);
				if(style_arr[0] == property) {
					style = style_arr[1];
					// there should be only one occurence of that property
					// so, once found, we skip the rest
					return false;
				}
			});
		}
		// we process only the first element of the set
		return false;
	});
	return style;
};

/**
* Extracts the whole style attribute of the first element in the set as pairs of properties/values,
* and returns it as an assicative object that can be passed as argument to the method $.css()
*/
$.fn.styles = function() {
	var styles = {};
	$.each(this, function() {
		var style_attr = $(this).attr('style');
		// if node has a style attribute set
		if(style_attr !== undefined) {
			// extract all properties and look for the specified one
			$.each($.fn.style.explode(';', style_attr.replace(/ /g, '')), function(i, tuple) {
				// extract property name and value
				var style_arr = $.fn.style.explode(':', tuple);
				styles[style_arr[0]] = style_arr[1];
			});
		}
		// we process only the first element of the set
		return false;
	});
	return styles;
};


/**
* Returns an array of strings, each of which being a substring formed by splitting the string on boundaries formed by the delimiter.
*/
$.fn.style.explode = function (delimiter, value) {
	var result = [], start = 0, length = value.length;
	while(start < length) {
		var pos = value.indexOf(delimiter, start);
		if(pos == -1) {
			result.push(value.slice(start));
			break;
		}
		result.push(value.slice(start, pos));
		start = pos+delimiter.length;
	}
	return result;
};


/**
*	Returns a set of selected nodes inside the given element
*	The resulting collection might contain elementNodes as well as textNodes (textNodes are returned when some elementNodes are partially selected)
*/
$.fn.selection = function(conf){
	var defaults = {
	};

	return (function ($this, conf) {
		// if no selection is found, result will be an empty array
		var collection = [];
		var sel = rangy.getSelection();
		var range = sel.getRangeAt(0);
		// create a range containing all container ($this) contents
		var container_range = rangy.createRangyRange();
		container_range.selectNodeContents($this[0]);
		// selection is not collapsed and is inside the specified container
		if(!sel.isCollapsed && container_range.containsRange(range)) {
			// split partially selected textNodes so that range only contains fully selected nodes
			range.splitBoundaries();
			// check all selected textNodes
			$.each(range.getNodes([3]), function (i, node) {
				// if the textNode is inside a partially selected elementNode
				if( !range.containsNodeText(node.parentNode) ){
					// add the textNode
					collection.push(node);
				}
				// otherwise, add the parent node (if not already present)
				else if($.inArray(node.parentNode, collection) == -1) {
					collection.push(node.parentNode);
				}
			});
		}
		sel.setSingleRange(range);
		// convert resulting array to a jQuery object
		return $(collection);
	})($(this), $.extend(true, {}, defaults, conf));
};


$.fn.richtext = function(conf) {
	var defaults = {
		toolbar: [
			['Maximize'],['Source'],['Bold','Italic','Underline','Strike','-','Subscript','Superscript', '-', 'RemoveFormat']
		],
		height: '250px'
	};

	var buttons_def = $.fn.richtext.buttons;

	var _init = function ($this, conf) {
		// initialize some internal parameters
		conf.mode = 'wysiwyg';			// wysiwyg | source
		conf.maximized = false;		// true | false
		conf.buttons_states = {};		// { button_name: true | false[, ...] }
	};

	var _layout = function ($this, conf) {
		// create the toolbar
		var $toolbar =
		$('<div/>')
		.addClass('ui-editor-toolbar ui-widget-header ui-corner-all ui-front');

		// create the editor
		var $editor =
		$('<div/>')
		.addClass('ui-editor ui-widget ui-widget-content ui-corner-all')
		.css({'height': conf.height})
		.append($toolbar)
		.append(
			$('<div/>')
			.addClass('ui-editor-content ui-widget-content')
			.attr({contenteditable: true, spellcheck: true})
			.html($this.html())
		)
		.append(
			$('<textarea/>')
			.addClass('ui-editor-source ui-widget-content')
			.attr({spellcheck: false})
			.val($this.html())
			.on('change', function() { $this.trigger('change'); })
			.hide()
		);


		// populate the toolbar (we'll need to know its height just afterward)
		$.each(conf.toolbar, function(i, button_group) {
			if(!$.isArray(button_group)) {
				if(button_group == '/') {
					$toolbar.append( $('<span/>').addClass('ui-editor-toolbar-break') );
				}
				return;
			}
			var $group = $('<span/>').addClass('ui-editor-toolbar-group ui-state-default ui-corner-all');
			$.each(button_group, function(j, button_name) {
				if(button_name == '-') {
					$group.append( $('<span/>').addClass('ui-editor-toolbar-separator') );
				}
				// if we have a definition for that button and if it is not yet in the toolbar
				else if(typeof buttons_def[button_name] != 'undefined' && typeof conf.buttons_states[button_name] == 'undefined') {
					// init/set the button state
					conf.buttons_states[button_name] = false;
					var $button =
					$('<span/>')
					.attr('title', button_name)
					.attr('button', button_name)
					.button({icons:{primary:buttons_def[button_name].icon}, text: false})
					.appendTo($group);
				}
			});
			$group.appendTo($toolbar);
		});

		// adjust height of content and source
		var $temp = $('<div/>').css({'position': 'absolute', 'top': '0', 'left': '-9999px'}).append($editor).appendTo('body');		
		$('.ui-editor-content,.ui-editor-source', $editor).css('height', (parseInt($editor.css('height'))-parseInt($toolbar.css('height'))-6)+'px');
		$editor.detach();

		// provide a quick access to the editor
		$this.data('editor', $editor);

		// insert editor element regarding the given element
		if($this.parent().length === 0) {
			if($this.prop('nodeName').toUpperCase() == 'TEXTAREA') {
				console.log('qRichtext error : cannot instanciate on detached TEXTAREA, insert or wrap it first or use DIV instead');
			}
			else {
				$this.empty().append($editor);
			}
		}
		else {
			$editor.insertBefore( $this.hide() );
			if($this.attr('name') !== undefined) {
				$('.ui-editor-source', $editor).attr('name', $this.attr('name'));
				$this.removeAttr('name');
			}
		}
	};

	var _listen = function($this, conf) {
		var $editor = $this.data('editor');

		$('.ui-button', $editor)
		.on('click', function () {
			var button_name = $(this).attr('title');
			switch (buttons_def[button_name].type) {
			case 'action':
				return buttons_def[button_name].click($editor, conf);
				break;
			case 'style':
				switch (buttons_def[button_name].method) {
				case 'css':
					var style = {};
					var $selection = $({});
					style[buttons_def[button_name].style.property] = buttons_def[button_name].style.value;
					if(!conf.buttons_states[button_name]) {
						$selection = methods.addCSS($editor.selection(), style);
						methods.setButtonState($this, conf, button_name, true);
					}
					else {
						$selection = methods.removeCSS($editor.selection(), style);
						methods.setButtonState($this, conf, button_name, false);
					}
					$.fn.richtext.updateSelection($selection);
					break;
				case 'wrap':
					if(!conf.buttons_states[button_name]) {
						methods.wrap($editor.selection(), buttons_def[button_name].tag);
						methods.setButtonState($this, conf, button_name, true);
					}
					else {
						methods.unwrap($editor.selection(), buttons_def[button_name].tag);
						methods.setButtonState($this, conf, button_name, false);
					}
					break;
				case 'wrapAll':
					if(!conf.buttons_states[button_name]) {
						var style = {};
						if(typeof buttons_def[button_name].style != 'undefined') {
							style[buttons_def[button_name].style.property] = buttons_def[button_name].style.value;
						}
						methods.wrapAll($editor.selection(), buttons_def[button_name].tag, style);
						methods.setButtonState($this, conf, button_name, true);
// todo : necessary because justify buttons are exclusive : how to generalize ?
						methods.updateButtonsStates($this, conf);
					}
					else {
						methods.unwrapAll($editor.selection(), buttons_def[button_name].tag);
						methods.setButtonState($this, conf, button_name, false);
					}
					break;
				}
				break;
			}
			methods.updateValue($this, conf);
		});

		$('.ui-editor-content', $editor)
		.on('focus', function(event){
		})
		.on('select', function(event){
			// note: select event don't seem to be triggered on contenteditable div
		})
		.on('click', function(event){
		})
		.on('dblclick', function(event){
			// when we end up here, click event has already been triggered twice
		})
		.on('mousedown', function(event) {
		})
		.on('mouseup', function(event) {
			// caret position or selection might have changed : update buttons states
			methods.updateButtonsStates($this, conf);
		})
		.on('keyup', function(event) {
			if(!event.shiftKey) {
				// caret position or selection might have changed : update buttons states
				methods.updateButtonsStates($this, conf);
			}
		})
		.on('keydown', function(event) {
			// handle carriage return output
			if (event.which == 13) {
				var range = rangy.getSelection().getRangeAt(0);
				var parentNode = range.startContainer.parentNode;
				// we are inside a list item : insert a new item after the current one
				if(parentNode.nodeName.toUpperCase() == 'LI') {
					range.splitBoundaries();
					var start = range.startContainer;
					// if we are in the middle of an element, select all parts after the caret
					if(start.nextSibling) {
						range.selectNodeContents(parentNode);
						range.setStart(start.nextSibling);
						range.select();
					}
					//  create new item and add the current selection to it
					var $li = $('<li/>')
					.append($editor.selection().detach());
					$(start).parent().after($li);
					range.selectNode($li[0]);
					// set the caret position at the beginning of the new item
					range.collapse(true);
					range.select();
				}
				// in all other cases, just add a line break
				else {
					var enter = $('<br/>')[0];
					range.collapse(true);
					range.insertNode(enter);
					range.setStartAfter(enter);
					range.select();
				}
				// prevent the default behaviour
				return false;
			}
			methods.updateValue($this, conf);
		});
	};


	var methods = {

		addCSS: function($selection, style) {
			return $.each($.fn.richtext.normalize($selection), function(i, node) {
				var $node = $(this);
				$.each(style, function (property, value) {
					// check the current value for the targeted property
					// note : we don't use $.css(property) which calls getComputedStyle and might return inherited style and mixed types values (ex: 700 as well as 'bold')
					var current_value = $node.style(property);
					// if style is not applied on element
					if(current_value.indexOf(value) == -1) {
						// if current value is not empty we add the specified value to it
						if($.inArray(current_value, [null, '', '0', 'none']) == -1) {
							value = current_value+' '+value;
						}
						// apply the specified style (if current value is empty, we overwrite it)
						$node.css(property, value);
					}
				});
			});
		},

		removeCSS: function($selection, style) {
			$.each($.fn.richtext.normalize($selection), function(i, node) {
				var $node  = $(this);
				$.each(style, function (property, value) {
					var node_style =  $node.attr('style');
					var current_property = $node.style(property);
					// if property is inherited
					if(node_style === undefined || node_style.indexOf(value) == -1) {
						// find the node from which property is inherited
						var $parent = $node.parent();
						do {
							current_property = $parent.style(property);
							// extract node from its parent (there might be several levels here)

							// example : remove bold style from ghi
							// <span style="font-weight: bold;">def<span style="font-style: italic;"><span>ghi</span>abc</span></span>

							// 1) <span style="font-weight: bold;">def<span style="font-style: italic;"></span><span>ghi</span><span style="font-style: italic;">abc</span></span>
							// 2) <span style="font-weight: bold;">def<span style="font-style: italic;"></span></span><span>ghi</span><span style="font-weight: bold;"><span style="font-style: italic;">abc</span></span>
							// 3) <span style="font-weight: bold;">def</span><span style="font-style: italic;">ghi</span><span style="font-weight: bold;"><span style="font-style: italic;">abc</span></span>

							// add some marker
							$node.addClass('qRtMoving');
							var tag = $parent.prop('nodeName');
							var $next_parent = $parent.parent();

							var $new =
								$('<'+tag+'>'+
								$parent.html().replace(new RegExp($node[0].outerHTML), '</'+tag+'>'+$node[0].outerHTML+'<'+tag+'>')+
								'</'+tag+'>')	;

							$parent.replaceWith($new);

							// retrieve node, remove marker and restore style
							$node  = $('.qRtMoving', $next_parent).removeAttr('class').attr('style', node_style);
							// update selection
							$selection[i] = $node[0];
							// add style from previous parent to node and its direct siblings
							$node
							.add($node.prev())
							.add($node.next())
							.css($parent.styles());
							// normalize the new parent  (we might have generated some empty tags)
							$.fn.richtext.normalize($next_parent.children(), false);
							// try the above parent
							$parent = $next_parent;
						} while(current_property.indexOf(value) == -1) ;
					}
					// remove property
					$node.css(property, $node.style(property).replace(new RegExp(value, 'g'), ''));
				});
			});
			return $selection;
		},

		wrap: function($selection, tag) {
			// wrap nodes not already wrapped by such tag
			$.each($selection, function () {
				if($(this).closest(tag).not('.ui-editor-content').length === 0) {
					$(this).wrap($('<'+tag+'/>'));
				}
			});
		},

		unwrap: function($selection, tag) {
			$.each($selection, function() {
				var $node = $(this);
				// node is a textNode
				if(this.nodeType == 3) {
					// node is either first node or last node of its parent
					if(this.parentNode.firstChild == this) {
						$node.parent().before($node.detach());
					}
					else {
						$node.parent().after($node.detach());
					}
					// continue iteration
					return;
				}
				// node is the wrapper itself
				if($node.prop('nodeName').toUpperCase() == tag.toUpperCase()) {
					$node.before($node.html()).remove();
				}
				// node is an element wrapped into the tag
				else {
					$node.unwrap();
				}
			});
		},

		wrapAll: function($selection, tag, style) {
			if($selection.prop('nodeName') && $selection.prop('nodeName').toUpperCase() == tag.toUpperCase()) {
				$selection.css(style);
			}
			else {
				var $parent = $.fn.richtext.normalize($selection).first().parent();
				if( $parent.prop('nodeName').toUpperCase() == tag.toUpperCase() && !$parent.hasClass('ui-editor-content') ) {
					$parent.css(style);
				}
				else {
					// note: $selection does not reflect the accurate innerHTML, so we cannot simply apply $.wrapAll() on it
					$parent.append( $('<'+tag+'/>').css(style).append($parent.children().detach()) );
				}
			}

		},

		unwrapAll: function($selection, tag) {
			// node is the wrapper itself
			if($selection.prop('nodeName') && $selection.prop('nodeName').toUpperCase() == tag.toUpperCase()) {
				$selection.before($selection.html()).remove();
			}
			else {
				$selection.unwrap();
			}
		},

		/**
		* synchronizes editor-source (textarea with attribute 'name' for form submission) and editor-content (contenteditable)
		* note: this value is the one returned by method .richtext('value')
		*/
		updateValue: function($this, conf) {
			if(conf.timeoutId) {
				clearTimeout(conf.timeoutId);
			}
			conf.timeoutId = setTimeout(function(){
				conf.timeoutId = null;
				var $editor = $this.data('editor');
				$('.ui-editor-source', $editor).val( $('.ui-editor-content', $editor).html() );
				$this.trigger('change');
			}, 500);
		},

		setButtonState: function ($this, conf, button_name, state) {
			var $button = $('span[button="'+button_name+'"]', $this.data('editor'));
			if(state) {
				conf.buttons_states[button_name] = true;
				$button.addClass('ui-state-highlight');
			}
			else {
				conf.buttons_states[button_name] = false;
				$button.removeClass('ui-state-highlight');
			}
		},

		updateButtonsStates: function($this, conf) {
			var $editor = $this.data('editor');
			var sel = rangy.getSelection();

			var $selection;

			// if there is no selected node, use the selection anchor node as selection
			if(sel.isCollapsed) {
				$selection = $(sel.anchorNode);
			}
			else {
				$selection = $editor.selection();
			}
			if($selection === undefined || $selection.length === 0) {
				return;
			}
			// 1) find the resulting style of the first element in the selection
			var $node = $selection.first();
			var getAppliedStyles = function($node) {
				var styles = {};
				$.each(buttons_def, function(button_name, button_def) {
					var $button = $('span[button="'+button_name+'"]', $editor);
					// if toolbar contains such button
					if($button.length) {
						// we're only interested in buttons related to content styling
						if(button_def.type != 'style') {
							// continue iteration
							return;
						}
						styles[button_name] = false;
						if(button_def.method == 'css') {
							// $.css() uses method getComputedStyle() wich applies only to elementNodes
							if($node[0].nodeType == 3) {
								$node = $( $node[0].parentNode );
							}
							if($node.css(button_def.style.property).indexOf(button_def.style.value) > -1) {
								styles[button_name] = true;
							}
						}
						else if(button_def.method == 'wrap'){
							if( $node.prop('nodeName').toUpperCase() == button_def.tag.toUpperCase()
								 || $node.parents(button_def.tag).length > 0 ) {
								styles[button_name] = true;
							}
						}
						else if(button_def.method == 'wrapAll'){
							if(typeof button_def.style == 'undefined') {
								if($node.parentsUntil($('.ui-editor-content', $editor), button_def.tag).length > 0) {
									styles[button_name] = true;
								}
							}
							else if($node.css(button_def.style.property) == button_def.style.value) {
								styles[button_name] = true;
							}
						}
					}
				});
				return styles;
			};

			var applied_styles = getAppliedStyles($node);

			// 2) reduce the applied styles by comparing with the other nodes in the selection
			$.each($selection.slice(1), function (){
				var styles = getAppliedStyles($(this));
				var is_empty = true;
				$.each(applied_styles, function (button_name, state) {
					applied_styles[button_name] = (applied_styles[button_name] && styles[button_name]);
					if(applied_styles[button_name]) {
						is_empty = false;
					}
				});
				// if resulting style is empty, stop iteration
				if(is_empty) return false;
			});

			$.each(applied_styles, function(button_name, state) {
				methods.setButtonState($this, conf, button_name, state);
			});
		}

	};

	if(typeof conf == 'object') {
		return $.each(this, function() {
			return (function ($this, conf) {
				_init($this, conf);
				_layout($this, conf);
				_listen($this, conf);
				return $this;
			})($(this), $.extend({}, defaults, conf));
		});
	}
	else if(typeof conf == 'string') {
		switch(conf){
		case 'value':
			return $('.ui-editor-source', $(this).data('editor')).val();
			break;
		}
	}
	return this;
};


/**
* This method allows to make sure all items among a selection are consistents
*
* @param $selection jQuery A jQuery collection of nodes
* @param wrap boolean  If set to true, the methode wraps all textNodes from selection inside SPAN nodes. If set to false, it removes empty SPAN nodes and extract textNodes from SPAN nodes having no style attribute set.
* @return jQuery object
*/
$.fn.richtext.normalize = function ($selection, wrap) {
	var args = arguments;
	// default action is to wrap textNodes
	if(args.length == 1) {
		args[1] = true;
	}
	return $.each($selection, function(i, node) {
		var $node = $(this);
		// A) wrap textNodes into span tags
		if(args[1]) {
			if(this.nodeType == 3) {
				$selection[i] = $node.wrap($('<span/>')).parent()[0];
			}
		}
		// B) remove unnecessary span wrappers
		else {
			if(this.nodeType == 1 && $node.prop('nodeName').toUpperCase() == 'SPAN') {
				var style_attr = $node.attr('style');
				// if node has no style left, convert it to textNode
				if($node[0].innerHTML.length === 0 || style_attr === undefined || style_attr.length === 0) {
					// $.children() doesn't seem to handle textNodes properly
					var $children = $( $node[0].childNodes );
					$node.before($children.detach()).remove();
					// update selection
					$selection[i] = $children[0];
				}
			}
		}
	});
};

$.fn.richtext.updateSelection = function($selection) {
	var range = rangy.getSelection().getRangeAt(0);
	if($selection.length) {
		range.setStartBefore($selection.first()[0]);
		range.setEndAfter($selection.last()[0]);
		range.select();
	}
};


/**
* $.fn.richtext.buttons property holds the definitions of the available buttons for the richtext plugin
* At first, we define some basic buttons,  more are defined below by extending this property
*/
$.fn.richtext.buttons = {
	'Maximize': {
		icon: 'ui-icon-editor-maximize',
		type: 'action',
		click: function ($editor, conf) {
			var $toolbar = $('.ui-editor-toolbar', $editor);
			if(conf.maximized) {
				conf.maximized = false;
				// restore size
				$editor.css({'position': 'relative', 'width': conf.width, 'height': conf.height});
				// restore parent
				conf.parent.append($editor.detach());
				// restore body children
				$('body').append(conf.body_children);
			}
			else {
				conf.maximized = true;
				// save the minimized width
				conf.width = $editor.css('width');
				// save original parent
				conf.parent = $editor.parent();
				// save original body content
				conf.body_children = $('body').children().detach();
				$editor
				.detach()
				.appendTo('body')
				.css({'position': 'absolute', 'width': '100%', 'height': '100%', 'z-index': '9999', 'top': '0px', 'left': '0px'});
			}
			$('.ui-editor-content,.ui-editor-source', $editor).css('height', (parseInt($editor.css('height'))-parseInt($toolbar.css('height'))-6)+'px');
		}
	},
	'Source': {
		icon: 'ui-icon-editor-source',
		type: 'action',
		click: function ($editor, conf) {
			var $content = $('.ui-editor-content', $editor);
			var $source = $('.ui-editor-source', $editor);
			if(conf.mode == 'wysiwyg') {
				conf.mode = 'source';
				$source.val($content.html());
				// disable all buttons except this one
				$('.ui-button', $editor).not($('.ui-button[title="Source"]', $editor)).button({ disabled: true });
			}
			else if(conf.mode == 'source') {
				conf.mode = 'wysiwyg';
				$content.html($source.val());
				$('.ui-button', $editor).button({ disabled: false });
			}
			$content.toggle();
			$source.toggle();
		}
	},
	'Italic': {
		icon: 'ui-icon-editor-italic',
		type: 'style',
		method: 'css',
		style: {
			property: 'font-style',
			value: 'italic'
		}
	},
	'Underline': {
		icon: 'ui-icon-editor-underline',
		type: 'style',
		method: 'css',
		style: {
			property: 'text-decoration',
			value: 'underline'
		}
	},
	'Strike': {
		icon: 'ui-icon-editor-strike',
		type: 'style',
		method: 'css',
		style: {
			property: 'text-decoration',
			value: 'line-through'
		}
	},
	'Bold': {
		icon: 'ui-icon-editor-bold',
		type: 'style',
		method: 'css',
		style: {
			property: 'font-weight',
			value: 'bold'
		}
	},
	'Subscript': {
		icon: 'ui-icon-editor-subscript',
		type: 'style',
		method: 'wrap',
		tag: 'sub'
	},
	'Superscript' : {
		icon: 'ui-icon-editor-superscript',
		type: 'style',
		method: 'wrap',
		tag: 'sup'
	},
	'RemoveFormat': {
		icon: 'ui-icon-editor-removeformat',
		type: 'action',
		click: function($editor, conf) {
			$.fn.richtext.updateSelection($.fn.richtext.normalize($editor.selection().removeAttr('style'), false));
		}
	},
	'Blockquote': {
		icon: 'ui-icon-editor-blockquote',
		type: 'style',
		method: 'wrapAll',
		tag: 'blockquote'
	},
	'Anchor': {
		icon: 'ui-icon-editor-anchor',
		type: 'action',
		click: function($editor, conf) {
		}
	},
	'Link': {
		icon: 'ui-icon-editor-link',
		type: 'action',
		click: function($editor, conf) {
		}
	},
	'Image': {
		icon: 'ui-icon-editor-image',
		type: 'action',
		click: function($editor, conf) {
		}
	},
	'Table': {
		icon: 'ui-icon-editor-table',
		type: 'action',
		click: function($editor, conf) {
		}
	}
};

/**
*	Main actions
*/
$.extend(true, $.fn.richtext.buttons, {
	'Undo': {
		icon: 'ui-icon-editor-undo',
		type: 'action',
		click: function ($editor, conf) {
			document.execCommand('undo', false, null);
		}
	},
	'Redo': {
		icon: 'ui-icon-editor-redo',
		type: 'action',
		click: function ($editor, conf) {
			document.execCommand('redo', false, null);
		}
	},
	'Cut': {
		icon: 'ui-icon-editor-cut',
		type: 'action',
		click: function ($editor, conf) {
			var range = rangy.getSelection().getRangeAt(0);
			conf.clipboard = range.extractContents();
		}
	},
	'Copy': {
		icon: 'ui-icon-editor-copy',
		type: 'action',
		click: function ($editor, conf) {
			var range = rangy.getSelection().getRangeAt(0);
			conf.clipboard = range.cloneContents();
		}
	},
	'Paste': {
		icon: 'ui-icon-editor-paste',
		type: 'action',
		click: function ($editor, conf) {
			var range = rangy.getSelection().getRangeAt(0);
			range.splitBoundaries();
			$(range.startContainer).after($(conf.clipboard.childNodes));
		}
	}
});

/**
*	Text justify
*/
$.extend(true, $.fn.richtext.buttons, {
	'JustifyLeft': {
		icon: 'ui-icon-editor-justifyleft',
		type: 'style',
		method: 'wrapAll',
		tag: 'div',
		style: {
			property: 'text-align',
			value: 'left'
		}
	},
	'JustifyCenter': {
		icon: 'ui-icon-editor-justifycenter',
		type: 'style',
		method: 'wrapAll',
		tag: 'div',
		style: {
			property: 'text-align',
			value: 'center'
		}
	},
	'JustifyRight': {
		icon: 'ui-icon-editor-justifyright',
		type: 'style',
		method: 'wrapAll',
		tag: 'div',
		style: {
			property: 'text-align',
			value: 'right'
		}
	},
	'JustifyBlock': {
		icon: 'ui-icon-editor-justifyblock',
		type: 'style',
		method: 'wrapAll',
		tag: 'div',
		style: {
			property: 'text-align',
			value: 'justify'
		}
	}
});

/*
*	Lists and indentation
*/
$.extend(true, $.fn.richtext.buttons, {
	'NumberedList': {
		icon: 'ui-icon-editor-numberedlist',
		type: 'action',
		click: function($editor, conf) {
			var $selection = $editor.selection();
			if($selection.length === 0) {
				var range = rangy.getSelection().getRangeAt(0);
				range.splitBoundaries();
				$(range.startContainer).after($('<ol><li/></ol>'));
			}
			else {
				$.fn.richtext.normalize($selection).wrapAll('<ol><li/></ol>');
			}
		}
	},
	'BulletedList': {
		icon: 'ui-icon-editor-bulletedlist',
		type: 'action',
		click: function($editor, conf){
			var $selection = $editor.selection();
			if($selection.length === 0) {
				var range = rangy.getSelection().getRangeAt(0);
				range.splitBoundaries();
				$(range.startContainer).after($('<ol><li/></ol>'));
			}
			else {
				$.fn.richtext.normalize($selection).wrapAll('<ul><li/></ul>');
			}
		}
	},
	'Outdent': {
		icon: 'ui-icon-editor-outdent',
		type: 'action',
		click: function($editor, conf){

		}
	},
	'Indent': {
		icon: 'ui-icon-editor-indent',
		type: 'action',
		click: function($editor, conf) {
		}
	}
});

/**
*	Text color
*	This button requires evol.colorpicker plugin
*/
$.extend(true, $.fn.richtext.buttons, {
	'TextColor': {
		icon: 'ui-icon-editor-textcolor',
		type: 'action',
		click: function($editor, conf) {
			var $picker = $('#qRtColorPicker', $editor);
			// at first call, instanciate the color picker
			if($picker.length === 0) {
				$picker = $('<div/>')
					.attr('id', 'qRtColorPicker')
					.css({'position': 'absolute'})
					.colorpicker({color:'#31859b'})
					.on('change.color', function(event, color){
							$.fn.richtext.normalize($editor.selection()).css('color', color);
							$picker.hide();
					})
					.appendTo($('.ui-editor-toolbar', $editor));
					// hide color picker if user clicks somewhere else on the editor
					$editor.on('click.colorpicker', function () {
						$picker.hide();
					});
			}
			else {
				// color picker already exists, clicking the button toggles its visibility
				$picker.toggle();
			}
			return false;
		}
	}
});

})(jQuery);