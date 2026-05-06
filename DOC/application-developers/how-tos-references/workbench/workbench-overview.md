# Workbench Overview

The **eQual Workbench** is a powerful tool designed to simplify the development process within the eQual framework. It provides a graphical interface for creating, editing, and managing components such as models, views, controllers, workflows, and more. By abstracting complex configurations into an intuitive UI, Workbench enables developers to focus on building applications efficiently.

## Getting Started

### Requirements

To use the Workbench, you need a working instance of eQual (branch `dev-2.0`). Refer to the [Installation Guide](/application-developpers/getting-started/installation/) for setup instructions.

### Installation

Install the Workbench by initializing the `core` package:
```bash
./equal.run --do=core_init_package --package=core
```

## Key Features

### **Component Creation and Management**

The Workbench allows you to create and manage various components of your application, including:

- **Models**: Define the structure and behavior of your data.
- **Views**: Design user interfaces for forms, lists, and dashboards.
- **Controllers**: Manage data flow and application logic.
- **Workflows**: Define state transitions and business processes.
- **Translations**: Define multilingual support for your application.

For detailed instructions, see [Creating eQual Components](#creating-equal-components).

### **Visual Editing**

The Workbench provides visual editors for:

- **Model Fields**: Add, edit, and configure fields with type-specific options.
- **View Layouts**: Drag-and-drop interface for designing forms, lists, and other views.
- **Workflows**: Graphical editor for defining state transitions.

Learn more in the [Model Field Editor](#model-field-editor), [Editing Views](#editing-views) and [Workflow editor](#workflow-editor) sections.

### **Translation Support**

Easily manage translations for models, views, and controllers to support multiple languages. The Workbench includes tools for translating field labels, error messages, and other UI elements.

See [Translation Editor](#translation-editor) for more details.

### **UML Viewer/Editor**

The UML Viewer/Editor automatically generates diagrams based on your models, allowing you to visualize relationships and dependencies. You can also edit and export these diagrams.

For more information, see [UML Viewer/Editor](#uml-viewereditor-1).

### **Data Initialization**

Create and manage initial or demo data for your application. This feature is useful for testing and seeding your database with predefined values.

Refer to [Creating Initial/Demo Data](#create-initialdemo-data) for details.

### **Menu and Route Management**

The Workbench provides tools for managing application menus and viewing routes. While route editing is not yet supported, you can view and organize routes through the interface.

See [Menu Editor](#edit-menus) and [Route Side Menu](#route-side-menu) for more information.

## Creating eQual Components

In workbench, you can find the component creator at the bottom left of every component selector of the app.

<img src="/_assets/img/workbench_creator_location.png">

The component creator allows you to create various type of components in eQual using templates to create a working basic component.

The component creator is context aware and will autofill the fields with your current context.
You will have to respect the naming convention of each components to be able to create them.

You can find precise components in eQual Workbench by using the base menu to search in all the component of the instance :

<img src="/_assets/img/workbench_find_explanation.png" style="height : 500px;">

To search for a component, you can use the **search bar (1)** to filter the component using this syntax :

`[component_type]:[package]>[keywords...]`

You can also use the **drop down menu (2)** to select the type (it will change it in the search bar and vice versa).

### Model Field Editor

After choosing a component, the right side of the screen will render the model's side menu :

<img src="/_assets/img/workbench_modelsp.png">

This side menu give a lot of information about the Model : 

1. Name and description of the Model.
2. The parent Model of the current Model (`Model` refers to `equal\orm\Model` the base Model of eQual).
3. The table name where object of this Model are or will be stored in the database.
4. The list of the fields of the Model (the tabs expand to give more information about the specific field).

Then, there are buttons to interact with the Model :

* **Fields (5)** leads to the field editor of the Model [see](#model-field-edition)

* **Views (6)** leads to the submenu of the views of the Model [see](#editing-views)

* **Translations (7)** leads to the translations editor of the Model [see](#translation-editor)

* **Workflows (8)** leads to the workflow editor of the Model [see](#workflow-editor)


The other buttons are currently work in progress.

#### Model Field Edition

<img src="/_assets/img/workbench_model_first_look.png">

1. **Back button** : allows to go back to the previous context (last page in the state you left it)
2. **Cancel/Redo** : allows to cancel and redo actions as `CTRL+Z` and `CTRL+Y` do.
3. **Save** : allows you sync your changes with the files of the instance of eQual
4. **More** : here are custom actions depending on the tool you are using. Here, you have the ability to show the JSON that will be turned into the PHP code during the save.

On the left of the screen you can see the **list of fields (5)** of the edited Model.

The **icon (6)** on the left of the name  gives two information about the field :

- The icon represent the type of the field :

| Icon                                              | type    | Icon                                            | type     | Icon                                              | type      |
| ------------------------------------------------- | ------- | ----------------------------------------------- | -------- | ------------------------------------------------- | --------- |
| <span class="material-icons">format_quote</span>  | string  | <span class="material-icons">today</span>       | date     | <span class="material-icons">looks_one</span>     | binary    |
| <span class="material-icons">123</span>           | integer | <span class="material-icons">event</span>       | datetime | <span class="material-icons">height</span>        | many2many |
| <span class="material-icons">data_array</span>    | array   | <span class="material-icons">access_time</span> | time     | <span class="material-icons">type_specimen</span> | alias     |
| <span class="material-icons">money</span>         | float   | <span class="material-icons">article</span>     | text     | <span class="material-icons">call_split</span>    | one2many  |
| <span class="material-icons">question_mark</span> | boolean | <span class="material-icons">call_merge</span>  | many2one | <span class="material-icons">functions</span>     | computed  |

- The background color represents the state of the field :
    - **Gray** is inherited from `equal\orm\Model`. It can't be edited or deleted because it is required by eQual to manage it in DB
    - **Yellow** is inherited. You can't delete an inherited field.
    - **Green** is Overridden. It's an inherited field that has been edited in this Model. It cannot be deleted but can be reset to its parent value
    - **Blue** is basic field, neither inherited, neither overridden.

As in the component menu, the **field creator (7)** is located at the bottom left of the screen. If you do not fill the name input, it will give an arbitrary name to the field.

### View Side Menu

The view side menu give information about the selected view and access to its edition.

<img src="/_assets/img/workbench_view_sidemenu.png">

* **Name and the description (1)** of the view
* **Information (2)** about the layout of the view
* Leads to the **view editor (3)**[see](#editing-views)
* Leads to the **translation (4)** of the Model/controller associated to the view [see](#translation-editor)

#### Editing Views

View are huge JSON file that are hard to read. Workbench give the user a simpler way to edit the view using an interface that mimic the result of the view in eQualUI

<img src="/_assets/img/workbench_view_editor_top.png">

In the editor, view are segmented in 5 categories **(2)**. The content of these part may vary depending on the type of view you are editing.

**1. Layout** : This is the part that vary the most. The [layout](../core-development-guide/views-ui/views/common-structures/common-structures.md#layout) is the part of the view that will display the object of a Model.

**2. Header** : Refers to the [header part](../core-development-guide/views-ui/views/common-structures/common-structures.md#header) of a view. You can edit header actions and selection actions in this tab.

**3. Actions** : You can edit the custom [actions](../core-development-guide/views-ui/views/common-structures/common-structures.md#actions) of the view here

**4. Routes** : You can edit the routes of the contextual menu of the view here

**5. Advanced** : You can edit all the other properties of the view here such as the domain, the collect controller, the operations, ...

#### Form/Search View Layout

When you are editing the layout of a form or a search view, the editor will create a render that looks like the render of eQual UI.

<img src="/_assets/img/workbench_view_editor_form.png" style="width : 49%">
<img src="/_assets/img/workbench_view_editor_comparison.png" style="width : 49%">

You can edit the layout visually using drag and drop. 

#### List View Layout

The view layout is represented as a list of column in the editor. You can edit the column and change their order by dragging and dropping them.

<img src="/_assets/img/workbench_view_editor_list.png">

### Controller Side Menu

The controller side menu give information about the selected controller using the announcer of the controller, allows the user to call the controller for testing purpose and give access to parameter and return value edition and controller [traduction](#translation-editor) edition.

<img src="/_assets/img/workbench_controller_sp1.png">

**1. Name and type** (GET = data controller or DO = action controller) of the controller.

**2. Description** of the controller.

**3. Response header** : Meta data about the response you will get by calling this controller.

<img src="/_assets/img/workbench_controller_sp2.png">

This list represents input parameters of the controller and can be filled to call the controller in workbench or generate cli command and http request to call the controller.

<img src="/_assets/img/workbench_controller_sp3.png">

**1.** Allows you to call the controller in workbench and see its response if all the required input parameter have a value.

**2.** Allows you to **copy the cli command** to call the controller with the parameter you entered in workbench.

**3.** Allows you to **copy the http request** to call the controller with the parameter you entered in workbench.

**4.** Give you all the **custom [routes](../core-development-guide/controllers-routing/routing.md)** enabled in the instance that uses this controller.

**5.** Leads to the input **parameters editor** [see](#controller-editor)

**6.** Leads to the **response editor** [see](#editing-controller-responses)

**7.** Leads to the **translation editor** of the controller [see](#translation-editor)

#### Controller Editor

The controller parameters editor works exactly like the [model field editor](#model-field-edition) without the inheritance handling and the advance panel.

<img src="/_assets/img/workbench_controller_params.png">

#### Editing Controller Responses

<img src="/_assets/img/workbench_controller_return.png">

In the return editor, you can modify the http response header **(1)**, and the eQual type of the content of the body **(2)**.

Http response header indicates in which format the data is sent, the descriptor is an indication about the type of the content.

For example an array can be sent in XML format : `content-type : application/XML` and `descriptor : array` But can also be sent in json format : `content-type : application/json` and `descriptor : array`.

You can also precise the quantity (`one` or `many`) of variable you send **(3)**

In addition of the "classic" types of eQual you can indicate an "entity" type that allow you to reference an eQual Model, or a data of type "any" : type "any" can be precise as a composite structure of eQual types if possible

<img src="/_assets/img/workbench_controller_return2.png">

### Menu Side Menu

<img src="/_assets/img/workbench_menu_sp.png">

The menu side menu is in work in progress and only gives access to the **[menu editor](#edit-menus) (1)** and **menu [traduction editor](#translate-a-component) (2)**

#### Edit Menus

The menu editor allows you to create/edit/delete menu items whilst having a preview of the final result.

<img src="/_assets/img/workbench_menu_editor.png">

**1. Name of the view** for display purpose only (unrelated to its id which is attached to its filename).

**2. Manage access** to the menu by group 

**3. Preview** and item selector (allow sorting by drag and drop on the same level)

**4. Edition of the properties** of the selected menu item

All the editable properties of the view in workbench are properties findable in the [original menu structure](../core-development-guide/views-ui/menus.md).

### Package Side Menu

<img src="/_assets/img/workbench_package_sp.png">

**1. Information about the package** : name, `package_consistency` reports and if the package has been initialized on the eQual instance.

**2. Details** about the `package_consistency` reports.

**3.** Button to open the **[package initializer dialog](#initialize-a-package)**.

**4.** Leads to **models [submenu](#submenus)**

**5.** Leads to **controllers [submenu](#submenus)**

**6.** Leads to **views [submenu](#submenus)**

**7.** Leads to **routes [submenu](#submenus)**

**8.** Leads to **init data editor**

**9.** Leads to **demo data editor**

Demo and initial data are the same structure but they are not saved at the same place to allow the developer to choose which he wants to import if he wants to import them.

#### Create `initial`/`demo` Data

<img src="/_assets/img/workbench_init_overview.png">

**1.** This is the list of the **init/demo files** for the given package, you can navigate through them using the side pane.

**2.** You can select the entity you want to modify its entries in a given file using the **tab selector**.

**3.** This button allows you to **interact with the Model properties**, mostly delete the model's entry or select which languages you want to create the data for the model (uses also [ISO 639-1](https://fr.wikipedia.org/wiki/Liste_des_codes_ISO_639-1)).

**4.** You can **visualize the data entry** as a list here (uses the list.default view of the model, make sure you created it trying to display the properties before creating data). By clicking on an entry, you can edit its values (fields that are not multilang are only editable in `en``) :
     <img src="/_assets/img/workbench_init_entry.png">
     
**5.** To **delete entry** you need to select the desired items then click on this button

**6.** **`id strict` and `toggle id` button**s allows you to tell to the editor if the entry is bound to a specific id or not. You may uses `bound id` to create relations between items or if a component uses the id in a domain.

**7.** The editor allows you to **import initial data** from the data of the eQual database. By clicking on this button, Workbench will fetch all the object of the Model and convert the one you select to initial/demo data :
    <img src="/_assets/img/workbench_init_import.png">

**8.** You can create initial **data entries** by hand using this button. this will open the edition menu automatically on the new item.

### Translation Editor

<img src="/_assets/img/workbench_traduction_top.png" style="width : 100%; height : auto;">

In the translation editor, you can select or create a lang to translate your **entity (1)**. 

The format for the lang is [ISO 639-1](https://fr.wikipedia.org/wiki/Liste_des_codes_ISO_639-1), either two lower case letter (representing a language) like `fr` or `en` or two lower case letter (representing a language) and two upper case letter (representing a country) separated by an underscore like `fr_BE` or `es_EC`.

Translating en entity means to translate its fields (or parameters name for controller), but also its views and error (due to constraints in models or exception in controllers)

#### Model translation

Translation applies to models. However, it also applies to controllers using params as their fields.

<img src="/_assets/img/workbench_translation_model.png">

You can translate the **name and description (1)** of the model and add plural variation to be used in the view render motor of eQualUI .

**Translating fields (3)**, will allow your apps to display the name differently in the views depending on the lang of the user that visualizes it. It also override the `label` argument of a view field. You can choose whether or not a field is translated or not via **check boxes (2)**.

#### View translation

There is not much to translate in views because the names of fields are already translated in [Model translation](#model-translation). Here, you will be able to translate groups, sections, row, column and label fields names.

<img src="/_assets/img/workbench_translation_view.png">

You can select which view to translate with the **tab selector (1)**, then you can **translate the view (2)**the same way you translate the model.

#### Error translation

Error translation works differently. You need to state for each field and error type the appropriate message.

<img src="/_assets/img/workbench_translation_error.png">

First you can choose if a field has error to translate via a **checkbox (1)**

Then, you can add error type to the **field (2)** and translate them.

### Workflow Editor

By using the button workflow in the [Model side menu](#model-field-editor), You can access the workflow editor :

<img src="/_assets/img/workflow_overview.png">

To create or edit a workflow, you can use the different **modes (1)** of the editor. 

When in view or edit state, you can move the states of the workflow to adjust the **visualization (2)** by drag and dropping the state.

If you want to edit properties of states or transition select the appropriate mode and click on the **edit button (4)** of the item you want to edit, then use the **contextual menu (3)** on the right  to alter the element.

When in create or edit transition mode, you can use the **anchors (5)** to move the start and the end of the transitions to adjust both the workflow and its visualization.

On save, the workflow will be converted to php associative array in the Model php file and the layout you created in core\Meta to be reused next time you want to visualize or edit the workflow.

### UML Viewer/Editor

Workbench has a UML Viewer/Editor that you can access by the side menu :

<img src="/_assets/img/workbench_access_uml.png">

The UML editor uses information contained in the models' structure to create automatically a diagram

<img src="/_assets/img/workbench_uml_overview.png">

The UML editor is similar to the [workflow editor](#creating-a-workflow) in is internal working. you can switch between 3 **modes (2)** to create your workflows.

You can load and save UML diagrams located in the folder `/uml` of the packages of your eQUal instance using header buttons **(1)**.

You can move by dragging the models in the **visual representation (3)** to adjust the graph

When **editing a model (4)** you can disable the inheritance links and relations link from this model. You can also hide fields to make the UML more readable.

This page handles printing. you can **render your UML as PDF (5)** using the button or by pressing `CTRL+P`. Make sure that the camera is in the top left corner of the diagram and edit the scale of the print to fit everything in the page.

<img src="/_assets/img/workbench_uml_print.png">

Adding a model is as simple as adding his name in the model list. The relations will add themselves.

<img src="/_assets/img/workbench_uml_add.png">

To delete a model from the representation, you can use the list of model, or select the model in the edition mode and press the delete button.


### Route Side Menu

Route edition is not currently supported by Workbench but you can view them in the main menu and they have a side menu :

<img src="/_assets/img/workbench_routes.png">

### Pipeline Editor

The Pipeline Editor, part of the Workbench App, allows developers to build pipelines using a graphical interface. This eliminates the need for writing boilerplate code manually, simplifies development, and enhances accuracy.

#### What is a Pipeline?

A **pipeline** is a sequence of connected operations used to process and transform data step by step. Each operation is represented by a **Controller** or **Router**.

#### Creating a Pipeline

1. Open the Workbench and navigate to the Pipeline Editor.
2. Drag and drop controllers and routers to create nodes.
3. Define input parameters for each controller.
4. Link the output of one node to the input of another.
5. Save and test your pipeline.

#### Example Pipeline

```php
"schema" => [
  "type" => "entity",
  "qty"  => "many",
  "entity" => "core\User"
]
```

This pipeline fetches multiple User entities and passes them to the next node.

---