/**
 * qinoa-ui.qSearchWidget : Plugin for generating SearchGrid widgets
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

/* This object holds the methods for rendering qSearch widgets
*  and can be extended to handle additional widgets
*
*/
qinoa.SearchWidgets = {
	'string': function ($this, conf) {
		var $widget = $('<input type="text"/>')
		.attr({id: conf.id, name: conf.name})
		// set layout and use jquery-UI css
		.addClass('ui-widget')
		.css('margin-right', '10px')
		// assign the specified value
		.val(conf.value);
		return $widget;
	},
	'short_text':  function ($this, conf) {
		return qinoa.SearchWidgets.string($this, conf);
	},	
	'text':  function ($this, conf) {
		return qinoa.SearchWidgets.string($this, conf);
	},	
	'binary':  function ($this, conf) {
		return qinoa.SearchWidgets.string($this, conf);
	},
	'boolean': function ($this, conf) {
		return qinoa.SearchWidgets.string($this, conf);
	},
	'integer': function ($this, conf) {
		return qinoa.SearchWidgets.string($this, conf);
	},
	'float': function ($this, conf) {
		return qinoa.SearchWidgets.string($this, conf);
	},
	'many2one': function ($this, conf) {
		return qinoa.SearchWidgets.string($this, conf);
	},
	'time': function ($this, conf) {
		return qinoa.SearchWidgets.string($this, conf);
	},
	'timestamp': function ($this, conf) {
		return qinoa.SearchWidgets.string($this, conf);
	},	
	'date': function ($this, conf) {
		var $widget = $('<input type="text"/>')
		.attr({id: conf.id, name: conf.name})
		.css('margin-right', '10px')
		.daterangepicker({
			dateFormat: 'yy-mm-dd',
			presetRanges: [
			{	
				text: 'Today', 
				dateStart: 'today', 
				dateEnd: 'today' 
			},
			{
				text: 'The previous Month', 
				dateStart: function(){ return Date.parse('1 month ago').moveToFirstDayOfMonth();  }, 
				dateEnd: function(){ return Date.parse('1 month ago').moveToLastDayOfMonth();  } 
			},
			{
				text: 'The previous Year', 
				dateStart: function(){ return Date.parse('12 months ago').moveToLastDayOfMonth();  }, 
				dateEnd: function(){ return Date.parse('1 day ago');  } 
			}
			],
			presets: {
				specificDate: 'Specific Date',
				dateRange: 'Date Range'
			},
			earliestDate: Date.parse('-70years'),
			latestDate: Date.parse('+20years'),
			datepickerOptions: {
				changeMonth: true, 
				changeYear: true, 
				yearRange: 'c-70:c+20'
			}
		})
		return $widget;
	},
	'datetime': function ($this, conf) {
		return qinoa.SearchWidgets.date($this, conf);
	}
};

$.fn.qSearchWidget = function(conf){

	var default_conf = {
		id: null,
		name: null,
		value: null,
		type: 'string'
	};
	
	return this.each(function() {
		return (function ($this, conf) {
			try {
				if(typeof qinoa.SearchWidgets[conf.type] == 'undefined') throw Error('Error raised in qinoa-ui.qSearchWidget : unknown type '+conf.type);
				var $widget = qinoa.SearchWidgets[conf.type]($this, conf);
				return $this.data('widget', $widget.appendTo($this));
			}
			catch(e) {
				qinoa.console.log(conf.type+' '+conf.name+e.message);
			}
		})($(this), $.extend(true, default_conf, conf));
	});
}
})(jQuery, qinoa);