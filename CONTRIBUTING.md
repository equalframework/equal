# Contributing to eQual

We love your input and new contributions to eQual are most welcome and encouraged! :octocat:

We want to make contributing to this project as easy and transparent as possible, whether it's:

- Reporting a bug
- Discussing the current state of the code
- Submitting a fix
- Proposing new features
- Becoming a maintainer

This document contains notes and guidelines on how to contribute to eQual.

## Joining the project

Active committers and contributors are invited to introduce themselves and request commit access to the project on the Discord [#join](https://discord.com/invite/xNAXyhbYBp) channel. If you think you can help, we'd love to have you!

## First-time contributors

There are always a few issues tagged [Good first issues](https://github.com/equalframework/equal/labels/good%20first%20issue) to make it easy to get started.

If you're interested in working on an issue:

* Make sure it has either a `good-first-issue` label added.
* Add a comment on the issue and wait for the issue to be assigned before you start working on it (this helps to avoid multiple people working on similar issues).

## Bugs and Issues

Please report these on our [GitHub page](https://github.com/equalframework/equal/issues). Please do not use issues for support requests: for help using eQual, please consider Stack Overflow.

Well-structured, detailed bug reports are hugely valuable for the project.

Guidelines for reporting bugs:

* Check the issue search to see if it has already been reported.
* Isolate the problem to a simple test case.
* Please include a code snippet that demonstrates the bug. If filing a bug against master, you may reference the latest code using the URL of the repository of the branch (e.g. [https://github.com/equalframework/equal/blob/master/eq.lib.php](https://github.com/equalframework/equal/blob/master/eq.lib.php) - changing the filename to point at the file you need as appropriate).
* Please provide any additional details associated with the bug, if it's generic or only happens with a certain configuration or data set.

## We Develop with Github

We use GitHub to host code, to track issues and feature requests, as well as for accepting pull requests.

## Any contributions you make will be under the LGPL 3.0 Software License

In short, when you submit code changes, your submissions are understood to be under the same [LGPL License](https://www.gnu.org/licenses/lgpl-3.0.en.html) that covers the project. Feel free to contact the maintainers if that's a concern.

## Submitting Pull Requests

We use [GitHub Flow](https://guides.github.com/introduction/flow/index.html), so all code changes happen through Pull Requests.
Pull requests are the best way to propose changes to the codebase (we use [GitHub Flow](https://guides.github.com/introduction/flow/index.html)). We actively welcome your pull requests:

1. Fork the repo and create your branch from `master` ([https://github.com/equalframework/equal/fork](https://github.com/equalframework/equal/fork))
2. Create your feature branch (`git checkout -b my-new-feature`)
3. Commit your changes (`git commit -am 'Add some feature'`)
3. Push to the branch (`git push origin my-new-feature`)
6. Create a new Pull Request

### Guidelines for submitting a Pull Request:

* Before opening a PR for additions or changes, please discuss those by filing an issue on [GitHub](https://github.com/equalframework/equal/issues) or asking about it on [Discord](https://discord.gg/xNAXyhbYBp) (#general channel). This will save you development time by getting feedback upfront and make review faster by giving the maintainers more context and details.
* Before submitting a PR, ensure that the code works with all PHP versions that we support (currently PHP 7.0 to PHP 7.4); that the test suite passes and that your code lints.
* If you've changed some behavior, update the 'description' and 'help' attributes (when present).
* If you are going to submit a pull request, please fork from `master`, and submit your pull request back as a fix/feature branch referencing the GitHub issue number.
* Please include a Unit Test to verify that a bug exists, and that this PR fixes it.
* Please include a Unit Test to show that a new Feature works as expected.
* Please don't "bundle" several changes into a single PR; submit one PR for each discrete change and/or fix.

### Helpful resources

* [Helpful article about forking](https://help.github.com/articles/fork-a-repo/ "Forking a GitHub repository")
* [Helpful article about pull requests](https://help.github.com/articles/using-pull-requests/ "Pull Requests")

## Submitting a package or a library

### Guidelines for submitting a package or a library:

* Check that your package will pass the consistency tests (`$ ./equal.run --do=test_package-consistency`).
* Make sure your package comes with unit tests (in the `packages/{your_package}/tests/`) and that classes and controllers have descriptions and helpers.

## Submitting eQual core contributions

### Guidelines

* All new development should be on feature/fix branches, which are then merged to the `master` branch once stable and approved; so the `master` branch is always the most up-to-date, working code.
* Avoid breaking changes unless there's an upcoming major release, which is infrequent. We encourage people to write distinct libraries and/or packages for most new advanced features, and care a lot about backwards compatibility.

## Unit Tests

When writing Unit Tests, please:

* Always try to write Unit Tests for both the happy and unhappy scenarios.
* Put all assertions in the Test itself, not in external classes or functions (even if this means code duplication between tests).
* Always try to split the test logic into `AAA` callbacks `arrange()`, `act()`, `assert()` and `rollback()` for each Test.
* If you change any global settings, make sure that you reset to the default in the `rollback()`.
* Don't over-complicate test code by testing several behaviors in the same test.

This makes it easier to see exactly what is being tested when reviewing the PR. We want to be able to see it in the PR, without having to hunt in other unchanged classes to see what the test is doing.

## Naming Conventions

The languages involved in eQual are varied: JS, TS, PHP, JSON, HTML, whether it's at the back-end or front-end. 

To facilitate cross-stack work, communication between teams, and code reading, the same logic is used for the syntax for variables, constants, classes, functions, and methods.

Indeed, consistently following naming conventions throughout the codebase of packages enhances readability, maintainability, and collaboration among developers.

### Conventions used across the framework:

1. **Scalar Variables & Functions - snake_case**

   Scalar variables and functions (or var holding an anonymous function) should be named using snake_case, where words are separated by underscores.  
   Examples: `$total_count`, `calculate_area()`

2. **Objects - camelCase**

   Objects (vars holding instances of classes) should be named using camelCase, starting with a lowercase letter and capitalizing subsequent words.  
   Examples: `$myObject`, `$userProfile`

3. **Classes - PascalCase**

   Classes should be named using PascalCase, where each word in the name is capitalized, including the first word.  
   Example: `BankAccount`, `UserProfileManager`

4. **Class Members - camelCase**

   Class members, including attributes and methods, should follow camelCase convention.  
   Example: `$this->myAttribute`, `calculateInterest()`

5. **Entity Properties - snake_case**

   Entity properties (as defined in `getColumns()` for classes or `params` for controllers), should use snake_case.  
   Example: `first_name`, `is_paid`
   
   Maps and objects properties should be named using snake_case.  
   Example: `{origin_country: "BE", is_shipped: true}`

6. **Controllers - kebab-case**

   Controllers, typically used in web development frameworks, should be named using kebab-case, where words are separated by hyphens.  
   Example: `user-controller.php`, `do-pay.php`


## Commit Convention

As commit convention, we adopt Conventional Commits v1.0.0. We have a history of commits that do not assume the convention, but any new commit should follow it.  
The commit message should be structured as follows:

```
<type>[(scope)]: <description>

[body]

[footer(s)]
```
(items within square brackets are optional)  
[See examples here](https://www.conventionalcommits.org/en/v1.0.0/#examples)

### Key Elements:

* **Type**: 
  - `fix:` for patching a bug
  - `feat:` for introducing a new feature
  - Other common types: `build:`, `docs:`, `style:`, `refactor:`, `test:`
  - Appending `!` after the type means a breaking change.
  
* **Description**: 
All commit messages should describe the action performed by the author of the commit (what has been done : "added", "removed", "improved", "corrected").
Example: `feat: allowed config object to extend other configs`

### Examples:
```
feat: allowed config object to extend other configs
```
```
feat(api)!: added sending of an email to the customer when a product is shipped
```
```
docs: improved comments
```
```
docs: corrected spelling in CHANGELOG
```

## Coding Conventions

Coding conventions guidelines:

* eQual naming conventions should be used.
* Reference guidelines of the code language used should be followed.
* Significant functionality should come with appropriate testing to be run in the automated test suite.
* Licensing guidelines must be followed.

## Licensing Guidelines

The following guidelines apply to all repositories under the eQual organization:

* Our Governance's licensing requirements must be respected.
* Unless otherwise specified, all code must be licensed under the [GNU LGPL 3.0](https://www.gnu.org/licenses/lgpl-3.0.en.html#license-text).
* Each repository must have a `LICENSE` file in its root folder and, when applicable, a `3rdpartylicenses.txt` file.
* If third-party code is used, their licenses must be vetted to ensure compatibility with our licensing requirements.
