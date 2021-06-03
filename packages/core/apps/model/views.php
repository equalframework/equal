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
    <link rel="stylesheet" href="workbench/assets/css/bootstrap.css" media="screen">
    <link rel="stylesheet" href="workbench/assets/css/font-awesome.css" media="screen">
    <link rel="stylesheet" href="workbench/assets/css/select.min.css" media="screen">	
    <script src="https://code.jquery.com/jquery-3.3.1.min.js" crossorigin="anonymous"></script>

    <script type="text/javascript" src="https://code.angularjs.org/1.6.9/angular.min.js"></script>    
    <script type="text/javascript" src="https://code.angularjs.org/1.6.9/angular-cookies.min.js"></script>
    <script type="text/javascript" src="https://code.angularjs.org/1.6.9/angular-route.min.js"></script>
    <script type="text/javascript" src="workbench/assets/js/ui-bootstrap-tpls-2.2.0.min.js"></script>
    <script type="text/javascript" src="workbench/assets/js/select-tpls.min.js"></script>
        
    <script type="text/javascript" src="workbench/views.js"></script>
    
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
					<button class="btn btn-primary pull-right" ng-click="save()" alt="save"><i class="fa fa-save" arial-hidden="true"></i> Save</button>
				</div>

				<div class="row" style="margin-top: 20px;">
					<div class="col-sm-2 well well-lg" style="height: 445px; overflow-y: scroll;">
						<h3 style="margin-top: 0;">Classes</h3>
						<div ng-repeat="class_def in classes track by $index">
							<a href ng-click="select_class($index)">{{class_def.name}}</a>
						</div>
					</div>
					<div class="col-sm-10" style="padding-left: 40px;">

						<div class="row">	
							<div class="col-sm-3">
								<h4 style="padding: 0; margin: 0;">
									View type
								</h4>							
							</div>
							<div class="col-sm-9">
								<select class="form-control" ng-model="params.selected_view">
									<option value="create">Create</option>
									<option value="edit">Edit</option>									
									<option value="list">List</option>									
									<option value="show">Show</option>									
								</select>
							
							</div>
						</div>

						<div class="row" style="margin-top: 10px; position: relative;">
							<div class="col-sm-3 pull-left">
								<h4 style="padding: 0; margin: 0;">
									Available fields
								</h4>
							</div>
							<div class="col-sm-6 pull-left">
								<select class="form-control" 
								ng-model="params.selected_field">
								<option ng-repeat="field_def in classes[params.selected_class].fields track by $index" value="{{field_def.name}}">{{field_def.name}}</option>
								</select>
							</div>
							<div class="col-sm-3 pull-left">
								<button class="btn btn-xs btn-success" ng-click="add_field()" alt="add new field"><i class="fa fa-plus" arial-hidden="true"></i></button>
								<button class="btn btn-xs btn-danger" ng-click="remove_field()" alt="delete field"><i class="fa fa-minus" arial-hidden="true"></i></button>								
							</div>
						</div> 

						
						<div class="row" style="margin-top: 10px;">				
							<div class="col-sm-3">	
								<div class="well well-lg" style="height: 360px; overflow-y: scroll;">	
									<h3 style="margin-top: 0">Fields</h3>
									<div ng-repeat="(field_name, field_def) in views[params.selected_view][params.selected_class]">
										<a href>{{field_name}}</a>
									</div>
								</div>
							</div>
							
							<div class="col-sm-9">
						
							<form class="form-horizontal" ng-show="params.selected_class >= 0 && params.selected_field >= 0">
								<fieldset>

								<!-- Form Name -->
								<legend>Field attributes</legend>


								<div class="form-group">
								  <label class="col-md-4 control-label" >Label</label>  
								  <div class="col-md-4">
									<input type="text" placeholder="label for the field" class="form-control input-md" ng-model="classes[params.selected_class].fields[params.selected_field].label">
								  </div>
								</div>


								<div class="form-group">
								  <label class="col-md-4 control-label" >Type</label>
								  <div class="col-md-4">
									<select readonly disabled class="form-control" ng-model="classes[params.selected_class].fields[params.selected_field].type" ng-options="type as type for type in params.types"></select>
								  </div>
								</div>
								
								<div class="form-group">
								  <label class="col-md-4 control-label" >Widget</label>
								  <div class="col-md-4">
									<select class="form-control" ng-model="classes[params.selected_class].fields[params.selected_field].widget" ng-change="select_target_type(idx)" ng-options="type as type for type in params.types"></select>
								  </div>
								</div>

								<div class="form-group">
								  <label class="col-md-4 control-label" >Readonly</label>
								  <div class="col-md-4">
									<input type="checkbox" ng-model="classes[params.selected_class].fields[params.selected_field].attributes[attr_name].value" />
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