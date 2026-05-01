# Packages

In order to ease development and collaboration, eQual organizes application logic into themed packages represented by
folders, located under the `/packages` directory.

These packages contain several components : models, views, controllers, and translation files. Models define data structure,
views describe user interface, controllers manage data flow, and translations support i18n features.

## Package structure

Each **package** is defined as follows :

```
package_name
├── classes
│   └── */*.class.php
├── actions
│   └── */*.php
├── apps
│   └── */*.php
├── data
│   └── */*.php
├── tests
│   └── */*.php
├── init
│   ├── data
│   |   └── *.json
|   ├── test
│   |   └── *.json
│   ├── demo
│   |   └── *.json
│   ├── bin
│   |   └── *.*
│   ├── routes
│   |   └── *.json
│   └── assets
│       ├── css
│       ├── fonts
│       ├── img
│       |   └── brand
│       └── js
├── i18n
│   └── *
│       └── *.json
├── views
│   └── *
│       └── *.json
├── manifest.json         
├── config.json
└── readme.md
```

| Folder    | Role                                            | Example                                                                    |
| :-------- | ----------------------------------------------- | -------------------------------------------------------------------------- |
| `classes` | model                                           | `core\User.class.php`, `core\Group.class.php`, `core\Permission.class.php` |
| `actions` | action handler (controller)                     | `core_manage`, `core_utils`                                                |
| `apps`    | applications related to the package             | `auth`, `apps`                                                             |
| `data`    | data provider                                   | `core_model_read`, `core_config_packages`                                  |
| `tests`   | test units                                      | `default.php`                                                              |
| `init`    | initialize the package (entities, data, routes) | `core_Group.json`                                                          |
| `views`   | templates                                       | `User.form.default.json`, `User.list.default.json`                         |
| `i18n`    | translations                                    | `User.json`                                                                |
| `assets`  | static content (resources)                      | js scripts, stylesheets, fonts, images                                     |

---

## Self description (root)

### `README.md`

A markdown file containing various relevant information about the package, its installation, configuration, and the features it offers.

### `manifest.json`

Packages manifests are an essential tool for managing the packages ecosystem and ensure compatibility between different components and versions of software libraries, facilitating seamless integration and streamlined development processes.

Each package has a manifest file (`manifest.json`) containing information about the package along with the apps it provides and the dependencies it requires. 

|    Property    | Role                                                                                                                                        | Example                                                                                                                                                                         |
| :------------: | ------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
|     `name`     | `(string)` Name of the package, also used as an identifier.<br />It should match the name of the folder of the package.                     | `"core"`                                                                                                                                                                        |
|   `version`    | `(string)` The version of the package, ensuring accurate versioning.                                                                       | `"2.0"`                                                                                                                                                                         |
| `description`  | `(string)` Short description of the package.                                                                                                | `"A short and yet explicit description of the package."`                                                                                                                        |
|   `license`    | `(string)` The license (and version) of the package.                                                                                        | `"LGPL-3"`                                                                                                                                                                      |
|   `authors`    | `(array <string>)` List of the name(s) of the author(s).                                                                                    | `["Cedric Francoys"]`                                                                                                                                                           |
|     `tags`     | `(array <string>)` List of descriptive tags or keywords associated with the package for easier categorization and searchability.            | `["Cedric Francoys"]`                                                                                                                                                           |
|  `depends_on`  | `(array <string>)` List of packages that need to be present and initialized in order for the package to work.                               | `[]`                                                                                                                                                                            |
|   `requires`   | `(object)` Map specifying external libraries or packages required by the package, along with version constraints using semantic versioning. | `"requires": {"swiftmailer/swiftmailer": "^6.2"}`                                                                                                                               |
| `requires_bin` | `(object)` Map specifying binaries required by the package that must be available on the host OS.                                           |                                                                                                                                                                                 |
|    `config`    | `(object)` Map associating config constants to the values specific to the package.                                                          | `"config": {"APP_LOGO_URL": "/assets/img/brand/logo.svg"}`                                                                                                                      |
|     `apps`     | `(array <string                                                                                                                             | object>)` Applications embedded in the package. <br />The list provides either Apps names (identifier) or descriptors for virtual apps to be used with the STD App (see below). | `["apps", "auth", "app", "settings"]` |

**Example:**

```json
{
    "name": "core",
    "description": "Foundation package holding common application logic and elementary entities.",
    "version": "2.0",
    "authors": ["Author Names"],
    "license": "License Type",
    "tags": [ "equal", "core" ],
    "depends_on": [],
    "requires": {
        "swiftmailer/swiftmailer": "^6.2",
        "phpoffice/phpspreadsheet": "^1.4",
        "dompdf/dompdf": "^0.8.3"
    },
    "apps": [ "apps", "auth", "app", "settings", "workbench", "welcome" ]
}
```

#### `requires` property

eQual is versionned so that there is a specific PHP version expected as "standard" environment (e.g. equal 1.0 = PHP 7.4, equal 2.0 = PHP 8.3).

In the `manifest.json` file of a package, dependencies are specified in the "requires" property.

When dependency constraints vary between different PHP versions, by convention, a `requires_` property is added in the manifest.

Example:

```json
"requires_php8.3": {

}
```

This notation is not taken into account during the package initialization nor Composer, but serves as a reminder for specific dependencies in cases where installation is performed in a non-standard environment.



#### `requires_bin` property


