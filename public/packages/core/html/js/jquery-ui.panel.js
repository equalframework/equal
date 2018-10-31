/*
 * jQuery UI Panel 0.5
 *
 * Copyright 2011, Elijah Horton (fieryprophet [AT] yahoo.com)
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://jquery.org/license
 *
 * http://www.fieryprophet.com/demo/jqui-panel/
 */
(function($, undefined){

$.widget("ui.panel", {
	options: {
		height: 200,
		width: 400,
		linkWidth: 100,
		click: false,
		flip: false
	},

	_create: function(){
		var self = this,
			options = self.options,
			el = self.element;
		el.addClass("ui-panel ui-widget ui-helper-reset ui-corner-all");
		self._oWidth = el.css("width");
		var p = el.children("div").addClass("ui-panel-content");
		self._pWidth = p.css("width");
		self._pHeight = p.css("height");
		if(options.flip){
			p.addClass('ui-panel-content-flip');
		}
		var ul = el.children("ul").addClass("ui-panel-list ui-widget-header");
		self._lHeight = ul.css("height");
		self._lWidth = ul.css("width");
		if(options.flip){
			ul.addClass('ui-panel-list-flip');
		}
		if(self._multi = (p.length > 1) ? true : false){
			ul.children("li").first().addClass("ui-state-selected ui-state-active");
			p.hide().first().show();
		}
		ul.children("li").addClass("ui-state-default ui-panel-item")
			.mouseover(function(event){self._mouseover(event); return false; })
			.mouseout(function(event){self._mouseout(event); return false; })
			.click(function(event){self._click(event); return false; });
		self.resize();
		$(window).bind("resize", function(event){ self.resize(); });
	},
	
	_mouseover: function(event){
		var self = this,
			options = self.options;
		if(options.mouseover){
			if(false == options.mouseover(event)){
				return false;
			}
		}
		if(options.hover){
			if(false == options.hover(event)){
				return false;
			}
		}
		$(event.target).addClass("ui-state-hover");
	},
	
	_mouseout: function(event){
		var self = this,
			options = self.options;
		if(options.mouseout){
			if(false == options.mouseout(event)){
				return false;
			}
		}
		if(options.hover){
			if(false == options.hover(event)){
				return false;
			}
		}
		$(event.target).removeClass("ui-state-hover");
	},
	
	_click: function(event){
		var self = this,
			options = self.options,
			me = $(event.target);
		if(options.beforeClick){
			if(false === options.beforeClick(event)){
				return false;
			}
		} 
		if(options.click){
			if(false === options.click(event)){
				return false;
			}
		} else if(self._multi){
			var a = self.element.children("div"),
				b = a.slice(me.index(), me.index()+1);
			if(b.length > 0){
				a.hide();
				b.show();
			}
		}
		self.element.children("ul").children("li.ui-state-selected").removeClass("ui-state-selected ui-state-active");
		me.addClass("ui-state-selected ui-state-active");
		if(options.afterClick){
			if(false === options.afterClick(event)){
				return false;
			}
		} 
	},
	
	_setBeforeClick: function(value){
		var self = this,
			options = self.options;
		if(!value){
			return options.beforeClick;
		}
		options.beforeClick = value;
		return self;
	},
	
	_setClick: function(value){
		var self = this,
			options = self.options;
		if(!value){
			return options.click;
		}
		options.click = value;
		return self;
	},
	
	_setAfterClick: function(value){
		var self = this,
			options = self.options;
		if(!value){
			return options.afterClick;
		}
		options.afterClick = value;
		return self;
	},
	
	_height: function(value){
		var self = this,
			options = self.options;
		if(!value){
			return options.height;
		}
		options.height = value;
		return self.resize();
	},
	
	_width: function(value){
		var self = this,
			options = self.options;
		if(!value){
			return options.width;
		}
		options.width = value;
		return self.resize();
	},
	
	_linkWidth: function(value){
		var self = this,
			options = self.options;
		if(!value){
			return options.linkWidth;
		}
		options.linkWidth = value;
		return self.resize();
	},
	
	_flip: function(value){
		var self = this,
			options = self.options;
		if(!value){
			return options.flip;
		}
		options.flip = value;
		return self.flip();
	},
	
	add: function(link, content){
		var self = this,
			el = self.element;
		if(link){
			var d = (typeof link === "string") ? $('<li>' + link + '</li>') : $(link);
			d.addClass("ui-state-default ui-panel-item")
				.mouseover(function(event){self._mouseover(event); return false; })
				.mouseout(function(event){self._mouseout(event); return false; })
				.click(function(event){self._click(event); return false; });
			el.children("ul").append(d);
		}
		if(content){
			var e = (typeof content === "string") ? $('<div>' + content + '</div>') : $(content);
			e.addClass("ui-panel-content");
			el.append(e);
		}
		return self;
	},
	
	remove: function(context, bcontext){
		var self = this,
			el = self.element;
		el.children("ul").find(context || "li.ui-state-active").remove();
		el.children("div").filter(bcontext || ":visible").remove();
		return self;
	},
	
	contents: function(content, context){
		var self = this,
			el = self.element,
			context = context ? $(context) : el.children("div").filter(":visible");
		context.html(content);
		return self;
	},
	
	append: function(content, context){
		var self = this,
			el = self.element,
			context = context ? $(context) : el.children("div").filter(":visible");
		context.append(content);
		return self;
	},
	
	prepend: function(content, context){
		var self = this,
			el = self.element,
			context = context ? $(context) : el.children("div").filter(":visible");
		context.prepend(content);
		return self;
	},
	
	resize: function(width, height, lWidth){
		var self = this,
			options = self.options,
			el = self.element,
			width = options.width = (width || options.width),
			height = options.height = (height || options.height),
			lWidth = options.linkWidth = (lWidth || options.linkWidth);
		el.width(width);
		var ul = el.children("ul");
		var p = el.children("div").height(height);
		ul.height((height > p.outerHeight()) ? height : p.outerHeight()).width((("" + lWidth).search("%") > -1) ? (el.width() * ('.' + parseFloat(lWidth))) : ((lWidth + 100) > width) ? (width - 100) : lWidth);
		p.width((el.width() - ul.outerWidth()) - (p.outerWidth() - p.width()) - 2);
		return self;
	},
	
	flip: function(flip){
		var self = this,
			options = self.options,
			el = self.element,
			flip = options.flip = (flip === undefined ? !options.flip : flip);
		if(flip){
			el.children("ul").addClass("ui-panel-list-flip");
			el.children("div").addClass("ui-panel-content-flip");
		} else {
			el.children("ul").removeClass("ui-panel-list-flip");
			el.children("div").removeClass("ui-panel-content-flip");
		}
		alert(options.flip);
		return self;
	},

	destroy: function() {
		var self = this,
			el = self.element;

		el.removeClass("ui-panel ui-widget ui-helper-reset ui-corner-all").css("width", self._oWidth);
		el.children("ul").removeClass("ui-panel-list ui-widget-header ui-panel-list-flip").css("height", self._lHeight).css("width", self._lWidth).children("li").removeClass("ui-state-default ui-state-selected ui-panel-item").unbind();
		el.children("div").removeClass("ui-panel-content ui-content-panel-flip").css("width", self._pWidth).css("height", self._pHeight);
		return $.Widget.prototype.destroy.call(this);
	},
	
	_setOption: function(key, value){
		var self = this;

		switch (key) {
			case "height":
				self._height(value);
				break;
			case "width":
				self._width(value);
				break;
			case "linkWidth":
				self._linkWidth(value);
				break;
			case "beforeClick":
				self._setBeforeClick(value);
				break;
			case "click":
				self._setClick(value);
				break;
			case "afterClick":
				self._setAfterClick(value);
				break;
			case "flip":
				self._flip(value);
				break;
		}

		$.Widget.prototype._setOption.apply(self, arguments);
	}
});
$.extend($.ui.panel, {
	version: "0.5"
});
})(jQuery);