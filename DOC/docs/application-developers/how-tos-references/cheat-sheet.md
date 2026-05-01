
# Cheat-Sheet

This section presents common questions along with some relevant examples.


## eQual Apps

### Dashboard

[http://equal.local/apps](http://equal.local/apps)
### Console

[http://equal.local/console.php](http://equal.local/console.php)

### Workbench

[http://equal.local/workbench](http://equal.local/workbench)


## CLI Commands

Should be located at the root of eQual (folder containing the file `run.php`).


#### Grant DB Rights

Available rights:

- create
- read
- update
- delete
- manage


You can grant one right for one entity at a time.

```bash
./equal.run --do=group_grant --group=default --right=update --entity="core\User"
```


#### Test Package Consistency

This controller runs some consistency checks and works with any package.

| **PATH**        | `core\actions\test\package-consistency.php`                                                                                   |
| --------------- | ----------------------------------------------------------------------------------------------------------------------------- |
| **URL**         | `?do=test_package-consistency&package=core`                                                                                   |
| **CLI**         | `$ ./equal.run --do=test_package-consistency --package=core --level=warn`                                                     |
| **DESCRIPTION** | Consistency checks between DB and class, as well as syntax validation for classes (PHP), views, and translation files (JSON). |


> The level property has 3 options:
>
> - **'error'**.
>   Example: `missing property 'entity' in file:  "packages\/lodging\/views\/sale\booking\InvoiceLine.form.default.json"`.
> - **'warn'**.
>   Example: `WARN - I18 - Unknown field 'object_class' referenced in file "packages\/core\/i18n\/en\/alert\MessageModel.json"`.
> - **\*** (error & warn).


#### Initiate eQual Core in DB

| **PATH**        | `core\actions\init\package.php`                                                                 |
| --------------- | ----------------------------------------------------------------------------------------------- |
| **URL**         | `?do=init_package&package=core`                                                                 |
| **CLI**         | `$ ./equal.run --do=init_package --package=core`                                                |
| **DESCRIPTION** | Initialize database for the given package. If no package is given, initialize the core package. |


> (This step is mandatory for every new installation.)


#### Initiate Your Package in DB

| **PATH**        | `core\actions\init\package.php`                                                                 |
| --------------- | ----------------------------------------------------------------------------------------------- |
| **URL**         | `?do=init_package&package=myPackage`                                                            |
| **CLI**         | `$ ./equal.run --do=init_package --package=myPackage`                                           |
| **DESCRIPTION** | Initialize database for the given package. If no package is given, initialize the core package. |


#### Initiate Your Package with Initial Data in DB

| **PATH**        | `core\actions\init\package.php`                                                                 |
| --------------- | ----------------------------------------------------------------------------------------------- |
| **URL**         | `?do=init_package&package=myPackage&import=true`                                                |
| **CLI**         | `$ ./equal.run --do=init_package --package=myPackage --import=true`                             |
| **DESCRIPTION** | Initialize database for the given package. If no package is given, initialize the core package. |


#### Seed Data for Your Package

| **PATH**        | `core\actions\init\seed.php`                                                           |
| --------------- | -------------------------------------------------------------------------------------- |
| **URL**         | `?do=init_seed&package=myPackage`                                                      |
| **CLI**         | `$ ./equal.run --do=init_package --package=myPackage`                                  |
| **DESCRIPTION** | Seed objects for the package using JSON configuration files in "{package}/init/seed/". |


#### Run Package Test Unit

| **PATH**        | `core\actions\test\package.php`                                                                                      |
| --------------- | -------------------------------------------------------------------------------------------------------------------- |
| **URL**         | `?do=test_package&package=core`                                                                                      |
| **CLI**         | `$ ./equal.run --do=test_package --package=core`                                                                     |
| **DESCRIPTION** | The controller checks the presence of test units for a given package and runs them, if any. (Page: [Testing](../../community/contribution-guide/tests-coverage.md)). |


## Invoking Controllers

### Data Provider


| **PATH** | `/packages/mypackage/data/my-controller.php`                     |
| -------- | ---------------------------------------------------------------- |
| **URL**  | `?get=mypackage_my-controller`                                   |
| **CLI**  | `$ ./equal.run --get=model_collect --entity="mypackage\MyClass"` |
| **PHP**  | ```run('get', 'mypackage_MyClass')```                            |


*Collect is the name of the controller, and model is the directory to which it belongs.*


### Action Handler


| **PATH** | `/packages/mypackage/actions/subdir/my-action.php`                                                     |
| -------- | ------------------------------------------------------------------------------------------------------ |
| **URL**  | `?do=mypackage_subdir_my-action`                                                                       |
| **CLI**  | `$ ./equal.run --do=model_update --entity=mypackage\MyObject --fields=[ids]=1 --fields=[name]=example` |
| **PHP**  | ```run('do', 'mypackage_myobject_action', [/* parameters */])```                                       |



## Operations: Syntax


| Level                   | Example                                                         | Description                                    |
| ----------------------- | --------------------------------------------------------------- | ---------------------------------------------- |
| `group_by` (simple)     | `"group_by": ["date"]`                                          | Basic grouping.                                |
| `group_by` (object)     | `{ "field": "product_id", "operation": ["SUM", "object.qty"] }` | Group with aggregation.                        |
| `group_by[].operations` | `{ "called_amount": { "operation": "SUM" } }`                   | Field-level operations in a group.             |
| `operations` (global)   | `"operations": { "total": { ... } }`                            | Aggregated values in named rows (like totals). |




## How tos

### How to Create a New Object?

```php
<?php
use core\User;

User::create(['firstname' => 'Bart']);
// Which is equivalent to
User::create()->update(['firstname' => 'Bart']);
```


> Note: When creating an object, by default, the `state` field is assigned to 'instance', unless another value is given
> as a parameter (e.g., state=draft).


### How to Check if a Given Object Exists?

```php
<?php
use core\User;

if(count(User::search(['firstname', '=', 'Bart'])->ids()) {...}
// Or, if you know the object id
if(User::id(123)->first()) {...}
```


### How to Browse All Objects of a Given Class?

```php
<?php
// Note: Ensure the specified class does actually exist.
$res = browse($object_class, search($object_class));
```


### How to Add a Clause to Every Condition?

```php
<?php
// Example: Add the (deleted = 1) clause to every condition.
for($i = 0, $j = count($domain); $i < $j; ++$i) {
	$domain[$i] = array_merge($domain[$i], array(array('deleted', '=', '1')));
}
```


### How to Obtain Output (JSON/HTML) from Another Script?

```php
<?php
// There are 2 possibilities:

// Either use an HTTP request
load_class('utils/HttpRequest');
$request = new HttpRequest(FClib::get_url(true, false).
'?get=core_objects_list&object_class=School%5CTeacher&rp=20&page=1&sortname
=id&sortorder=asc&domain%5B0%5D%5B0%5D%5B%5D=courses_ids&domain%5B0%5D%5B
0%5D%5B%5D=contains&domain%5B0%5D%5B0%5D%5B2%5D%5B%5D=1&fields%5B%5D=id&
fields%5B%5D=firstname&fields%5B%5D=lastname');
$json_data = $request->get();

// Or use PHP output buffering (to prevent scope collision, remember to 
// embed such code into a function).
function get_include_contents($filename) {
	ob_start();	
	include($filename); // assuming  parameters required by the script 
	// being called are present in the current URL.
	return ob_get_clean();
}
$result = get_include_contents('packages/core/data/objects/list.php');
```


### How to Sort the Result of the Browse Method (Without Calling the Search Method)?

```php
<?php
// $order is an array containing field names on which we want the result 
// set sorted.
// $result is an array returned by a call to the browse method.

foreach($order as $ofield) {
	uasort($result, function ($a, $b) use($ofield){
		if ($a[$ofield] == $b[$ofield]) return 0;
		return ($a[$ofield] < $b[$ofield]) ? -1 : 1;
	});
}
```


### How to Request Fields from All Sub-Objects at Once?

```php
<?php
$pages_values = $orm->read('icway\Page', $pages_ids, array('url_resolver_id'), 
$lang);            
$url_ids = array_map(function($a){return $a['url_resolver_id'];}, $pages_values);
$url_values = $orm->read('core\UrlResolver', $url_ids, ['human_readable_url']);
```

---

