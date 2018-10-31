/*! qinoa.api - v1.0.0
* https://github.com/cedricfrancoys/qinoa
* Copyright (c) 2015 Cedric Francoys; Licensed GPLv3 */

/**
* Singleton implementation for qinoa
*
* qinoa main API
*/

var qinoa = {
	/* context data */
	conf: {
		user_id: 0,
		user_key: 0,
		// user_lang is the language in which the UI is displayed (set once and for all)
		user_lang: 'fr',
		// content_lang is the language in which multilang fields values are displayed (on demand)
        content_lang: 'fr',


 		// locale       
		DEFAULT_LANG:                       'fr',
		QN_DATE_FIRST_DAY_OF_WEEK:			2,						// 1=>Sunday,7=>Saterday
		QN_DATE_FORMAT:						'dd/mm/yy',
		QN_TIME_FORMAT:						'hh:mm:ss',
		QN_DATETIME_FORMAT:					'dd/mm/yy hh:mm:ss',
		QN_NUMERIC_THOUSANDS_SEPARATOR:		'.',
		QN_NUMERIC_DECIMAL_POINT:			',',
		QN_NUMERIC_DECIMAL_PRECISION:		2,
		QN_CURRENCY_SYMBOL:					'€',
		QN_CURRENCY_SYMBOL_INT:				'EUR',
		QN_CURRENCY_FORMAT:					'#.##0,00€',


		UNKNOWN_ERROR:	 0,
		MISSING_PARAM:	 1,
		INVALID_PARAM:	 2,
		SQL_ERROR:		 4,
		UNKNOWN_OBJECT:	 8,
		NOT_ALLOWED:	16


// todo : add other conf vars ?
// yes : export constants from PHP
		// UPLOAD_MAX_FILE_SIZE
		// auto_save_delay
	},


	/* constants */

	error_codes:{
		 0: "unknown error(s)",
		 1: "one or more mandatory parameters are missing",
		 2: "invalid parameters or wrong values",
		 4: "SQL errors",
		 8: "unknown resource (class, object, view, ...)",
		16: "action violates some rule or privilege"
	},

	simple_types: ['boolean', 'integer', 'float', 'string', 'short_text', 'text', 'date', 'time', 'datetime', 'timestamp', 'selection', 'binary', 'many2one'],

	init: function(conf) {
		$.extend(this.conf, conf);
	},

	/*
	* 	ObjectManager methods
	*/
    create: function(class_name, fields, lang) {
		var deferred = $.Deferred();
		$.each(arguments, function (i, arg) {
			if(arg === null) {
                console.log('0');
				deferred.reject(qinoa.conf.MISSING_PARAM);
				return deferred.promise();
			}
		});
		$.ajax({
			type: 'GET',
			url: 'index.php?do=core_objects_create',
			dataType: 'json',
			// note: if we don't want to request complex fields, remember to list every simple fields we need
			data: {
				fields: fields,
				class_name: class_name,
				lang: lang
			},
			contentType: 'application/json; charset=utf-8'
		})
		.done(function (data){
			try {
				if(typeof data != 'object' || typeof data.result == 'undefined' || parseInt(data.result) == NaN) 
                        throw Error(qinoa.conf.UNKNOWN_ERROR);
				if(data.result < 0)	                
                        throw Error(-data.result);
				deferred.resolve(data.result);
			}
			catch(e) { deferred.reject(e.message); }
		})
		.fail(function() { deferred.reject(qinoa.conf.UNKNOWN_ERROR); });
		return deferred.promise();        
    },

	/**
	* Returns either an error code or an associative array containing, for every requested object id, an array maping each selected field to its value.
	*
	*/
	read: function(class_name, ids, fields, lang) {
		var deferred = $.Deferred();
		$.each(arguments, function (i, arg) {
			if(arg === null) {
				deferred.reject(qinoa.conf.MISSING_PARAM);
				return deferred.promise();
			}
		});
		$.ajax({
			type: 'GET',
			url: 'index.php?get=core_objects_read',
			dataType: 'json',
			// note: if we don't want to request complex fields, remember to list every simple fields we need
			data: {
				fields: fields,
				class_name: class_name,
				ids: ids,
				lang: lang
			},
			contentType: 'application/json; charset=utf-8'
		})
		.done(function (data){
			try {
				if(typeof data != 'object') 
                    throw Error(qinoa.conf.UNKNOWN_ERROR);			
				if(typeof data.result != 'object') {
                    if(parseInt(data.result) != NaN) throw Error(data.result);
                    else throw Error(qinoa.conf.UNKNOWN_ERROR);
                }
				deferred.resolve(data.result);
			}
			catch(e) { deferred.reject(e.message); }
		})
		.fail(function() { deferred.reject(qinoa.conf.UNKNOWN_ERROR); });
		return deferred.promise();
	},

	/**
	*
	*	note : this method does not work for the 'binary' type (form control uses its own routine for posting binaries)
	*/
	write: function(class_name, ids, values, lang) {
		var deferred = $.Deferred();
		$.each(arguments, function (i, arg) {
			if(arg === null) {
				deferred.reject(qinoa.conf.MISSING_PARAM);
				return deferred.promise();
			}
		});
		$.ajax({
			type: 'POST',
			url: 'index.php?do=core_objects_write',
			dataType: 'json',
			data: $.extend({
				class_name: class_name,
				ids: ids,
				lang: lang
			}, values),
			// note : this MIME content-type does not allow binary data (FILE elements)
			contentType: 'application/x-www-form-urlencoded; charset=utf-8'
		})
		.done(function (data){
			try {
				if(typeof data != 'object')			
                    throw Error(qinoa.conf.UNKNOWN_ERROR);
                if(typeof data.result != 'boolean') {
                    if(parseInt(data.result) != NaN) throw Error(data.result);
                    else throw Error(qinoa.conf.UNKNOWN_ERROR);                      
                }
				deferred.resolve(data.result);
			}
			catch(e) { deferred.reject(e.message); }
		})
		.fail(function() { deferred.reject(qinoa.conf.UNKNOWN_ERROR); });

		return deferred.promise();
	},

	/**
	*
	*
	*/
	search: function(class_name, domain, order, sort, start, limit, lang) {
		var deferred = $.Deferred();
		if(class_name === null) {
			deferred.reject(qinoa.conf.MISSING_PARAM);
		}
		else {
			var values = {
				class_name: class_name,
				domain: [[[]]],
				lang: qinoa.conf.DEFAULT_LANG
			};
/*
			$.each(arguments, function (i, value) {
			});
*/
			if(domain !== null) values.domain = domain;
			if(order  !== null) values.order = order;
			if(sort   !== null) values.sort = sort;
			if(start  !== null) values.start = start;
			if(limit  !== null) values.limit = limit;
			if(lang   !== null) values.lang = lang;

			$.ajax({
				type: 'GET',
				url: 'index.php?get=core_objects_search',
				dataType: 'json',
				data: values,
				contentType: 'application/x-www-form-urlencoded; charset=utf-8'
			})
			.done(function (data){
				try {
					if(typeof data != 'object')			throw Error(qinoa.conf.UNKNOWN_ERROR);
					if(typeof data.result == 'number')	throw Error(data.result);
					if(typeof data.result != 'object')	throw Error(qinoa.conf.UNKNOWN_ERROR);
					deferred.resolve(data.result);
				}
				catch(e) { deferred.reject(e.message); }
			})
			.fail(function() { deferred.reject(qinoa.conf.UNKNOWN_ERROR); });
		}
		return deferred.promise();
	},

	/**
	*
	*
	*/
	remove: function(class_name, ids, permanent) {
		var deferred = $.Deferred();
		$.each(arguments, function (i, arg) {
			if(arg === null) {
				deferred.reject(qinoa.conf.MISSING_PARAM);
				return deferred.promise();
			}
		});
		$.ajax({
			type: 'GET',
			url: 'index.php?do=core_objects_remove',
			dataType: 'json',
			data: {
				class_name: class_name,
				ids: ids,
				permanent: Number(Boolean(permanent))
			},
			contentType: 'application/json; charset=utf-8',
		})
		.done(function(data) {
			try {
				if(typeof data != 'object')			throw Error(qinoa.conf.UNKNOWN_ERROR);
				if(typeof data.result == 'number')	throw Error(data.result);
				if(typeof data.result != 'object')	throw Error(qinoa.conf.UNKNOWN_ERROR);
				deferred.resolve(data.result);
			}
			catch(e) { deferred.reject(e.message); }
		})
		.fail(function() { deferred.reject(qinoa.conf.UNKNOWN_ERROR); });
		return deferred.promise();
	},

	/**
	*
	*
	*/
// todo : undelete (set deleted field to 0 if object_id is found)
	restore: function(class_name, ids) {
		var deferred = $.Deferred();
		$.ajax({
			type: 'GET',
			url: 'index.php?do=core_objects_restore',
			async: false,
			dataType: 'json',
			data: {
				object_class: class_name,
				ids: ids
			},
			contentType: 'application/json; charset=utf-8',
			success: function(data){
					if(!data) alert("Unable to remove object : check user's permissions");
					else deferred.resolve(data);
			},
			error: function(e){
			}
		});
		return deferred.promise();
	},

	/*
	*	i18n methods
	*/

	/**
	*	import specified locale into current config
	*
	*/
	get_locale: function(locale) {
		var deferred = $.Deferred();
		$.ajax({
			type: 'GET',
			url: 'index.php?get=core_i18n_locale',
			dataType: 'json',
			data: {
				locale: locale
			},
			contentType: 'application/json; charset=utf-8'
		})
		.done(function (data){
			try {
				if(typeof data != 'object')			throw Error(qinoa.conf.UNKNOWN_ERROR);
				if(typeof data.result == 'number')	throw Error(data.result);
				if(typeof data.result != 'object')	throw Error(qinoa.conf.UNKNOWN_ERROR);
				// extend/erase qinoa configuration with received object
				$.extend(qinoa.conf, data.result);
			}
			catch(e) { deferred.reject(e.message); }
		})
		.fail(function() { deferred.reject(qinoa.conf.UNKNOWN_ERROR); });

		return deferred.promise();
	},

	/*
	* 	AccessController methods
	*/

	/**
	*
	*
	*/
	lock: function (key, value) {
		if(typeof(value) == 'number') value = value.toString();
		if(typeof(key) == 'number') key = key.toString();
		if(value.length == 32) {
			var hex_prev = function (val) {
				var hex_tab = '0123456789abcdef';
				var prev = parseInt(val, 16) - 1;
				if(prev < 0) prev = 15;
				return hex_tab.charAt(prev);
			};
			for(var i = 0; i < key.length; ++i) {
				pos =  parseInt(key.charAt(i));
				hex_val = hex_prev(value.charAt(pos));
				value = value.substring(0,pos) + hex_val + value.substring(pos+1);
			}
		}
		return value;
	},

	/**
	*
	*
	*/
	login: function(login, password) {
		var deferred = $.Deferred();
		$.ajax({
			type: 'GET',
			url: 'index.php?get=core_user_login',
			async: true,
			dataType: 'json',
			data: {
				login: login,
				password: password
			},
			contentType: 'application/json; charset=utf-8',
			success: function(data){
				var res = false;
				if(typeof data.result == 'number') qinoa.console.log('Error raised by qinoa.login(): '+qinoa.error_codes[data.result]);
				else res = data.result;
				deferred.resolve(res);
			},
			error: function(e){
				deferred.resolve(false);
			}
		});
		return deferred.promise();
	},

	/**
	*
	*
	*/
	user_id: function () {
		var deferred = $.Deferred();
		if(!qinoa.conf.user_id) {
			$.ajax({
				type: 'GET',
				url: 'index.php?get=core_user_id',
				async: true,
				dataType: 'json',
				contentType: 'application/json; charset=utf-8',
				success: function(data){
					var res = false;
					if(data.result < 0) qinoa.console.log('Error raised by qinoa.user_key(): '+qinoa.error_codes[-data.result]);
					else if(typeof data.result == 'number') res = data.result;
					qinoa.conf.user_id = res;
					deferred.resolve(res);
				},
				error: function(e){
					qinoa.conf.user_id = false;
					deferred.resolve(false);
				}
			});
		}
		else deferred.resolve(qinoa.conf.user_id);
		return deferred.promise();
	},

	/**
	*
	*
	*/
	user_key: function () {
		var deferred = $.Deferred();
		if(!qinoa.conf.user_key) {
			$.ajax({
				type: 'GET',
				url: 'index.php?get=core_user_key',
				async: true,
				dataType: 'json',
				contentType: 'application/json; charset=utf-8',
				success: function(data){
					var res = false;
					if(typeof data.result == 'number') qinoa.console.log('Error raised by qinoa.user_key(): '+qinoa.error_codes[data.result]);
					else if(typeof data.result == 'string') res = data.result;
					qinoa.conf.user_key = res;
					deferred.resolve(res);
				},
				error: function(e){
					qinoa.conf.user_key = false;
					deferred.resolve(false);
				}
			});
		}
		else deferred.resolve(qinoa.conf.user_key);
		return deferred.promise();
	},

	/**
	*
	*
	*/
	user_lang: function () {
		var deferred = $.Deferred();
		if(!qinoa.conf.user_lang) {
			$.ajax({
				type: 'GET',
				url: 'index.php?get=core_user_lang',
				async: true,
				dataType: 'json',
				contentType: 'application/json; charset=utf-8',
				success: function(data){
					var res = false;
					if(typeof data.result == 'number') qinoa.console.log('Error raised by qinoa.user_lang(): '+qinoa.error_codes[data.result]);
					else if(typeof data.result == 'string') res = data.result;
					qinoa.conf.user_lang = res;
					deferred.resolve(res);
				},
				error: function(e){
					qinoa.conf.user_lang = false;
					deferred.resolve(false);
				}
			});
		}
		else deferred.resolve(qinoa.conf.user_lang);
		return deferred.promise();
	}
};


/**
* examples of qinoa standard API usage
*

// call read method
$.when(qinoa.read('knine\\Article', article_id, ['title','authors_ids'], lang))
.done(function(result) {
		$.each(result, function(id, values) {
			alert('id '+id+' : '+values['title']);
		});
})
.fail(function (code) {
	qinoa.console.log('Error raised by qinoa.read(): '+qinoa.error_codes[code]);
});

// get the schema of a specific class
$.when(qinoa.get_schema('icway\\Page'))
.done(function(result) {console.log(result);});

// get fields names involved in a specific view
$.when(qinoa.get_fields('icway\\Post', 'form.default'))
.done(function(result) {
	console.log(Object.keys(result));
});

*/