The `requires_bin` field in the package manifest allows to declare system-level dependencies (binaries) that must be available on the host system.

Since package managers and package names vary between operating systems, eQual uses an OS-aware mechanism to resolve and (optionally) install the required binaries. The actual package to install is determined **based on the identified operating system** at runtime.

You should always provide a `generic` entry as the default fallback, and override it with specific values when a package name differs on a particular system.

The table below lists all supported OS codes you can use in the `package` field of a binary requirement, for defining which identifier to use with the system’s package manager.


| Code      | Target OS                   | package manager               |
| --------- | --------------------------- | ----------------------------- |
| `generic` | **any**                     | OS default                    |
| `ubuntu`  | Ubuntu, Linux Mint          | apt                           |
| `debian`  | Debian                      | apt (if distinct from Ubuntu) |
| `fedora`  | Fedora, RHEL, CentOS Stream | dnf                           |
| `arch`    | Arch Linux, Manjaro         | pacman                        |
| `alpine`  | Alpine Linux                | apk                           |
| `mac`     | macOS                       | brew (Homebrew)               |
| `windows` | Windows 10+                 | choco (Chocolatey)            |

??? example "Example for `requires_bin` structure"
    ```
    "requires_bin": {
      "qpdf": {
        "version": ">=10.0",
        "check": "qpdf --version",
        "package": {
          "generic": "qpdf"
        }
      },
      "freetype": {
        "version": "^2.0",
        "check": "pkg-config --exists freetype2",
        "package": {
          "generic": "libfreetype6-dev",
          "mac": "freetype",
          "arch": "freetype2"
        }
      },
      "mssql_driver": {
        "version": "^17.0",
        "check": "odbcinst -q -d | grep -i 'ODBC Driver 17 for SQL Server'",
        "package": {
          "generic": "msodbcsql17",
          "mac": null // not supported
        }
      }
    }
    ```



#### `apps` property
The `apps`  property might either contain strings or descriptor objects.  The `app` application, defined in the core package,  can act as surrogate for creating custom Apps using all standard features without having to write additional Angular components. In such case, the descriptor is expected to be a full descriptor of the app, similar to the ones used in the manifest of Apps.

??? example "Example of custom App definition"
    ```json
    {
            "id": "inventory",
            "name": "Inventory",
            "extends": "app",
            "description": "Application inventory",
            "icon": "analytics",
            "color": "#6C3483",
            "text_color": "#050505",
            "access": {
                "groups": [
                    "users"
                ]
            },
            "params": {
                "menus": {
                    "left": "inventory.left",
                    "top": "inventory.top"
                }
            }
    }
    ```

---


## `apps` folder

### `manifest.json`

| Property              | Role                                                                            | Example                                             |
| --------------------- | ------------------------------------------------------------------------------- | --------------------------------------------------- |
| name                  | The name of the application.                                                    | `Settings`                                          |
| description           | A short description of the role and usage of the application.                   |                                                     |
| url                   | Relative URL of the application.                                                | `/settings`                                         |
| icon (optional)       | Identifier of the (material) icon for representing the application.             | `settings`                                          |
| color (optional)      | Color used for the button (background-color). Defaults to `#b0b0b0`.            | `#FF9741`                                           |
| text_color (optional) | Color used for the text of the button (color). Defaults to `#ffffff`.           | `#000000`                                           |
| extends (optional)    | String telling which application it extends from, if any.                       | `app`                                               |
| access/groups         | List of groups the access to the application is restricted to.                  | `['users']`                                         |
| params                | Object to be relayed to the targeted App targeted with `extends`(if supported). | `"params": { "menus" : { "left": "myApp.left" }  }` |
| params.menus          | The menu to be displayed for the App (possible menus are 'left' and 'right').   |                                                     |
| params.context        | The context to be used for the root URL ('/').                                  |                                                     |
| show_in_apps          | Flag telling if the application must be listed in the dashboard Apps list.      | default: ``false``                                  |
| tags (optional)       | List of labels for tagging of the application.                                  | `["equal", "core"]`                                 |


#### `params`
The `params` property allows to provide the surrogate application (`app`)  with parameters specific to the application being described. It can be used to define which menus must be loaded and which context must be displayed by default.

Example:
````
"params": {
    "menus": {
        "left": "settings.left",
        "top": "settings.top"
    },
    "context": {
        "entity": "core\\User"
        "view": "list.default",
        "order": "id",
        "sort": "asc"
    }
}
````

---

## `actions` folder

| Subfolder | Description                               |
| --------- | ----------------------------------------- |
| model     | Operations for ORM-related data retrieval |
| config    | Configuration and metadata retrieval      |
| utils     | Utility data providers                    |

## `data` folder

| Subfolder | Description                              |
| --------- | ---------------------------------------- |
| category  | Organizing test suites by feature/domain |

## `tests` folder

| Subfolder | Description                           |
| --------- | ------------------------------------- |
| init      | Initialization and data management    |
| model     | Operations to manipulate ORM entities |

## `init` folder

| Subfolder | Description                                                                  |
| --------- | ---------------------------------------------------------------------------- |
| data      | The name of the application.                                                 |
| test      | A short description of the role and usage of the application.                |
| demo      | Relative URL of the application.                                             |
| bin       |                                                                              |
| routes    |                                                                              |
| assets    | HTML dependencies (img, js, css) to be copied in the `public/assets` folder. |

