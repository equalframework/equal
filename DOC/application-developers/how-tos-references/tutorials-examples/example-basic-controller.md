# Controllers

The controllers are usually separated in three different directories: **"Data, Actions, Apps"**.

- In the folder Data, we will be **Fetching**  the data, **method GET**.

> Example :  `/core/data/model/search.php`.

What it does:

`announce()` will handle the values of our query:

    - `description` tells what the controller does.
    - `params` gives additional requirements and conditions.
    - `response` defines the format of the server response.
    - `providers` helps us to access useful services such as `context`, `orm`, `auth`.

> Example : **`/core/actions/model/create.php`**.
>
> The controller "Create" => "Create a new object using given fields values."

- In Apps, we will be **showing an APP/UI**, with different types of content.

> Example : **`/core/apps/model/controllers.php`**.
>
>  The controller "Controllers" => "UI for browsing controllers and their definition amongst packages."  



Let's go a bit more into details...

> You can also check out [rest-api](tutorial-rest-api.md) for more info.



##  Data Providers

We will use the above example, to make it easier.

Let's open  **`/core/data/model/search.php`**.

```php
<?php
list($params, $providers) = announce([
    'description'   => 'Returns a list of identifiers of a given entity, according 
    to given domain (filter), start offset, limit and order.',
    'response'      => [   // defines the format of the server response
        'content-type'      => 'application/json', 
        'charset'           => 'UTF-8',
        'accept-origin'     => '*',
        'cacheable'         => false
    ],    
    'params'        => [ // gives additional requirements and conditions
        'entity' =>  [
            'description'   => 'Full name (including namespace) of the class 
            to look into (e.g. \'core\\User\').',
            'type'          => 'string', 
            'required'      => true 
            // Means that the entity Condition/Requirement is necessary for 
            // the request to go through
        ],
        'domain' => [
            'description'   => 'Criteria that results have to match 
            (series of conjunctions)',
            'type'          => 'array',
            'default'       => []
        ],
        'order' => [
            'description'   => 'Column to use for sorting results.',
            'type'          => 'string',
            'default'       => 'id'
        ],
        'sort' => [
            'description'   => 'The direction  (i.e. \'asc\' or \'desc\').',
            'type'          => 'string',
            'default'       => 'asc'
        ],
        'start' => [
            'description'   => 'The row from which results have to start.',
            'type'          => 'integer',
            'default'       => 0
        ],
        'limit' => [
            'description'   => 'The maximum number of results.',
            'type'          => 'integer',
            'default'       => -1
        ]
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'providers'     => [ 'context' ] 
    // helps us to access useful services such as context, orm, auth
]);

list($context) = [ $providers['context'] ];

if(!class_exists($params['entity'])) {
    throw new Exception("unknown_entity", EQ_ERROR_UNKNOWN_OBJECT);
}

$collection = $params['entity']::search($params['domain'],
[ 'sort' => [ $params['order'] => $params['sort']] ]);

$total = count($collection->ids());

$result = $collection
          ->shift($params['start'])
          ->limit($params['limit'])
          ->ids();

$context->httpResponse()
        ->header('X-Total-Count', $total)
        ->body($result)
        ->send();
```

What it does :

`announce()` will handle the values of our query :

- `description` tells what the controller does

- `params` gives additional requirements and conditions

- `response` defines the format of the server response

- `providers` helps us to access useful services such as `context`, `orm`, `auth`

### About Providers

We use ``` list($context) = [$providers['context']] ``` to implement the services we want to use.

In the above example (*search controller*) the `$collection` variable is where we receive the data from our query :

- `$params['entity']::search([$params])`  searches for data associated with a specific **Entity or Class** and that matches the `params` given

  > Example: If we use the entity **User**, we will do a research amongst all the Users from our database.
  >
  > If we don't use any requirements/conditions, we will have a list with all the users.

Finally, `$context` is used to accomplish REST's purpose, displaying the data on our browser as JSON

```php
<?php
$context
    ->httpResponse()// get the HTTP response being built
    ->body($result)	// populate the body with resulting result
    ->send();		// output the response (i.e. some plain text
					// @see https://www.w3.org/Protocols/rfc2616)
```



### Using GET in your browser

We'll assume we already have an existing database containing a data sample of tasks, and have already done the following :

```bash
$> php run.php --do=test_package-consistency --package=todolist 
// The test_package_consistency controller checks if the package has the 
right values, if there are errors, ...

$> php run.php --do=init_package --package=todolist 
// The init_package controller should populate the database with the tables 
related to the specified package.
```

