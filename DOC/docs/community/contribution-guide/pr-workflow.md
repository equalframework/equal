## Submitting Pull Requests

We use [GitHub Flow](https://guides.github.com/introduction/flow/index.html), so all code changes happen through Pull Requests.
Pull requests are the best way to propose changes to the codebase (we use [GitHub Flow](https://guides.github.com/introduction/flow/index.html)). We actively welcome your pull requests:

1. Fork the repo and create your branch from `master` ([https://github.com/equalframework/equal/fork](https://github.com/equalframework/equal/fork))
2. Create your feature branch (`git checkout -b my-new-feature`)
3. Commit your changes (`git commit -am 'Add some feature'`)
3. Push to the branch (`git push origin my-new-feature`)
6. Create a new Pull Request

### Guidelines for Submitting a Pull Request:

* Before opening a PR for additions or changes, please discuss those by filing an issue on [GitHub](https://github.com/equalframework/equal/issues) or asking about it on [Discord](https://discord.gg/xNAXyhbYBp) (#general channel). This will save you development time by getting feedback upfront and make review faster by giving the maintainers more context and details.
* Before submitting a PR, ensure that the code works with all PHP versions that we support (currently PHP 7.0 to PHP 7.4); that the test suite passes and that your code lints.
* If you've changed some behavior, update the 'description' and 'help' attributes (when present).
* If you are going to submit a pull request, please fork from `master`, and submit your pull request back as a fix/feature branch referencing the GitHub issue number.
* Please include a Unit Test to verify that a bug exists, and that this PR fixes it.
* Please include a Unit Test to show that a new Feature works as expected.
* Please don't "bundle" several changes into a single PR; submit one PR for each discrete change and/or fix.

### Helpful Resources

* [Helpful article about forking](https://help.github.com/articles/fork-a-repo/ "Forking a GitHub repository")
* [Helpful article about pull requests](https://help.github.com/articles/using-pull-requests/ "Pull Requests")

---