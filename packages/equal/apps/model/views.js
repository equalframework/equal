'use strict';


// Instanciate resiway module
var project = angular.module('project', [
    // dependencies
    'ngCookies',
    'ui.bootstrap',
    'oi.select'
])


/**
* Set HTTP POST format to URLENCODED (instead of JSON)
*
*/
.config([
    '$httpProvider', 
    '$httpParamSerializerJQLikeProvider', 
    function($httpProvider, $httpParamSerializerJQLikeProvider) {
        // Use x-www-form-urlencoded Content-Type
        $httpProvider.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';    
        $httpProvider.defaults.paramSerializer = '$httpParamSerializerJQLike';    
        $httpProvider.defaults.transformRequest.unshift($httpParamSerializerJQLikeProvider.$get());
    }
])

/**
* Enable HTML5 mode
*
*/
.config([
    '$locationProvider', 
    function($locationProvider) {
        // ensure we're in Hashbang mode
        $locationProvider.html5Mode({enabled: true, requireBase: true, rewriteLinks: true}).hashPrefix('!');
    }
])

.factory('httpRequestInterceptor', [
    '$cookies',    
    function ($cookies) {
        return {
            request: function (config) {
                config.headers['Authorization'] = 'Bearer ' + $cookies.get('access_token');
                return config;
            }
        };
    }
])

.config(['$httpProvider', function ($httpProvider) {
  $httpProvider.interceptors.push('httpRequestInterceptor');
}])

.run( [
    '$window', 
    '$timeout', 
    '$rootScope', 
    '$location',
    '$cookies',
    '$http',
    function($window, $timeout, $rootScope, $location, $cookies, $http) {
        console.log('run method invoked');
        
        // @init
        
        // flag indicating that some content is being loaded
        $rootScope.viewContentLoading = true;   

      

        // @events

        // This is triggered afeter loading, when DOM has been processed
        angular.element(document).ready(function () {
            console.log('dom ready');
            $rootScope.viewContentLoading = false;			
        });
        
        // when requesting another location (user click some link)
        $rootScope.$on('$locationChangeStart', function(angularEvent) {
            // mark content as being loaded (show loading spinner)
            $rootScope.viewContentLoading = true;
        });


        /**
        * This callback is invoked at each change of view
        * it is used to complete any pending action
        */
        $rootScope.$on('$viewContentLoaded', function(params) {
            console.log('$viewContentLoaded received');
            // hide loading spinner
            $rootScope.viewContentLoading = false;
        });            
    }
])

