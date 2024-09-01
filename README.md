eQual is a complete open-source low-code web-based framework, designed to efficiently create and manage **modern softwares** that can **adapt to any Application Logic**.

"Not everyone can be a fullstack developer, but with eQual, anyone can develop like one": Developing an application from end to end is extremely complex and requires a wide range of skills that few people possess on their own. eQual's mission is to **enable any developer to create production-ready applications** by helping them compensate for the aspects they are less proficient in.

⭐ Before reaching the moon, we need stars: if you find eQual useful, nice, or simply relevant, please consider <a href="#">giving us a star on GitHub</a>! Your support encourages us and will help making eQual the most powerful framework ever.

🛠️ [Contributors welcome!](https://github.com/equalframework/equal/blob/master/.github/CONTRIBUTING.md) You want to contribute to a great open-source project? We need help to keep on 🚀, finishing 🚧, fixing 🐛, and make it 🎨

[![Build Status](https://circleci.com/gh/equalframework/equal.svg?style=shield)](https://circleci.com/gh/equalframework/equal)
![Number of contributors](https://img.shields.io/github/contributors/equalframework/equal)
[![PRs Welcome](https://img.shields.io/badge/PRs-welcome-brightgreen.svg?style=flat-square)](https://github.com/equalframework/equal/pulls)
[![first-timers-only](https://img.shields.io/badge/first--timers--only-friendly-blue.svg?style=flat-square)](https://github.com/equalframework/equal/labels/first-timers-only)
[![License: LGPL v3](https://img.shields.io/badge/License-LGPL%20v3-blue.svg)](https://www.gnu.org/licenses/lgpl-3.0)
![GitHub commit activity](https://img.shields.io/github/commit-activity/m/equalframework/equal)
[![GitHub stars](https://img.shields.io/github/stars/equalframework/equal)](https://github.com/equalframework/equal/stargazers)
[![Discord](https://img.shields.io/discord/999933707475501097?label=Discord&logo=discord)](https://discord.gg/xNAXyhbYBp)
[![Follow us on X, formerly Twitter](https://img.shields.io/twitter/follow/equalframework?style=social)](https://x.com/equalframework)
[![Follow us on Mastodon](https://img.shields.io/mastodon/follow/112820075870021767?domain=https%3A%2F%2Fmastodon.social&style=social)](https://mastodon.social/@equalframework)

<p align="center">
    <img src="https://raw.githubusercontent.com/equalframework/equal/master/packages/core/init/assets/img/equal_logo.png" alt="eQual - Create great Apps, your way!" />
</p>

<h1 align="center">
  ✨ eQual - Create great Apps, your way! ✨
</h1>

<p align="center">
    <img src="https://raw.githubusercontent.com/equalframework/equal/master/packages/core/init/assets/img/equal_summary.png" alt="eQual - Create great Apps, your way!" />
</p>


eQual offers a native Low-Code approach, based on the definition of the application logic and components (rather than on code or the language used).

Data is modeled via entities, to which a large part of the application logic is associated (workflow, roles, events, actions, policies), which are manipulated by the ORM, with which it is possible to interact using CQRS controllers.

In turn, the Controllers can be invoked via an API.

eQual offers tools that allow the visual consultation and editing of the different components, in turn in the form of relational diagrams, and entity, workflow, view, menu, and translation editors.

It also has a rendering engine that allows views and menus to be assembled in order to define a complete application.

This mechanism enables eQual to generate an application without writing a single line of code, providing both a user interface and an API that can be connected to any external service.



## Features

Beside its revolutionary edge, eQual is a fully-featured framework providing an amazing set of both traditional and innovative features.


### Low-Code
:white_check_mark: **Visual Workbench for building Apps**: No-code editor for all components (models, views, routes, ...).  
:white_check_mark: **Instant APIs Without Code**: Auto-documented controllers with an announcement system.  
:white_check_mark: **Desktop & mobile**: Customizable layout to fit any device screen.  
:white_check_mark: **Views**: Create any view without coding - form, lists, menus, charts, Dashboard.  

### Architecture
:white_check_mark: **CQRS architecture**: Division of controllers into Action Handlers, Data Providers, and App Providers.  
:white_check_mark: **MVC segregation**: Strict distinction between Models (entities), Views and Controllers.  
:white_check_mark: **Dependency injection**: Inject services into classes, methods, controllers, or functions anywhere.  
:white_check_mark: **Data Adaptation**: Automatically transform received data based on format and context.  
:white_check_mark: **Services Extensibility**: Ability to extend services behavior, and to register custom ones.  
:white_check_mark: **Cascading Configuration**: Overridable settings at different levels (default, global, package).  
:white_check_mark: **I/O as HTTP messages**: Inputs & outputs are handled as text in all contexts (default is 'application/json').  

### Entities & ORM
:white_check_mark: **Model definition**: With support for inheritance, workflows, actions, roles and policies, transitions, and events.  
:white_check_mark: **CRUD operations**: Perform create, read, update, and delete operations on individual objects or collections.  
:white_check_mark: **Domains**: Simple array notation for any possible boolean condition, either directly or with references.  
:white_check_mark: **Date References**: Enables defining a date relative to another date using specific syntactic notations.  
:white_check_mark: **Explicit typing (Usages)**: Attach any value to a Usage that can be flawlessly adapted (converted) to any other language or environment.  

### Security
:white_check_mark: **Authentication Management**: HTTP Auth support (JWT) & 3 levels of visibility (public, protected, private).  
:white_check_mark: **Granular access control**: Set permissions of any kind at any level (ACL, RBAC, PBAC, ABAC, ReBAC).  
:white_check_mark: **Security Policies**: Restrict access to a set of users or groups, based on their IP or Schedule.  
:white_check_mark: **Secure**: All the credentials are securely encrypted using bcrypt 448-bits with random salt.  

### Native Features
:white_check_mark: **Settings values**: Global, group, user, with multilingual support.  
:white_check_mark: **Emails sending**: Send emails via SMTP, either instantly or queued.  
:white_check_mark: **Multi-formats exports**: Export any Controller data in TXT, CSV, or PDF formats.  

### Miscellaneous
:white_check_mark: **Database**: Ability to connect to various data source (DBMS), with guidelines to develop your own if needed.  
:white_check_mark: **Multi-user**: Allows concurrency through Optimistic Concurrency Control.  
:white_check_mark: **Multi-lang & Multi-locale support**: Provides multilingual support and regional settings.  
:white_check_mark: **Self-host**: Supports Docker, Kubernetes, AWS EC2, Google Cloud Run, and more.  
:white_check_mark: **Embedded documentation**: Embeds end-user documentation directly in Apps.  
:white_check_mark: **CLI**: Powerful command-line tool to easily discover available controllers and their roles & expected params.  
:white_check_mark: **Auto-documented Controllers**: Description with parameters along with their types and attributes.  
:white_check_mark: **Logging**: Recording of the history of changes made to entities, including user and date.  
:white_check_mark: **Scheduled tasks**: Execute controllers as scheduled tasks, either recurring or one-time.  
:white_check_mark: **Alerts system**: Allows to manage alerts (notice or error) linked to Controllers, with ability to ignore retry execution.  

### Coding Amenities
:white_check_mark: **Selectable debug levels and modes**: Report anything, anywhere in the code, with 6 levels and 7 modes.  
:white_check_mark: **Unit testing**: Use AAA (Arrange, Act, Assert) pattern and support for rollback.  
:white_check_mark: **Debug Console**: Search amongst logs, grouped by threads, with keywords, level or mode.  

### Upcoming features
:black_square_button: **Version control**: Keep track of all versions of any object.  
:black_square_button: **Authentication Policies**: Set the level of required auth (token, password, mfa) based on the context (trust).  
:black_square_button: **Pipelines**: Ability to visually create and edit data flow by chaining controllers together.  


## Benefits

**Rock Solid Security** Secure every API endpoint with User Management, Role-Based Access Controls, SSO Authentication, JWT, CORS, and OAuth.

**Server-Side Scripting** Implement custom logic on any endpoint to build your own custom API, and interact with it using your preferred programming language.

**Low-Code Instant APIs** Automatically generate a complete set of ReST API endpoints with live documentation for any SQL or NoSQL database, file storage system, or external service.


## Documentation

For a comprehensive documentation that includes examples and step-by-step instructions, please visit our full documentation at https://doc.equal.run.


## Requirements
eQual requires the following environment:

* **PHP 8+** with extensions (opcache, zip, tidy, ...)
* **Apache 2+** or **Nginx**
* **SQL:2011** compatible DBMS (SQLite, MariaDB Server, Microsoft SQL Server, Oracle MySQL)

## Install

Download code as ZIP:
```
wget https://github.com/equalframework/equal/archive/master.zip
```
or clone with Git :
```
git clone https://github.com/equalframework/equal.git
```

For more info, see : [http://doc.equal.run/getting-started/installation](http://doc.equal.run/getting-started/installation/)

## Contributing
Contributions are what make the open-source community such an amazing place to learn, inspire, and create.

If you'd like to contribute, please kindly read our [Contributing Guide](https://github.com/equalframework/equal/blob/master/.github/CONTRIBUTING.md) to familiarize yourself with our development process, how to suggest bug fixes and improvements, and the steps for building and testing your changes.

## Contributors

<a href="https://github.com/equalframework/equal/graphs/contributors">
  <img src="https://contrib.rocks/image?repo=equalframework/equal&max=400&columns=20" />
</a>

## Stats

![Alt](https://repobeats.axiom.co/api/embed/5f9dfe02bd01e5a3d95b1e2ffde811c02f169000.svg "Repobeats Analytics")

## Questions & Support

For questions or any type of support, you can reach out via [Discord](https://discord.gg/xNAXyhbYBp)

## License

eQual framework project - Released under the [GNU Lesser General Public License v3.0](https://www.gnu.org/licenses/lgpl-3.0).