Open your browser, and in the localhost page you defined for eQual, add this (or simply click here: [http://equal.local/apps/](http://equal.local/apps/)):

```bash
?get=core_model_search 
```

> "core"  (the name of the package) is not necessary here.
>
> We simplified it, inside the **eq.lib.php** script, because we use this package quite a lot. 
>
> **Summary** : *?get=model_search* would also work.

The query result is: 

```JSON
"errors": {
        "MISSING_PARAM": "entity"
 }
```

> Remember that the entity is required.

We will add an entity to the search: 

|**PATH**|`core\data\model\search.php`|
| --------------- | ------------------------------------------------------------ |
|**URL**|`?get=model_search&entity=core\User`|
|**CLI**|`$ ./equal.run --get=model_search --entity=core\\User`|
|**DESCRIPTION**|Returns a list of identifiers of a given entity, according to given domain (filter), start offset, limit and order.|

Equal-framework does the work of reading ```?get=model_search```  as ```/core/classes/User.class.php ```.

It will display a JSON formatted answer showing an array with all the User identifiers.

As easy as that. You now have a REST response that you can use in any frontend project.



## Action Handlers

Let's open  **`/core/actions/model/create.php`**.

**Controller**: 

```php
<?php
list($params, $providers) = announce([
    'description'   => "Create a new object using given fields values.",
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'UTF-8',
        'accept-origin' => '*'
    ],
    'params'        => [
        'entity' =>  [
            'description'   => 'Full name (including namespace) of the class 
            to return (e.g. \'core\\User\').',
            'type'          => 'string', 
            'required'      => true
        ],
        'fields' =>  [
            'description'   => 'Associative array mapping fields to 
            their related values.',
            'type'          => 'array', 
            'default'       => []
        ],
        'lang' => [
            'description '  => 'Specific language for multilang field.',
            'type'          => 'string', 
            'default'       => DEFAULT_LANG
        ]
    ],
    'providers'     => ['context', 'orm', 'adapt']
]);

list($context, $orm, $adapter) = [$providers['context'], $providers['orm'], 
                               	  $providers['adapt']];

// fields and values have been received as a raw array : 
// adapt received values according to schema
$entity = $orm->getModel($params['entity']);
if(!$entity) {
    throw new Exception("unknown_entity", EQ_ERROR_INVALID_PARAM);
}

$schema = $entity->getSchema();
foreach($params['fields'] as $field => $value) {
    // drop empty and unknown fields
    if(is_null($value) || !isset($schema[$field])) {
        unset($params['fields'][$field]);
        continue;
    }
    $params['fields'][$field] = $adapter->adapt($value, $schema[$field]['type']);
}

// fields for which no value has been given are set to default value (set in Model)
$instance = $params['entity']::create($params['fields'], $params['lang'])
            ->adapt('txt')
            ->first();

$result = [
    'entity' => $params['entity'], 
    'id'     => $instance['id']
];

$context->httpResponse()
        ->status(201)
        ->body($result)
        ->send();
```

###### What it does :

- `params` defines the requirements we need, the field requirement allows us to give properties to the object we want to create.

- `$params['entity']` is then used with the `::create()` method to initiate a POST request and create a new object, following the `$params` given

- `adapt('txt')` turns the data into strings

- Finally, we use `$context` to send it and get a REST response

  

### Using DO in your browser

If we type in the browser :

```markdown
?do=model_create&entity=core\User&fields[login]=vygern8@gmail.com
&fields[password]=test3333
```

> The login and the password are **required** from the User class (`'core/classes/User.class.php'`)
>
> Equal-framework does the work of reading ```?do=model_create``` as ```/core/actions/model/create.php```

Congrats, you created a new User !



## Apps Controllers

Apps controllers are meant for server-side rendering (example: fully rendered HTML page for the client, the way Wordpress does).

Most apps built with eQual have little or no use of Apps controllers and rather call data providers through AJAX requests and then use the data for client-side rendering of components.

However, the philosophy of eQual is to allow developers to do things the way they want. For that reason the choice of the use of Apps controllers is left to the discretion of the developers.



### Using SHOW in your browser

If we type in the browser :

```markdown
?show=core_welcome&announce=1
```

**The Response** :

```
{
    "announcement": {
        "description": "Redirect to welcome folder.",
        "params": [],
        "response": {
            "location": "\/welcome\/"
        }
    }
}
```



The response has the form of an **APP/UI**, where we can browse controllers and their definition amongst packages.

## Using 'Setting' Examples

```php
// Get a setting value for user_id 1
$value = Settings::get_value('billing', 'defaults', 'currency', 'EUR', ['user_id' => 1]);

// Assert a default setting (global)
Settings::assert_value('billing', 'defaults', 'currency', 'EUR');

// Fetch and increment a sequence
$nextNumber = Settings::fetch_and_add('invoicing', 'sequence', 'invoice_number', ['organization_id' => 5]);
```

### Example: Meal Preferences Assignment Check

The controller `sale_booking_check-mealprefs-assignments` verifies consistency between the number of participants (`nb_pers`) and the quantity of assigned meal preferences (`meal_preferences_ids.qty`) for a `Booking`.

#### Implementation

1. **Booking is loaded** via its ID.
2. Each `booking_lines_group` of type `sojourn` or `event` is evaluated.
3. If a mismatch is detected, a `warning` alert is dispatched for the model `lodging.booking.mealprefs_assignment`:

```php
$dispatch->dispatch(
    'sale.booking.mealprefs_assignment',
    'sale\booking\Booking',
    $params['id'],
    'warning',
    'sale_booking_check-mealprefs-assignments',
    ['id' => $params['id']],
    [],
    null,
    $booking['center_office_id']
);
```
4. If everything matches, any existing alert is cancelled:

```
<?php
$dispatch->cancel('lodging.booking.mealprefs_assignment', 'sale\booking\Booking', $params['id']);
```
This ensures that alerts are raised on inconsistency and removed on resolution.

