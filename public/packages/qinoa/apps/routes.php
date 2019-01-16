<!DOCTYPE html>
<html lang="en" id="top" ng-app="project" ng-controller="rootController as rootCtrl">
  <head>
    <meta charset="utf-8">
    <title>Yb dashboard</title>
    <!-- define fragment to be used as hashbang (@see https://www.contentside.com/angularjs-seo/) -->        
    <meta name="fragment" content="!">
    <base href="/">
        
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <link rel="stylesheet" href="packages/qinoa/assets/css/bootstrap.css" media="screen">
    <link rel="stylesheet" href="packages/qinoa/assets/css/font-awesome.css" media="screen">
    <script src="https://code.jquery.com/jquery-3.3.1.min.js" crossorigin="anonymous"></script>

    <script type="text/javascript" src="https://code.angularjs.org/1.6.9/angular.min.js"></script>    
    <script type="text/javascript" src="https://code.angularjs.org/1.6.9/angular-cookies.min.js"></script>
    <script type="text/javascript" src="https://code.angularjs.org/1.6.9/angular-route.min.js"></script>
    <script type="text/javascript" src="packages/qinoa/assets/js/ui-bootstrap-tpls-2.2.0.min.js"></script>
    <script type="text/javascript" src="packages/qinoa/assets/js/select-tpls.min.js"></script>
        
    <script type="text/javascript" src="packages/qinoa/apps/routes.js"></script>
    
    <style>

      .route {
        display: block;
        position: relative;
        margin-bottom: 20px;
        font-weight: normal;
        text-align: center;
        vertical-align: middle;
        background-image: none;
        border: 1px solid transparent;
        white-space: nowrap;
        padding: 0 12px 10px 12px;
        font-size: 14px;
        line-height: 1.42857143;
        border-radius: 4px;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
    }      

    .route.route-GET {
        border-color: #f3af3b;
    }

    .route.route-POST {
        border-color: #25978b;
    }

    .route.route-PUT {
        border-color: #325ea0;
    }

    .route.route-DELETE {
        border-color: #923e00;
    }
    .route > span.route-method {
        position: absolute;
        right: 10px;
        top: 10px;
        border: solid;
        padding-left: 5px;
        padding-right: 5px;
        font-weight: bold;
        border-radius: 0.25em;
        color: white;
    }

    .route > span.route-method-GET {
        border-color: #f3af3b;
        background-color: #f3af3b;    
    }

    .route > span.route-method-POST {
        border-color: #25978b;
        background-color: #25978b;    
    }

    .route > span.route-method-PUT {
        border-color: #325ea0;
        background-color: #325ea0;    
    }

    .route > span.route-method-DELETE {
        border-color: #923e00;
        background-color: #923e00;    
    }

    .route > h3.route-uri {
        display: block;
        margin: 0;
        padding: 0;
        margin-top: 10px;
        margin-bottom: 10px;
        text-align: left;
        font-size: 20px;
    }

    .route > div.route-description {
        text-align: left;
    }

    .route > uib-accordion div[uib-accordion-group] {
        text-align: left;
        font-weight: bold;
        margin-bottom: 0;
    }
    .route > uib-accordion > .panel-group {
        margin-bottom: 0;
    }
    
    .route .route-params {
        font-weight: normal;
    }

    </style>
  </head>
  

  <body>

<div class="container">
            <div class="bs-component" style="margin-bottom: 40px;">
                <div class="form-group">
                    <label>Selected API:</label>
                    <select class="form-control" ng-model="apis.selected" ng-change="selectAPI()" ng-options="api as api for api in apis.list"></select>
                </div>                
            </div>
            
            <div class="bs-component" style="margin-bottom: 40px;">

                <div ng-repeat="route in routes" class="route route-{{route.method}}">
                    <span class="route-method route-method-{{route.method}}">{{route.method}}</span>
                    <h3 class="route-uri">{{route.uri}}</h3>
                    <div class="route-description">
                    {{route.description}}
                    </div>
                    <uib-accordion>                    
                    <div uib-accordion-group class="panel-default" is-open="status[route.uri+'_'+route.method].open">
                        <uib-accordion-heading>
                            Params <i class="pull-right fa" ng-class="{'fa-chevron-down': status.open, 'fa-chevron-right': !status.open}"></i>
                        </uib-accordion-heading>
                        <div class="route-params">

                        <div ng-repeat="(param, attrs) in route.params">
                            <div style="display: inline-block; width: 100px; overflow: hidden;"><b>{{param}}</b></div><div style="display: inline-block; width: 70px;">({{attrs.type}})</div> {{attrs.description}}
                        </div>
                        </div>
                    </div>
                    </uib-accordion>                    
                </div>                

            </div>    
</div>
  </body>
</html>