/**
*
* we take advantage of the rootController to define globaly accessible utility methods
*/
.controller('rootController', [
    '$rootScope', 
    '$scope',
    '$location',
    '$http',
	'$uibModal',
    function($rootScope, $scope, $location, $http, $uibModal) {
        console.log('root controller');

        var rootCtrl = this;
		
		// @def
        var classStruct = {
			name: 'new_class',
			parent: 'qinoa\\orm\\Model',
			fields: []
		};
		
        var fieldStruct = {
			name: 'new_field',
			type: 'integer',
			attributes: []
		};
		
		
		
		$scope.classes = [];
		$scope.views = [];
		
		$scope.params = {
			types: [],
			fields: [],
			classes: ['qinoa\\orm\\Model'],
			selected_class: -1,
			selected_view: 'create',
			selected_field: -1
		};
		

		
        // @init
		$http({
            method: 'GET',
            url: '/index.php?get=qinoa_config_types'
        })
        .then(
            function success(json) {
				$scope.params.types = Object.keys(json.data);
				$scope.params.defs = json.data;
            },
            function error() {
            }        
		);
		
		$http({
            method: 'GET',
            url: '/index.php?get=qinoa_config_classes'
        })
        .then(
            function success(json) {
				console.log(json.data);
				for(let pkg in json.data) {					
					for(let cls of json.data[pkg]) {
						$scope.params.classes.push(pkg + '\\' +cls);
					}
				}
            },
            function error() {
            }        
		);
		
		
		$http({
			method: 'GET',
			url: '/index.php?get=qinoa_config_classes&package='+global_config['package']
		})
		.then(
			function success(json) {				
						
				angular.forEach(json.data, function (cls, key) {
										
					$http({
						method: 'GET',
						url: '/index.php?get=qinoa_model_schema&entity='+global_config['package']+'\\'+cls
					})
					.then(
						function success(json) {
						    var class_def = {name: cls, fields: []};
							var exclusions = ['id', 'created', 'creator', 'modified', 'modifier', 'deleted', 'state'];
							for(let field in json.data.fields) {
								if(exclusions.indexOf(field) > -1) continue;
								var type = json.data.fields[field].type;
								var attributes = angular.merge({}, $scope.params.defs[type], json.data.fields[field]);
								delete attributes.type;
								for(let attr in attributes) {
									var attr_val = attributes[attr];
									var val = '';
									if(typeof $scope.params.defs[type][attr] == 'undefined') {
										delete attributes[attr];
									}
									else {
										if(attr == 'foreign_object') {
											val = attr_val.split('\\').pop();
										}									
										else if(attr == 'selection' && type == 'string') {

											if(val.constructor == Array) {
												val = attr_val.join(',');
											}
											else {
												val = '';
											}
										}
										else if(attr == 'multilang') {
											val = (attr_val == true || attr_val === 'true');
										}
										attributes[attr] = $scope.params.defs[type][attr];
										attributes[attr].value = val;									
									}
								}
								class_def.fields.push({name: field, type: type, attributes: angular.merge({}, attributes)});
							}
							class_def.parent = json.data.parent;
							$scope.classes.unshift(angular.copy(class_def));
							return json.data;
						},
						function error() {
							return null;
						}        
					);
					
					
				});
			
				return json.data;
			},
			function error() {
				return null;
			}        
		);	



		$scope.getFields = function(class_name) {
			for(let i in $scope.classes) {
				if($scope.classes[i].name == class_name) {
					return $scope.classes[i].fields;
				}
			}
		}
		
		$scope.save = function() {

			
			var class_def = $scope.classes[$scope.params.selected_class];
			
			console.log(class_def);
			
			var modalInstance = $uibModal.open({
				template: '<div class="modal-header"><h3>Confirm</h3></div><div class="modal-body"><p>This will overwrite existing object definition along with embedded methods, key and constraint defintion.</p> <p>Are you sure?</p></div><div class="modal-footer"><button class="btn btn-success" ng-click="ctrl.ok()">OK</button><button class="btn btn-warning" ng-click="ctrl.cancel()">Cancel</button></div>',
				controller: ['$uibModalInstance', function ($uibModalInstance, items) {
					var ctrl = this;
					ctrl.ok = function () {
						$uibModalInstance.close();
					};
					ctrl.cancel = function () {
						$uibModalInstance.dismiss();
					};
				}],
				controllerAs: 'ctrl',
				size: 'sm',
				appendTo: angular.element(window.document.querySelector(".modal-wrapper")),
				resolve: {
					items: function () {
						return [];
					}
				}					
			});


			modalInstance.result.then(
				function () {
					$http.post('index.php?do=qinoa_config_save-model', {
						schema: class_def,
						package: global_config.package,
						entity: class_def.name
					})
					.then(
						function (response) {
							console.log(response.data);
						}
					);
				}, 
				function () {
					console.log('cancel');
				}
			);

		}
		
		$scope.select_field = function(index) {
			$scope.params.selected_field = index;
			
			for(let attr in $scope.classes[$scope.params.selected_class].fields[$scope.params.selected_field].attributes) {
				if(attr == 'foreign_field') {
					$scope.select_target_class(0, 'foreign_object');
				}
			}
		}
		
		$scope.select_class = function(index) {
			$scope.params.selected_class = index;
			
			if($scope.classes[$scope.params.selected_class].fields.length == 0) {
			}
		}
		

		$scope.add_field = function () {
			console.log($scope.params);
			if(typeof $scope.views[$scope.params.selected_view] == 'undefined') {
				$scope.views[$scope.params.selected_view] = [];
			}
			if(typeof $scope.views[$scope.params.selected_view][$scope.params.selected_class] == 'undefined') {
				$scope.views[$scope.params.selected_view][$scope.params.selected_class] = {};
			}
			$scope.views[$scope.params.selected_view][$scope.params.selected_class][$scope.params.selected_field] = angular.copy(fieldStruct);
		}

		$scope.remove_field = function () {
			delete $scope.views[$scope.params.selected_view][$scope.params.selected_class].fields[$scope.params.selected_field];
		}		
		
		$scope.select_target_type = function (index) {
			var selected_type = $scope.classes[$scope.params.selected_class].fields[$scope.params.selected_field].type;
			$scope.classes[$scope.params.selected_class].fields[$scope.params.selected_field].attributes = angular.copy($scope.params.defs[selected_type]);
		}
		
		$scope.select_target_class = function (index, attr) {
			var selected_class = $scope.classes[$scope.params.selected_class].fields[$scope.params.selected_field].attributes[attr].value;
			
		}
		
    }
]);