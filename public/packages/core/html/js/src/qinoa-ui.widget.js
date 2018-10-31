/**
 * qinoa-ui.widget : A plugin generating editable widgets for view controls
 *
 * Author	: Cedric Francoys
 * Launch	: March 2015
 * Version	: 1.0
 *
 * Licensed under GPL Version 3 license
 * http://www.opensource.org/licenses/gpl-3.0.html
 *
 */

// require jquery-1.7.1.js (or later), ckeditor.js, jquery-ui.timepicker.js, easyObject.grid.js, easyObject.dropdownlist.js, easyObject.choice.js
// jquery.inputmask.js

// accepted types are: boolean, float, integer, string, short_text, text, date, time, datetime, timestamp, selection, binary, one2many, many2one, many2many
// and soe additional types : password, image


// @deprecated

(function($){

	$.fn.editable = function(conf){
		var default_conf = {
			name: '',
			value: '',
			mode: 'edit',
			type: 'string',			
			format: '',
			align: 'left',		
			readonly: false,
			required: false,
			onchange: function() {}

		}

		return this.each(function() {
			return (function ($this, conf) {
                $this.attr('mode', conf.mode);
                $this.data('value', conf.value);
				switch(conf.type) {
						case 'boolean':
                            $this
							.on('render', function() {
                                $this.empty();                             
                                $('<input type="checkbox" />')
                                .attr({id: conf.name, name: conf.name})
                                .prop('checked', (parseInt(conf.value) > 0))                               
                                .on('change', function() {
                                    $this.data('value', +(this.checked));
                                    $this.trigger('change');
                                })
                                .val((parseInt($this.data('value')) > 0)?1:0)
                                .appendTo($this);
                            });                                        
							break;
						case 'integer':
							var $widget = $('<input type="text"/>')
												.attr({id: conf.name, name: conf.name})
												.css({'width': '100%', 'text-align': conf.align})
												.val(conf.value)
												.on('change', conf.onchange);
						
							$widget.inputmask("integer",  { allowMinus: true });
							if(conf.readonly) $widget.attr("disabled","disabled");
							
							break;
						case 'float':
							var $widget = $('<input type="text"/>')
												.attr({id: conf.name, name: conf.name})
												.css({'width': '100%', 'text-align': conf.align})
												.val(conf.value)
												.on('change', conf.onchange);
						
							$widget.inputmask("decimal", { radixPoint: "." , digits: 2, autoGroup: false});
							if(conf.readonly) $widget.attr("disabled","disabled");
							
							break;
						case 'string':
							$this
							.on('toggle', function() {
								$this.attr('mode', ($this.attr('mode') == 'view')?'edit':'view');
								$this.trigger('render');
							})
							.on('render', function() {
								$this.empty();
								if($this.attr('mode') == 'edit') {
									$('<input type="text"/>')
									.attr({id: conf.name, name: conf.name})
									.css({'width': '100%', 'text-align': conf.align})									
									.on('change', function() {
										$this.data('value', $(this).val());
										$this.trigger('change');
									})
									.val($this.data('value'))
									.appendTo($this);													
								}
								else {
									$('<div/>')
									.css({'width': '100%', 'text-align': conf.align})									
									.html($this.data('value'))
									.appendTo($this);									
								}						
							});
							break;
						case 'selection':                          
                            $this
							.on('render', function() {
                                $this.empty();
                                var $options = $('<div />');
                                $.each(conf.selection, function(value, display) {
                                    $option = $('<option />').attr('value', value).text(display);
                                    if(value == conf.value) $option.attr('selected', 'selected');
                                    $options.append($option);
                                });                                
                                $('<select />')
                                .attr({id: conf.name, name: conf.name})
                                .css({'width': '100%', 'text-align': conf.align})
                                .append($options.children())                                
                                .on('change', function() {
                                    $this.data('value', $(this).val());
                                    $this.trigger('change');
                                })
                                .val($this.data('value'))
                                .appendTo($this);
                            });
							break;                            
				}
				return $this.trigger('render');
			})($(this), $.extend(true, default_conf, conf));
		});
	};
})(jQuery);
