
#### Integrating Tests in CI/CD

To run tests in a CI/CD pipeline, add the following commands to your `.travis.yml` file:

```yaml
# Initialize data set
- php run.php --do=init_package --package=mypackage
# Run tests
- php run.php --do=test_package --package=mypackage
```

> **Note:** If tests pass locally but fail in CI, ensure the host is set to `localhost` instead of `127.0.0.1`.

## PHP Compatibility in CI/CD Pipelines

A CI/CD pipeline is maintained to automate testing processes.
The official branch uses a CircleCI workflow and config file source code can be found at the following URL: [CircleCI Configuration File](https://github.com/equalframework/equal/blob/master/.circleci/config.yml).

Collecting workspace informationThe content of the code-coverage.md file can be divided into smaller sections and integrated into your architecture as follows:

## Code Coverage Setup

To enable code coverage for PHP 7.4 with Xdebug, follow these steps:

### 1. Install Xdebug
In your Dockerfile, add the following command:
```bash
RUN pecl install xdebug-3.1.6
```

Update your `php.ini`:
```ini
zend_extension=xdebug
```

### 2. Create a Tests Directory
```bash
mkdir tests
```

### 3. Install Dependencies
Add the following to your `composer.json`:
```json
{
    "require-dev": {
        "phpunit/phpunit": "^9.5",
        "phpunit/php-code-coverage": "^9.2",
        "symplify/easy-coding-standard": "^11.1"
    }
}
```

Run:
```bash
./equal.run --do=init_composer
```

## Code Coverage in CI/CD

To integrate code coverage into your CI/CD pipeline, add the following step to your pipeline configuration:

```yaml
- name: Run Code Coverage
  run: |
    XDEBUG_MODE=coverage ./vendor/bin/phpunit --coverage-html tests/report
```

Ensure that the `tests/report` directory is archived or uploaded as an artifact for review.


Collecting workspace informationThe content of the ci-cd.md file can be divided into smaller sections and integrated into your architecture under the **Operations (DevOps / Sysadmin)** category. Here's how I suggest structuring and placing the content:

# CI/CD Overview

CI/CD (standing for Continuous Integration & Continuous Development) is an [Agile](https://www.agilealliance.org/agile101/the-agile-manifesto/) concept closely related to Test Driven Development (TDD).

The idea is to run a set of tests whenever an update is made during development. By doing so, we have a feedback loop as small as possible that allows us to quickly identify potential issues.

A real-world situation would be, for example, using a GIT versioning platform with a CI pipeline service that automatically runs our test units in any needed environment, whenever we make a new commit.

We'll immediately see if something goes wrong and easily locate where the problem comes from depending on the quality of our test set.

## Travis CI

[Travis CI](https://docs.travis-ci.com/) is a testing environment offering many options and compatibilities.

Example `.travis.yml` configuration:
```yaml
dist: xenial
language: php
php:
  - '7.2'
services:
  - mysql
script:
  - php run.php --do=test_fs-consistency
  - php run.php --do=test_db-connectivity
  - php run.php --do=init_db
  - php run.php --do=init_package --package=core
  - php run.php --do=test_package --package=core
```

## BitBucket Pipelines

[BitBucket](https://bitbucket.org/) has a built-in testing environment called Pipelines that relies on [Docker](https://www.docker.com/).

Example configuration:
```yaml
image: php:7.2-fpm
pipelines:
  default:
    - step:
        script:
          - apt-get update && apt-get install -y default-mysql-client
          - docker-php-ext-install mysqli
          - php run.php --do=test_fs-consistency
          - php run.php --do=test_db-connectivity
          - php run.php --do=init_db
          - php run.php --do=init_package --package=core
          - php run.php --do=test_package --package=core
```

## CircleCI

Example `.config.yml` configuration:
```yaml
version: 2.1
jobs:
  build:
    docker:
    - image: php:7.3-apache
    - image: mysql:5.7
      environment:
        MYSQL_ROOT_PASSWORD: test
        MYSQL_DATABASE: equal
    steps:
      - run: apt-get update && apt-get -y install git
      - run: docker-php-ext-install pdo pdo_mysql mysqli
      - run: php run.php --do=test_fs-consistency
      - run: php run.php --do=test_db-connectivity
      - run: php run.php --do=init_db
      - run: php run.php --do=init_package --package=core
      - run: php run.php --do=test_package --package=core
```

### Pipelines in CI/CD

Pipelines can be integrated into CI/CD workflows to automate data processing and backend operations. For example:

1. **Define a Pipeline**: Use the Pipeline Editor to create a sequence of operations.
2. **Test the Pipeline**: Run the pipeline locally to ensure it works as expected.
3. **Integrate with CI/CD**: Add the pipeline to your CI/CD configuration.

#### Example CI/CD Configuration

```yaml
version: 2.1
jobs:
  build:
    docker:
    - image: php:7.3-apache
    - image: mysql:5.7
      environment:
        MYSQL_ROOT_PASSWORD: test
        MYSQL_DATABASE: equal
    steps:
      - run: php run.php --do=test_fs-consistency
      - run: php run.php --do=test_db-connectivity
      - run: php run.php --do=init_package --package=core

---