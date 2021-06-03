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
    function($rootScope, $scope, $location, $http) {
        console.log('root controller');

        var ctrl = this;
        var rootCtrl = this;

        $scope.status = {};
        $scope.apis = {
            list: ['[Select an API]'],
            selected: '[Select an API]'
        };
        
        $scope.selectAPI = function () {
            if($scope.apis.selected == '[Select an API]') return;
            $http({
                method: 'GET',
                url: '/index.php?get=core_config_routes&api='+$scope.apis.selected
            })
            .then(
                function success(json) {
                    $scope.routes = json.data;
                },
                function error() {
                    $scope.apis.selected = 'Select an API';
                }
            );             
        }
        
        // @init
		$http({
            method: 'GET',
            url: '/index.php?get=core_config_apis'
        })
        .then(
            function success(json) {
                console.log(json.data);
                $scope.apis.list = $scope.apis.list.concat(json.data);
            },
            function error() {
            }
        );
        
        $scope.unfoldAll = function() {            
            angular.forEach($scope.routes, function(route) {
                if(typeof $scope.status[route.uri+'_'+route.method] == 'undefined') {
                    $scope.status[route.uri+'_'+route.method] = {};
                }
                $scope.status[route.uri+'_'+route.method].open = true;
            });
        }

        $scope.foldAll = function() {            
            angular.forEach($scope.routes, function(route) {
                if(typeof $scope.status[route.uri+'_'+route.method] == 'undefined') {
                    $scope.status[route.uri+'_'+route.method] = {};
                }
                $scope.status[route.uri+'_'+route.method].open = false;
            });
        }
             
        
    }
]);