/**
 * qinoa-ui.qGridCell : A plugin generating editble cells for Grid view controls
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
/* This object holds the methods for rendering qGrid cells
*  and can be extended to handle additional types
*
*/
qinoa.GridCells = {
	'string': function ($this, conf) {
		var $widget;
		if(conf.mode == 'view') {
			$widget = $('<span/>').text(conf.value[conf.id]);
		}
		else if(conf.mode == 'edit') {
			$widget = $('<input type="text"/>')
			// 'name' attribute will generate data at form submission
			.attr({name: conf.name})
			.uniqueId()
			.css({'box-sizing': 'border-box', 'width': '100%', 'text-align': conf.align})
			// assign the specified value
			.val(conf.value[conf.id])
			// define the expected .data('value') method
			.data('value', function() {return $(this).val();});
			if(conf.readonly) $widget.attr('disabled', 'disabled').addClass('ui-state-disabled');
			if(conf.required) $widget.addClass('required');		
		}
		return $widget;
	},
	'boolean': function ($this, conf) {
		return qinoa.GridCells.string($this, conf);
	},    
	'integer': function ($this, conf) {
		return qinoa.GridCells.string($this, conf);
	},
	'float': function ($this, conf) {
		return qinoa.GridCells.string($this, conf);
	},	
	'date': function ($this, conf) {
		// force format conversion<
		// note : we should have receive the date in the right format but widget attribute might override the datetime type
		var value = $.datepicker.formatDate( qinoa.conf.QN_DATE_FORMAT, Date.parse(conf.value[conf.id]) );
		var $widget;
		if(conf.mode == 'view') {
			$widget = $('<span/>').text(value);
		}
		else if(conf.mode == 'edit') {
			$widget = $('<input type="text"/>')
			// 'name' attribute will generate data at form submission
			.attr({name: conf.name})
			.uniqueId()
			.css({'box-sizing': 'border-box', 'width': '100%', 'text-align': conf.align})
			// assign the specified value
			.val(conf.value[conf.id])
			// define the expected .data('value') method
			.data('value', function() {return $(this).val();});
			if(conf.readonly) $widget.attr('disabled', 'disabled').addClass('ui-state-disabled');
			if(conf.required) $widget.addClass('required');		
		}
		return $widget;	
		
	},
	'datetime': function ($this, conf) {
		// force format conversion
		// var value = Date.parse(conf.value[conf.id], qinoa.conf.QN_DATETIME_FORMAT );
		var value = conf.value[conf.id];
		var $widget;		
		if(conf.mode == 'view') {
			$widget = $('<span/>').text(value);
		}
		else if(conf.mode == 'edit') {
			$widget = $('<input type="text"/>')
			// 'name' attribute will generate data at form submission
			.attr({name: conf.name})
			.uniqueId()
			.css({'box-sizing': 'border-box', 'width': '100%', 'text-align': conf.align})
			// assign the specified value
			.val(conf.value[conf.id])
			// define the expected .data('value') method
			.data('value', function() {return $(this).val();});
			if(conf.readonly) $widget.attr('disabled', 'disabled').addClass('ui-state-disabled');
			if(conf.required) $widget.addClass('required');		
		}
		return $widget;	
	},
	'many2one': function ($this, conf) {
		var $widget = $('<a/>')
		.addClass('ui-state-default')
		.attr('href', '#');
		$.each(conf.fields, function (i, field) {
            $widget.append( (i > 0)? ', '+conf.value[field] : conf.value[field] );
		});
		$widget.on('click', function() {
// todo : temporary (might change after edition)
			var object_id = conf.value[conf.id];
// todo : handle views attribute inside LI 			
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
						$this.closest('.ui-grid').trigger('reload');
					}
					$dia.trigger('dialogclose');
				});
				
			});
			
		});
		return $widget;
	}
};

$.fn.qGridCell = function(conf){
	var default_conf = {
		mode:	'view',
		type:	'string'
	}
	return this.each(function() {
		return (function ($this, conf) {
			try {
				if(typeof qinoa.GridCells[conf.type] == 'undefined') throw Error('Error raised in qinoa-ui.qGridCell : unknown type '+conf.type);
				var $widget = qinoa.GridCells[conf.type]($this, conf);
				return $this.data('widget', $widget.appendTo($this));
			}
			catch(e) {
				qinoa.console.log(conf.type+' '+conf.name+e.message);
			}
		})($(this), $.extend(true, default_conf, conf));
	});
}
})(jQuery);