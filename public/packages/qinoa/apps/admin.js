(function() {    
    var url_string = window.location.href;
    var url = new URL(url_string);
    var model = url.searchParams.get("package");
    if(!model) {
        console.log('no model found, stopping');
        
        console.log(angular.element(document.querySelector( '#loader' )));
        angular.element(document.querySelector( '#loader' )).html('missing package argument: nothing to display');
        return;
    }
    
    /* Global object for holding config and specific adpaters methods */
    var qinoa = {
        config: {
            title: 'Admin',
            url: '/index.php',
            entities: {},
            model: model,
            fields: {},      // will be built in ng-admin config phase
// @see https://github.com/marmelab/ng-admin/blob/master/doc/reference/Field.md#referencedlist-field-type
            adapt: function(type) { // translation table from qn types to ng-admin
                var res = type;
                switch(type) {
                    case 'boolean': return 'choice';
                    case 'integer': return 'number';    
                    case 'html': return 'wysiwyg';
                    case 'many2one':  return 'reference';
                    case 'one2many':  return 'referenced_list';
// needed to adjust this : fake one-to-many with rel_table                
                    case 'many2many':  return 'referenced_list';
                }
                return res;
            },
            targetType: function(field, classname) {
                var schema = qinoa.config.entities[classname].schema.fields;
                var target_field  = field;
                var target_type = schema[field]['type'];
                while(target_type == 'function' || target_type == 'alias') {
                    if(schema[target_field].type == 'alias') {
                        target_field = schema[target_field].alias;
                        target_type = schema[target_field].type;
                    }
                    else if(schema[target_field].type == 'function') {
                        target_type = schema[target_field].result_type;
                    }
                }
                return target_type;
            }
            
        }
    };
    
    (function fetchConfig() {
        var $http = angular.injector(["ng"]).get("$http");
        var $q = angular.injector(["ng"]).get("$q");
        var deferred = $q.defer();
        $http({
            method: 'GET',
            url: qinoa.config.url+'?get=qinoa_config_classes&package='+qinoa.config.model
        })
        .then(
            function success(response) {
                
                var promises = [];
                angular.forEach(response.data, function(classname) {
                    var entity = qinoa.config.model+'\\'+classname;
                    qinoa.config.entities[entity] = {schema: {}, views: {}};
                    
                    promises.push($http({
                        method: 'GET',
                        url: '/index.php?get=qinoa_model_schema&entity='+entity
                    }).then(
                        function success(response) {
                            qinoa.config.entities[entity].schema = response.data;
                        }
                    ));
                    
                    var types = ['list', 'show', 'delete', 'edit', 'create']; 
                    
                    for(var type of types) {
                        promises.push($http({
                            method: 'GET',
                            url: '/index.php?get=qinoa_model_view&entity='+entity+'&context='+type
                        }).then(
                            function success(response) {
                                // retrieve requested view type
                                var view_type = response.config.url.split('?')[1].split('&')[2].split('=')[1];
                                qinoa.config.entities[entity].views[view_type] = response.data;
                            },
                            function error(response) {
                                // in case of error (no view defined), fallback to default view
                                // retrieve requested view type
                                var view_type = response.config.url.split('?')[1].split('&')[2].split('=')[1];                                
                                qinoa.config.entities[entity].views[view_type] = [{field: 'id'}, {field: 'name', label: 'Name'}];
                            }
                        ));
                    }
                    
                            
                });
                $q.all(promises).then(function() {
                    setTimeout(function() {
                        deferred.resolve();                    
                    }, 100);
                });
            },
            function error(result) {
                var error = result.data.errors[Object.keys(result.data.errors)[0]];
                angular.element(document.querySelector( '#loader' )).html('Nothing to display: '+error);
                return;
            }
        );       
        return deferred.promise;
    // wait for config data loading completion
    })().then(function (result) {
        
        console.log('init phase');
        console.log(result);
        
        var myApp = angular.module('myApp', ['ng-admin']);
        
        angular.element(document).ready(function() {
            
            
            myApp.run(['$rootScope', 'NgAdminConfiguration', function($rootScope, nga) {
                $rootScope.isReady = true;
                var app = nga();
                console.log(app.menu());
                // fix left menu links
                setTimeout(function() {
                    for(var menu of app._menu._children) {
                        menu._link = encodeURI(menu._link);
                    }
                }, 1000);

            }])
            
            .constant('qnConf', qinoa.config)

            .factory('httpInterceptor', [ 'qnConf', function(qnConf) {  
                var httpInterceptor = {
                    request: function(config) {
                        console.log(config);
                        if(config.url.indexOf('.html') < 0) {       // do not rewrite templates URL !
                            config.url = qnConf.url+'?'+config.params.operation;
                            delete config.params.operation;
                        }
                        return config;
                    },
                    response: function(response) {
                        if(typeof response.data.message == 'undefined') {
                            response.data.message = '';
                        }   
                        return response;
                    }                    
                };
                return httpInterceptor;
            }])

            
            .config(['$httpProvider', function($httpProvider) {  
                $httpProvider.interceptors.push('httpInterceptor');
            }])

            .config(['RestangularProvider', function(RestangularProvider) {
                RestangularProvider.addResponseInterceptor(function(data, operation, what, url, response) {
                    return data;
                });
            }])

            .config(['RestangularProvider', 'qnConf', function (RestangularProvider, qnConf) {

                RestangularProvider.addFullRequestInterceptor(function(element, operation, what, url, headers, params) {

                    params.entity = what;

                    switch(operation) {
                        case 'get':
                            params['fields[]'] = qnConf.fields[params.entity]['show'].concat(qnConf.fields[params.entity]['edit']);
                            params.operation = 'get=qinoa_model_object';
                            params.id = url.substr(url.lastIndexOf('/') + 1);
                            break;
                        case 'getList':                        
                            params['fields[]'] = qnConf.fields[params.entity]['list'];
                            params.operation = 'get=qinoa_model_collection';
                            break;
                        case 'post':
                            // create entity
                            element = { fields: angular.merge({}, element) };                            
                            params.operation = 'do=qinoa_model_create';
                            break;
                        case 'put':
                            // update entity
                            var fields = qnConf.fields[params.entity]['edit'];
                            var result = {};
                            for(var field of fields) {
                                result[field] = element[field];
                            }
                            element = { fields: result };
                            params.operation = 'do=qinoa_model_update';
                            params.id = url.substr(url.lastIndexOf('/') + 1);                            
                            break;
                        case 'remove':
                            params.operation = 'do=qinoa_model_delete';
                            params.id = url.substr(url.lastIndexOf('/') + 1);                            
                            break;
                            
                    }
                    
                    if (operation == "getList") {                    
                        // custom pagination params
                        if (params._page) {
                            params.start = (params._page - 1) * params._perPage;
                            delete params._page;
                        }
                        if (params._perPage) {                        
                            params.limit = params._perPage;
                            delete params._perPage;
                        }
                        if (params._sortField) {
                            params.order = params._sortField;
                            delete params._sortField;
                        }
                        if (params._sortDir) {
                            params.sort = params._sortDir;
                            delete params._sortDir;
                        }
                        // custom filters
                        if (params._filters) {
                            var i = 0;
                                                        
                            for (var field in params._filters) {
                                var target_type = qnConf.targetType(field, params.entity);
                                params['domain['+i+'][0]'] = field;
                                
                                switch(target_type) {
                                    case 'string':
                                    case 'text':
                                        params['domain['+i+'][1]'] = 'ilike';
                                        params['domain['+i+'][2]'] = '%'+params._filters[field]+'%';
                                        break;
                                    case 'one2many':										
                                    case 'many2many':
                                        params['domain['+i+'][1]'] = 'contains';
                                        params['domain['+i+'][2]'] = params._filters[field];
										break;
                                    default:
                                        params['domain['+i+'][1]'] = '=';
                                        params['domain['+i+'][2]'] = params._filters[field];
                                        break;
                                }                                
                            }
                            delete params._filters;
                        }
                    }
                    
                    return { params: params, element: element };
                });
            }])

            
            
            
            .config(['NgAdminConfigurationProvider', 'qnConf', function (nga, qnConf) {
                // CrudModule needs the application to be defined, along with the API root URL
                var app = nga
                    .application(qnConf.title)
                    .baseApiUrl(qnConf.url);


                var entities = qnConf.entities;
                
                // pool containing nga objects
                var ngaConf = {
                    entities: {},
                    views: {}
                };
                // ngaConf.views[{entity}][{view_type}]
                
// @see fields config at : https://github.com/marmelab/ng-admin-demo/blob/master/js/customers/config.js
                
                
                // first pass : create nga entities and related views
                
                
                for(var classname in entities) {
                    var entity = nga.entity(classname);
                    ngaConf.entities[classname] = entity; // as used in API calls
                    ngaConf.views[classname] = [];
                    ngaConf.views[classname]['list'] = entity.listView();
                    ngaConf.views[classname]['show'] = entity.showView();
                    ngaConf.views[classname]['create'] = entity.creationView();
                    ngaConf.views[classname]['edit'] = entity.editionView();
                    ngaConf.views[classname]['delete'] = entity.deletionView();
                    
                    
                    // remember fields names associated to each view
                    qnConf.fields[classname] = {}; 
                    for(var view_type in entities[classname]['views']) {
                        qnConf.fields[classname][view_type] = [];
                        for(item of entities[classname].views[view_type]) {
                            qnConf.fields[classname][view_type].push(item.field);                            
                        }
                    }
                        
                }
                
                var buildFields = function(classname, view_type) {
                    
                    var view = qnConf.entities[classname].views[view_type];
                    var schema = qnConf.entities[classname].schema.fields;  
                    
                    var fields = [];
                    for(var item of view) {
                        console.log(item);
                        var target_field = item.field;
                        var target_type;
                        
                        if(typeof item.widget != 'undefined') {
                            target_type = item.widget;
                        }
                        else if(typeof schema[item.field].selection != 'undefined') {
                            target_type = 'choice';
                        }                        
                        else {                                
                            target_type = qnConf.adapt(qnConf.targetType(target_field, classname));
                        }
                        

                        var field = nga.field(item.field, target_type);
                        var prepend_fields = [];
                        var append_fields = [];
                        
                        // many2one 
                        if(target_type == 'reference') {
                            field
                            .targetEntity(ngaConf.entities[schema[item.field].foreign_object]) 
                            .targetField(nga.field('name'));
                        }
                        // one2many and many2many
                        else if(target_type == 'referenced_list') {

                            field
                            .targetEntity(ngaConf.entities[schema[item.field].foreign_object])                             
                            .targetReferenceField(schema[item.field].foreign_field) 
                            .targetFields(buildFields(schema[item.field].foreign_object, 'list'))                            
                            .perPage(5);
                            if(view_type == 'edit') {
                                field.listActions(['edit', 'delete'])
                            }
                            if(typeof item.limit == 'number') {
                                field.perPage(item.limit);
                            }
                            
                            append_fields.push(
                                nga.field('')
                                .label('')
                                .template('<span class="pull-left"><ma-filtered-list-button entity-name="'+schema[item.field].foreign_object+'" filter="{ '+schema[item.field].foreign_field+': entry.values.id }" size="sm"></ma-filtered-list-button></span>')
                            );

                        }
                        else if(target_type == 'choice') {
                            var choices = [];
                            if(schema[target_field].type == 'boolean') {
                                choices = [
                                  { value: true, label: 'true' },
                                  { value: false, label: 'false' }
                              ];
                            }
                            else {
                                for(var value of schema[item.field].selection) {
                                    choices.push({value: value, label: value});
                                }                                
                            }
                            console.log('choices');
                            console.log(choices);
                            field.choices(choices);
                        }
                        else if(target_type == 'number') {
                            // adapt formatting base on field role
                            if(item.field == 'id') {
                                field.format('0000');
                            }
                        }                        

                                                   
                        if(typeof item.label != 'undefined') {
                            field.label(item.label);
                        }
                        if(typeof item.link != 'undefined' && item.link) {
                            // has no effect if showView is not defined
                            field.isDetailLink(true).detailLinkRoute('show');                            
                        }
                        
                        if(typeof item.readonly != 'undefined' && item.readonly) {
                            console.log('disabled');
                            field.editable(false);
                        }
                        if(item.field == 'id') {                        
                            field.editable(false);
                        }
                        
                        for(var prepend_field of prepend_fields) {
                            fields.push(prepend_field);
                        }
                        fields.push(field);
                        for(var append_field of append_fields) {
                            fields.push(append_field);
                        }
                    }
                    return fields;
                };
                
                
                for(var classname in entities) {
                    

                    
                    var entity = ngaConf.entities[classname];
                    var schema = entities[classname].schema.fields;
                    
                    for(var view_type in entities[classname]['views']) {
                    
                        var fields = buildFields(classname, view_type);

                        
                        switch(view_type) {
                            case 'list':
                                entity.listView()
                                      .listActions(['edit', 'delete'])
                                      .filters(fields)
                                      .fields(fields);
                                break;                            
                            case 'show':
                                entity.showView().fields(fields);
                                break;                            
                            case 'create':
                                entity.creationView().fields(fields);
                                break;
                            case 'edit':
                                entity.editionView().fields(fields);
                                break;                            
                            case 'delete':
                                entity.deletionView().fields(fields);
                                break;
                        }

                    }
                    
                    app.addEntity(entity);

                }

                // run admin App
                nga.configure(app);
                

            }]);

            angular.bootstrap(document, ['myApp']);
        });        
    });
    
})();


