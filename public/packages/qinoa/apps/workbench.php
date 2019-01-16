<!DOCTYPE html>
<html lang="en" id="top" ng-app="project" ng-controller="rootController as rootCtrl">
  <head>
    <meta charset="utf-8">
    <title>eQual workbench</title>
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
        
    <script type="text/javascript" src="packages/qinoa/apps/workbench.js"></script>
    
    <style>
    #package-selector {
        display: inline-block;
        clear: both;
        width: 120px;
        padding: 0;
        margin: 0;
        line-height: 20px;
        vertical-align: top;
        margin-top: 8px;
        margin-left: 10px;
    }

    #main {
        position: fixed;
        left: 0;    
        top: 57px;
        height: calc(100% - 57px);
        width: 100%;        
    }
    iframe {
        position: absolute;
        left: 0;
        top: 0;
        display: block;
        width: 100%;
        height: 100%;
        border: 0;
    }    
    </style>
  </head>
  
  <script>
  
    $(document).ready(function() {    
		$.getJSON('/index.php?get=qinoa_config_packages', function (json_data) {
        
            $.each(json_data, function(i,item){
                $('#packages').append($('<option/>').val(item).html(item));
            });

		});
    });
    
    function showApp(app) {
        var model = $('#packages').val();
        if(model != '[select a package]') {
            $('#content').attr('src', '/index.php?show='+app+'&package='+model);
        }
    }
  </script>
  <body>


    <div class="navbar navbar-default" style="margin: 0; padding-top: 10px; padding-bottom: 10px;">
    
      <div class="container">
        <div class="navbar-header">
          

          <button class="navbar-toggle" type="button" data-toggle="collapse" data-target="#navbar-main">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>

        </div>
        <div class="navbar-collapse collapse" id="navbar-main">
          <div class="nav navbar-nav" style="width: 100%;">
            <div class="col-lg-2" style="min-width: 200px;">
                <div style="display: inline-block; width: 30px; height: 30px; background: url(/packages/qinoa/assets/img/yb_logo_small.png) no-repeat; background-size: contain;"></div>
                <div id="package-selector" ><select id="packages"><option>[select a package]</option></select></div>
            </div>
            <div class="col-lg-8" style="display: inline-block;">          

                <div class="btn-group" uib-dropdown is-open="status.model.isopen">
                    <button id="single-button" type="button" class="btn btn-primary" style="width: 130px;" uib-dropdown-toggle>
                        <span class="fa fa-cubes"></span> Model <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu" uib-dropdown-menu role="menu" aria-labelledby="single-button">
                        <li role="menuitem"><a href="#" onclick="showApp('qinoa_model_editor')">Edit model</a></li>
                        <li role="menuitem"><a href="#" onclick="showApp('qinoa_model_uml')">View UML</a></li>
                    </ul>
                </div>            

                <a href="#" onclick="showApp('qinoa_model_controllers')" class="btn btn-success" style="width: 130px;"><span class="fa fa-code"></span> Controllers</a>

                <div class="btn-group" uib-dropdown is-open="status.routes.isopen">
                    <button id="single-button" type="button" class="btn btn-info" style="width: 130px;" uib-dropdown-toggle>
                        <span class="fa fa-globe"></span> Routes <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu" uib-dropdown-menu role="menu" aria-labelledby="single-button">
                        <li role="menuitem"><a href="#" onclick="showApp('qinoa_routes')">App routes</a></li>
                        <li role="menuitem"><a href="#" onclick="showApp('qinoa_model_uml')">Browse APIs</a></li>
                    </ul>
                </div>            

                
                <a href="#" onclick="showApp('qinoa_admin')" class="btn btn-warning" style="width: 130px;"><span class="fa fa-cogs"></span> API explorer</a>                    
                <a href="#" onclick="showApp('qinoa_admin')" class="btn btn-danger" style="width: 130px;"><span class="fa fa-th-list"></span> Data</a>

            </div>
          </div>
        </div>
      </div>
      
    </div>  
  
    <div id="main">
        <iframe id="content" src="/index.php?show=qinoa_start" data-responsive="1">-</iframe>
    </div>

  </body>
</html>