/*! 
* qinoa-ui - v1.0.0
* https://github.com/cedricfrancoys/qinoa
* Copyright (c) 2015 Cedric Francoys; Licensed GPLv3 */

/**
* This file extends qinoa with UI related methods
*
*/
(function ($, qinoa) {

    /**
    * Keyboard handler for Qinoa console (dialog shows up on 'ctrl + alt + shift')
    *
    */
    $(document).bind('keydown', function(event) {
        if(event.ctrlKey && event.shiftKey && event.altKey) {
            qinoa.console.show();
        }
    });

    $.extend(true, qinoa, {
        /**
        * UI related configuration
        *
        */
        conf: {
            dialog_width: 700
        },

        /**
        * data buffers
        * associative array class=>fields descriptions
        */
        schemas: [],
        i18n: [],
        views: [],
        fields: [],

        /**
        *  dedicated console
        */
        console:{
                stack: $('<p/>'),
                log: function(msg) {
                    this.stack.append(msg + "<br/>");
                },
                show: function() {
                    if(typeof this.dia == 'undefined') {
                        this.dia = $('<div/>')
                                    .append($('<div/>')
                                        .css({'font-size': '11px', 'height': '200px', 'overflow': 'scroll', 'border': 'solid 1px grey'})
                                        .append(this.stack)
                                    )
                                    .dialog({
                                        modal: false,
                                        title: 'Qinoa console',
                                        width: 700,
                                        height: 'auto'
                                    });
                    }
                    this.dia.dialog('open');
                }
        },
        
        dialog: function(conf) {
            conf = $.extend({
                content:    $('<div/>'),
                modal:        true,
                title:        '',
                width:        qinoa.conf.dialog_width,
                height:        'auto',
                minHeight:    100,
                buttons: [
                    {
                        text: "Ok",
                        click: function() {
                            $( this ).dialog( "destroy" );
                        }
                    }
                ],
                position: {
                    my: "center top",
                    at: "center top",
                    of: window
                }
            }, conf);
            // adjust the vertical position of the dialog
            // we need the actual height of rendered content
            // we temporarily append the content to an offscreen DIV (so we keep all events and objects attached to the the content)
            var $temp = $('<div/>').css({'position': 'absolute', 'left': '-10000px'}).append(conf.content).appendTo($('body'));
            var dialog_height = $temp.height() + 50;
            var window_height = $(window).height();
            // if there is any space left, leave one third of it above the dialog
            if(dialog_height < window_height) {
                conf.position = {
                    my: "center top",
                    at: "center top+"+(window_height-dialog_height)/3,
                    of: window
                };
            }
            // don't destroy the content !
            conf.content.detach();
            $temp.remove();
            var $dia = $('<div/>')
            .attr('title', conf.title)
            .append(conf.content)
            .dialog(conf)
            .on('dialogclose', function( event, ui ) {
                $(window).scrollTop(0);
                $dia.dialog('destroy');
            });
            // if dialog height exceeds window height, return to the top
            if(dialog_height > window_height) $(window).scrollTop(0);
            return $dia;
        },
        
        alert: function(conf) {
            var default_conf = {
// todo : translate
                title: 'Alert',
                message: ''
            };
            (function (conf) {
                qinoa.dialog({
                    content:    $('<div/>').css({'padding': '10px'}).html(conf.message),
                    title:        conf.title,
                    buttons:     [
                                    {
// todo : translate
                                        text: "Close",
                                        click: function() { $( this ).dialog( "destroy" ); }
                                    }
                    ]
                });
            })($.extend(default_conf, conf));
        },
        
        confirm: function(conf) {
            var default_conf = {
// todo : translate
                title:        'Confirm',
                message:    ''
            };
            return (function (conf) {
                var deferred = $.Deferred();
                qinoa.dialog({
                    content:     $('<div/>').css('padding', '10px').html(conf.message),
                    title:         conf.title,
                    buttons:    [
                                    {
// todo : translate
                                        text: "Yes",
                                        click: function() {
                                            $(this).dialog( "destroy" );
                                            deferred.resolve();
                                        }
                                    },
                                    {
// todo : translate
                                        text: "No",
                                        click: function() {
                                            $(this).dialog( "destroy" );
                                            deferred.reject();
                                        }
                                    }

                    ]
                });
                return deferred.promise();
            })($.extend(default_conf, conf));
        },
        
        loader: {
            show: function ($item) {
                $item
//                .css('position', 'relative')
                .prepend(
                    $('<div/>')
                    .addClass('qLoader')
                    .append(
                        $('<div/>').addClass('ui-overlay')
                        .append($('<div/>').addClass('ui-widget-overlay'))
                        .append($('<div/>').addClass('ui-widget-shadow ui-corner-all'))
                    )
                    .append(
                        $('<div/>')
                        .addClass('qinoa-ui-loader ui-corner-all')
                    )
                );
            },
            hide: function ($item) {
                $('.qLoader', $item).remove();
            }
        },

        /**
        *  Retrieves specified fields for objects of selected class and matching the given criteria
        *  This method is a combination of the search and read methods and is useful for lists
        */
        find: function(class_name, fields, domain, order, sort, start, limit, lang) {
                var deferred = $.Deferred();
                $.when(qinoa.search(class_name, domain, order, sort, start, limit, lang))
                .done(function (ids) {
                    $.when(qinoa.read(class_name, ids, fields, lang))
                    .done(function (data) {
                        // build resulting object keeping the order provided by the search method
                        var res = {};
                        $.each(ids, function(i, id) {
                            // make sure id is among the returned fields
                            data[id].id = id;
                            res[i] = data[id];
                        });
                        deferred.resolve(res);
                    })
                    .fail(function (code) {
                        deferred.reject(code);
                    });
                })
                .fail(function () {
                    deferred.reject(qinoa.conf.UNKNOWN_ERROR);
                });
                return deferred.promise();
        },



        /**
        * ObjectManager methods
        */
        getObjectPackageName: function (class_name) {
                return class_name.substr(0, class_name.indexOf('\\'));

        },
        
        getObjectName: function(class_name) {
                return class_name.substr(class_name.indexOf('\\')+1);
        },

        /**
        * schema methods
        returns an associative object mapping each field to its description
        */
        get_schema: function(class_name) {
                var deferred = $.Deferred();
                var package_name = qinoa.getObjectPackageName(class_name);
                var object_name = this.getObjectName(class_name);
                if(typeof qinoa.schemas[package_name] == 'undefined') qinoa.schemas[package_name] = [];
                if(typeof qinoa.schemas[package_name][object_name] == 'undefined') {
                    $.ajax({
                        type: 'GET',
                        url: 'index.php?get=core_objects_schema&class_name='+class_name,
                        async: true,
                        dataType: 'json',
                        contentType: 'application/json; charset=utf-8',
                        success: function(data){
                            var res = false;
                            if(typeof data.result == 'number') qinoa.console.log('Error raised by qinoa.get_schema('+class_name+'): '+qinoa.error_codes[data.result]);
                            else if(typeof data.result == 'object') res = data.result;
                            qinoa.schemas[package_name][object_name] = res;
                            deferred.resolve(res);
                        },
                        error: function(e){
                            // data not found
                            qinoa.schemas[package_name][object_name] = false;
                            deferred.resolve(false);
                        }
                    });
                }
                else deferred.resolve(qinoa.schemas[package_name][object_name]);
                return deferred.promise();
        },


        /**
        * i18n methods
        */
        get_lang: function(class_name, lang) {
                var deferred = $.Deferred();
                var package_name = this.getObjectPackageName(class_name);
                if(typeof(qinoa.i18n[package_name]) == 'undefined') qinoa.i18n[package_name] = [];
                if(typeof(qinoa.i18n[package_name][class_name]) == 'undefined') {
                    $.ajax({
                        type: 'GET',
                        // note : we could try to get directly the file URL ('packages/'+package_name+'/i18n/'+lang+'/'+class_name+'.json')
                        // but sometimes browsers are troubled when a 404 occurs for an ajax request

                        url: 'index.php?get=core_i18n_lang&class_name='+class_name+'&lang='+lang,
                        async: true,
                        dataType: 'json',
                        contentType: 'application/json; charset=utf-8',
                        success: function(data){
                            var res = false;
                            if(typeof data.result == 'number') qinoa.console.log('Error raised by qinoa.get_lang('+class_name+', '+lang+'): '+qinoa.error_codes[data.result]);
                            else if(typeof data.result == 'object') res = data.result;
                            qinoa.i18n[package_name][class_name] = res;
                            deferred.resolve(res);
                        },
                        error: function(e){
                            // data not found
                            qinoa.i18n[package_name][class_name] = false;
                            deferred.resolve(false);
                        }
                    });


                }
                else deferred.resolve(qinoa.i18n[package_name][class_name]);
                return deferred.promise();
        },

        /**
        * views methods
        */
        /*
            Returns html from the related view
        */
        get_view: function(class_name, view_name) {
                var deferred = $.Deferred();
                if(class_name === null || view_name === null) {
                    deferred.reject(qinoa.conf.MISSING_PARAM);
                }
                else {
                    var package_name = qinoa.getObjectPackageName(class_name);
                    var object_name     = qinoa.getObjectName(class_name);
                    if(typeof qinoa.views[package_name] == 'undefined') qinoa.views[package_name] = [];
                    if(typeof qinoa.views[package_name][object_name] == 'undefined') qinoa.views[package_name][object_name] = [];
                    if(typeof qinoa.views[package_name][object_name][view_name] == 'undefined') {
                        $.ajax({
                            type: 'GET',
                            // note : we could try to get directly the file URL (''packages/'+package_name+'/views/'+object_name+'.'+view_name+'.html')
                            // but browsers don't always behave nicely when a 404 occurs for an ajax request
                            url: 'index.php?get=core_objects_view&class_name='+class_name+'&view_name='+view_name,
                            async: true,
                            dataType: 'json',
                            contentType: 'application/json; charset=utf-8'
                        })
                        .done(function (data) {
                            try {
                                if(typeof data != 'object')           throw Error(qinoa.conf.UNKNOWN_ERROR);
                                if(typeof data.result == 'number')    throw Error(data.result);
                                if(typeof data.result != 'string')    throw Error(qinoa.conf.UNKNOWN_OBJECT);
                                qinoa.views[package_name][object_name][view_name] = data.result;
                                deferred.resolve(data.result);
                            }
                            catch(e) { deferred.reject(e.message); }
                        })
                        .fail(function () {
                            // if an error occurs, we set value to false to prevent further requests
                            qinoa.views[package_name][object_name][view_name] = false;
                            deferred.reject(qinoa.conf.UNKNOWN_ERROR);
                        });
                    }
                    else deferred.resolve(qinoa.views[package_name][object_name][view_name]);
                }
                return deferred.promise();
        },
        
        /*
            Returns an associatie object mappging each field present in the view to its attributes
        */
        get_fields: function(class_name, view_name) {
                var deferred = $.Deferred();
                if(class_name === null || view_name === null) {
                    deferred.reject(qinoa.conf.MISSING_PARAM);
                }
                else {
                    var package_name = this.getObjectPackageName(class_name);
                    var object_name = this.getObjectName(class_name);
                    if(typeof qinoa.fields[package_name] == 'undefined') qinoa.fields[package_name] = [];
                    if(typeof qinoa.fields[package_name][object_name] == 'undefined') qinoa.fields[package_name][object_name] = [];
                    if(typeof qinoa.fields[package_name][object_name][view_name] == 'undefined') {
                        qinoa.fields[package_name][object_name][view_name] = {};
                        var item_type;
                        switch(view_name.split('.')[0]) {
                            case 'form' :
                                item_type = 'var';
                                break;
                            case 'report' :
                            case 'list' :
                                item_type = 'li';
                                break;
                        }
                        $.when(qinoa.get_view(class_name, view_name))
                        .done(function(result) {
                            // returned view might be set to false
                            if(result) {
                                var $q = $({}); 
                                $('<div/>')
                                .append(result)
                                .find(item_type)
                                .each(function() {
                                    var item = this;
                                    var field = $(this).attr('id');                                
                                    $q.queue(function (next) {                                        
                                        var attributes = {};
                                        $.each(item.attributes, function(i, attr) {
                                            attributes[attr.name] = attr.value;
                                        });
                                        qinoa.fields[package_name][object_name][view_name][field] = attributes;
                                        // handle dot notation: if detected, we have to recurse throught classes to find out target type
                                        var parts = field.split('.');
                                        if(parts.length > 1) {
                                            var $q1 = $({});
                                            $q1.sub_class_name = class_name; 
                                            $q1.sub_package_name = package_name;
                                            $q1.sub_object_name = object_name;                                  
                                            $.each(parts, function(i, part) {
                                                $q1.queue( function (next1) { 
                                                    $.when(qinoa.get_schema($q1.sub_class_name))
                                                    .done(function (result) {
                                                        var type = qinoa.schemas[$q1.sub_package_name][$q1.sub_object_name][part]['type'];
                                                        qinoa.schemas[package_name][object_name][field] = {};
                                                        qinoa.schemas[package_name][object_name][field]['type'] = type;                                          
                                                        
                                                        if(typeof qinoa.schemas[$q1.sub_package_name][$q1.sub_object_name][part]['foreign_object'] != 'undefined') {
                                                            $q1.sub_class_name = qinoa.schemas[$q1.sub_package_name][$q1.sub_object_name][part]['foreign_object'];
                                                            $q1.sub_package_name = qinoa.getObjectPackageName($q1.sub_class_name);
                                                            $q1.sub_object_name = qinoa.getObjectName($q1.sub_class_name);
                                                        }                                                        
                                                        next1(); 
                                                    })
                                                    .fail(function (code) {
                                                        next1(); 
                                                    });
                                                });
                                            });
                                            $q1.queue(function (next1) {
                                                next();
                                            });
                                        }                                     
                                        else next();
                                    });
                                });
                                $q.queue(function (next) {
                                    deferred.resolve(qinoa.fields[package_name][object_name][view_name]); 
                                });
                            }
                            else deferred.reject(qinoa.conf.UNKNOWN_ERROR);
                        })
                        .fail(function (code) {
                            qinoa.console.log('Error in qinoa-ui raised by qinoa.get_view('+class_name+','+view_name+'): '+qinoa.error_codes[code]);
                        });
                    }
                    else deferred.resolve(qinoa.fields[package_name][object_name][view_name]);
                }
                return deferred.promise();
        },

    });
})(jQuery, qinoa);



/**

qinoa UI usage examples

$.when(qinoa.confirm({title: 'test', message: 'confirmation'}))
.done(function() {
    console.log('yes');
})
.fail(function() {
    console.log('no');
});



*/
