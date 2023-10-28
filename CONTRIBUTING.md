# Contributing to eQual

We love your input and new contributions to eQual are most welcome! We want to make contributing to this project as easy and transparent as possible, whether it's:

- Reporting a bug
- Discussing the current state of the code
- Submitting a fix
- Proposing new features
- Becoming a maintainer

This document contains notes and guidelines on how to contribute to eQual.


## Joining the project

Active committers and contributors are invited to introduce themselves and request commit access to the project on the Discord [#join](https://discord.gg/65WcBQFVg6) channel. If you think you can help, we'd love to have you!


## Bugs and Issues

Please report these on our [GitHub page](https://github.com/equalframework/equal/issues) . Please do not use issues for support requests: for help using eQual, please consider Stack Overflow.

Well structured, detailed bug reports are hugely valuable for the project.

Guidelines for reporting bugs:

* Check the issue search to see if it has already been reported.
* Isolate the problem to a simple test case.
* Please include a code snippet that demonstrates the bug. If filing a bug against master, you may reference the latest code using the URL of the repository of the branch (e.g. `https://github.com/equalframework/equal/blob/master/eq.lib.php` - changing the filename to point at the file you need as appropriate). 
* Please provide any additional details associated with the bug, if it's generic or only happens with a certain configuration or data set.

## We Develop with Github

We use github to host code, to track issues and feature requests, as well as for accepting pull requests.

## Any contributions you make will be under the LGPL 3.0 Software License

In short, when you submit code changes, your submissions are understood to be under the same [LGPL License](https://www.gnu.org/licenses/lgpl-3.0.en.html) that covers the project. Feel free to contact the maintainers if that's a concern.

## Submitting Pull Requests

We use [Github Flow](https://guides.github.com/introduction/flow/index.html), so all code changes happen through Pull Requests.
Pull requests are the best way to propose changes to the codebase (we use [Github Flow](https://guides.github.com/introduction/flow/index.html)). We actively welcome your pull requests:


1. Fork the repo and create your branch from `master`  ([https://github.com/equalframework/equal/fork](https://github.com/equalframework/equal/fork))
2. Create your feature branch (`git checkout -b my-new-feature`)
3. Commit your changes (`git commit -am 'Add some feature'`)
3. Push to the branch (`git push origin my-new-feature`)
6. Create a new Pull Request

### Guidelines
* Before opening a PR for additions or changes, please discuss those by filing an issue on [GitHub](https://github.com/equalframework/equal/issues) or asking about it on [Discord](https://discord.gg/BNCPYxD9kk) (#general channel). This will save you development time by getting feedback upfront and make review faster by giving the maintainers more context and details.
* Before submitting a PR, ensure that the code works with all PHP versions that we support (currently PHP 7.0 to PHP 7.4); that the test suite passes and that your code lints.
* If you've changed some behavior, update the 'description' and 'help' attributes (when present).
* If you are going to submit a pull request, please fork from `master`, and submit your pull request back as a fix/feature branch referencing the GitHub issue number
* Please include a Unit Test to verify that a bug exists, and that this PR fixes it.
* Please include a Unit Test to show that a new Feature works as expected.
* Please don't "bundle" several changes into a single PR; submit one PR for each discrete change and/or fix.


### Helpful resources
* [Helpful article about forking](https://help.github.com/articles/fork-a-repo/ "Forking a GitHub repository")
* [Helpful article about pull requests](https://help.github.com/articles/using-pull-requests/ "Pull Requests")



## Submitting a package or a library

### Guidelines

* Check that your package will pass the consistency tests (`$ ./equal.run --do=test_package-consistency`).
* Make sure your package comes with unit tests (in the `packages/{your_package}/tests/`) and that classes and controllers have descriptions and helpers.

## Submitting eQual core contributions

### Guidelines

* All new development should be on feature/fix branches, which are then merged to the `master` branch once stable and approved; so the `master` branch is always the most up-to-date, working code
* Avoid breaking changes unless there is an upcoming major release, which is infrequent. We encourage people to write distinct libraries and/or packages for most new advanced features, and care a lot about backwards compatibility.


## Unit Tests
When writing Unit Tests, please
* Always try to write Unit Tests for both the happy and unhappy scenarios.
* Put all assertions in the Test itself, not in external classes or functions (even if this means code duplication between tests).
* Always try to split the test logic into AAA callbacks `arrange()`, `act()`, `assert()` and `rollback()` for each Test.
*  If you change any global settings, make sure that you reset to the default in the `rollback()`.
* Don't over-complicate test code by testing several behaviors in the same test.

This makes it easier to see exactly what is being tested when reviewing the PR. I want to be able to see it in the PR, not have to hunt in other unchanged classes to see what the test is doing.
