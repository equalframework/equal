<!DOCTYPE html>
<html lang="en" id="top" ng-app="project" ng-controller="rootController as rootCtrl">
  <head>
    <meta charset="utf-8">
    <title>model builder</title>
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
        
    <script type="text/javascript" src="packages/qinoa/apps/model/builder.js"></script>
    
    <style>
   
    </style>
  </head>
  
  <script>
	var global_config = {
		package: '<?php echo $_GET['package']; ?>'
	};
  </script>
  <body>

		<main id="body" role="main">
            <!-- This is a dedicated element where modal will anchor -->
            <div class="modal-wrapper"></div>
                       
            <div class="container" style="margin-top: 30px;">
			
				<div class="row">
					<button class="btn btn-xs btn-success" ng-click="new_class()" alt="add new class"><i class="fa fa-plus" arial-hidden="true"></i></button>
					<button class="btn btn-xs btn-danger" ng-click="" alt="delete class"><i class="fa fa-trash" arial-hidden="true"></i></button>													
					<button class="btn btn-primary pull-right" ng-click="save()" alt="save"><i class="fa fa-save" arial-hidden="true"></i> Save</button>
				</div>

				<div class="row">
					<div class="col-sm-2 well well-lg" style="height: 443px; overflow-y: scroll;">
						<h3 style="margin-top: 0;">Classes</h3>
						<div ng-repeat="class_def in classes track by $index">
							<a href ng-click="select_class($index)">{{class_def.name}}</a>
						</div>
					</div>
					<div class="col-sm-10" style="padding-left: 40px;">

						<div class="row">				
							<h3 style="margin-top: 0;"><input ng-model="classes[params.selected_class].name"></h3>
						</div> 

						
						<div class="row">				
							<div class="col-sm-2 well well-lg" style="height: 400px; overflow-y: scroll;">	
								<h3 style="margin-top: 0">Fields</h3>
								<div ng-repeat="field_def in classes[params.selected_class].fields track by $index">
									<a href ng-click="select_field($index)">{{field_def.name}}</a>
								</div>
							</div>
							<div class="col-sm-10">
								<button class="btn btn-xs btn-success" ng-click="new_field()" alt="add new field"><i class="fa fa-plus" arial-hidden="true"></i></button>
								<button class="btn btn-xs btn-danger" ng-click="" alt="delete field"><i class="fa fa-trash" arial-hidden="true"></i></button>								

							<form class="form-horizontal" ng-show="params.selected_class >= 0 && params.selected_field >= 0">
								<fieldset>

								<!-- Form Name -->
								<legend>Field attributes</legend>


								<div class="form-group">
								  <label class="col-md-4 control-label" >Name</label>  
								  <div class="col-md-4">
									<input type="text" placeholder="name of the field" class="form-control input-md" ng-model="classes[params.selected_class].fields[params.selected_field].name">
								  </div>
								</div>


								<div class="form-group">
								  <label class="col-md-4 control-label" >Type</label>
								  <div class="col-md-4">
									<select class="form-control" ng-model="classes[params.selected_class].fields[params.selected_field].type" ng-change="select_target_type(idx)" ng-options="type as type for type in params.types"></select>
								  </div>
								</div>
								
								
								<div class="form-group" ng-repeat="(attr_name, attr_def) in classes[params.selected_class].fields[params.selected_field].attributes">
									<label class="col-md-4 control-label" >{{attr_name}}</label>
									<div class="col-md-4" ng-switch="attr_def.type">
									
										<div ng-switch-when="boolean">
											<input type="checkbox" ng-model="classes[params.selected_class].fields[params.selected_field].attributes[attr_name].value" />
										</div>

										<div ng-switch-when="string">
											<input type="text" class="form-control input-md" ng-model="classes[params.selected_class].fields[params.selected_field].attributes[attr_name].value" />
										</div>

										<div ng-switch-when="selection">
											<select class="form-control" ng-model="classes[params.selected_class].fields[params.selected_field].attributes[attr_name].value" ng-options="opt as opt for opt in attr_def.selection"></select>
										</div>

										<div ng-switch-when="select_class">
											<select class="form-control" ng-model="classes[params.selected_class].fields[params.selected_field].attributes[attr_name].value" ng-change="select_target_class(idx, attr_name)" ng-options="class_def.name as class_def.name for (class_idx, class_def) in classes"></select>
										</div>

										<div ng-switch-when="select_field">
											<select class="form-control" ng-model="classes[params.selected_class].fields[params.selected_field].attributes[attr_name].value" ng-options="field_def.name as field_def.name for (field_idx, field_def) in getFields(classes[params.selected_class].fields[params.selected_field].attributes[attr_def.origin].value)"></select>	
										</div>
									
									
									</div>
								</div>
								

								</fieldset>
							</form>



							</div>
						  
						</div> 

					
					</div>				
				</div>

				
            </div>
        </main>

  </body>
</html>