

This section will walk you through creating your first eQual application with the Workbench. The app can be found at [http://equal.local/workbench/](http://equal.local/workbench/). (This may change if you modified the docker-compose.yml.)


!!! note "The Workbench"
  The Workbench is a tool intended to help you build your apps visually, without worrying about the code (as much as possible).

---


### Creating Your First Component


The first step to your application is packages. Note that you will have to respect the naming convention of each component to be able to create them.


Let's start by creating a package called `tutorial` and the Model `tutorial\Post` using the component creator:


<img src="/_assets/img/workbench_creator_package.png" style="width: 49%;">
<img src="/_assets/img/workbench_creator_model.png" style="width: 49%;">

---


---

### Using the Base Menu


By using the [base menu](../workbench-overview.md/#creating-equal-components), you can easily find the components you need. Let's look for our newly created Model:


<img src="/_assets/img/workbench_find_example.png" style="width: 30%;">

---


---

### Editing Your Components


Let's add properties to our `Post` Model:


<img src="/_assets/img/workbench_field_title.png" style="width: 49%;">
<img src="/_assets/img/workbench_field_content.png" style="width: 49%;">
<img src="/_assets/img/workbench_field_published.png" style="width: 49%;">
<img src="/_assets/img/workbench_field_author_name.png" style="width: 49%;">


Once you have these fields, you can save the model:


<center><img src="/_assets/img/workbench_field_list.png" style="height: 400px;"></center>


You will need to add the function for the `computed` in the PHP file (you can find it at `/packages/tutorial/classes/Post.class.php`) to make it work. Workbench does not allow logic editing for now.

```php
<?php

public static function calcAuthorName($self){
    $result = [];
    $posts = $self->read(['id', 'creator' => ['fullname']]);
    foreach($posts as $id => $post) {
        $result[$id] = $post['creator']['fullname'];
    };
    return $result;
}
```

---

### Translations


Let's translate our Model `Post` into English.


<img src="/_assets/img/workbench_translation_tuto.png">

---

### Creating Workflows


Let's create a workflow for Post. Set the name and icons as follows.


<center><img src="/_assets/img/workflow_tuto.png"></center>

---

### Creating Views


Let's create two views for Post using the view submenu: `form.default` and `list.default` (the basic view of a model). By clicking on the [component creator](#creating-equal-components), you can notice that the context is auto-filled into the fields.


<center><img src="/_assets/img/workbench_view_creator.png"></center>


You should end up with:


<center><img src="/_assets/img/workbench_view_tuto.png"></center>

---

### Editing Views


Edit the views of Post like this:


##### `form.default`:


<center><img src="/_assets/img/workbench_view_editor_tuto_form.png"></center>


Items have a width of 25%.


---

#### `list.default`:


<center><img src="/_assets/img/workbench_view_editor_tuto_list.png"></center>

---


---

### Creating Menus


Start by filtering the elements by menus. Then create a menu of type `left` and of name `app` in the package `tutorial`.


<center><img src="/_assets/img/workbench_menu_create.png"></center>


Then, open the menu editor and edit the menu like so:


<center><img src="/_assets/img/workbench_menu_tuto.png"></center>

---


---

### Adding Data


Navigate to the package `tutorial` and create some posts for our blog by accessing the initial data button:


<center><img src="/_assets/img/workbench_init_tuto.png"></center>


---

### Initialize a Package


First, let's create the application for our package `tutorial`. Edit the `manifest.json` of the package as follows:

```json
{
    "name": "tutorial",
    "description": "workbench tutorial package",
    "version": "1.0",
    "author": "YesBabylon",
    "license": "LGPL-3",
    "depends_on": [ "core" ],
    "apps": [
        {
          "id": "blog",
          "name": "Blog",
          "extends": "app",
          "description": "blog",
          "icon": "ad_units",
          "color": "#3498DB",
          "access": {
            "groups": [
              "users",
              "admins"
            ]
          },
          "params": {
            "menus": {
              "left": "app.left"
            }
          }
        }
      ],
    "tags": [ ]
}
```


Then, let's use the initialize button of the [package side menu](../workbench-overview.md/#package-side-menu).


<center><img src="/_assets/img/workbench_package_init.png"></center>


1. These checkboxes allow you to ask eQual to initialize the package's dependencies (and choose if you want to import their initial data) before initializing it.
2. This checkbox allows you to choose if you want to import the initial data of the package.


You can initialize the package `tutorial` as follows.


If all goes well, you will see this message in your [package side menu](../workbench-overview.md#package-side-menu)):


<center><img src="/_assets/img/workbench_package_initialized.png"></center>


Now, if you return to the /apps of your instance, you should see the blog app appear:


<center><img src="/_assets/img/workbench_apps_icons.png"></center>


Congratulations! You have created your first app using eQual Workbench.

---
