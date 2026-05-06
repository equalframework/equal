## PHPUnit Configuration

Create a `phpunit.xml` file in your project root:
```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="https://schema.phpunit.de/9.5/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         cacheResultFile="cache/test-results"
         executionOrder="depends,defects"
         forceCoversAnnotation="false"
         beStrictAboutCoversAnnotation="false"
         beStrictAboutOutputDuringTests="true"
         beStrictAboutTodoAnnotatedTests="true"
         convertDeprecationsToExceptions="true"
         failOnRisky="false"
         failOnWarning="true"
         verbose="true">
    <testsuites>
        <testsuite name="default">
            <directory>tests</directory>
        </testsuite>
    </testsuites>

    <coverage cacheDirectory="cache/code-coverage"
              processUncoveredFiles="true">
        <include>
            <directory suffix=".php">lib</directory>
        </include>
    </coverage>
</phpunit>
```

Alternatively, generate the configuration file:
```bash
./vendor/bin/phpunit --generate-configuration
```

## Running Code Coverage Tests

Run the following command to execute tests and generate an HTML coverage report:
```bash
XDEBUG_MODE=coverage ./vendor/bin/phpunit --coverage-html tests/report
```

The report will be available in the `tests/report` directory.

## Minimal Test Implementation

Create a `tests/CoverageTest.php` file:
```php
<?php
use PHPUnit\Framework\TestCase;
use equal\test\Tester;
include('eq.lib.php');

final class CoverageTest extends TestCase {

    public function test(): void {
        $tests_path = "packages/core/tests";
        if (is_dir($tests_path)) {
            foreach (glob($tests_path . "/*.php") as $filename) {
                include($filename);
                $tester = new Tester($tests);
                $tester->test()->toArray();
            }
        }
        $this->assertTrue(true);
    }
}
```

## Unit Tests

When writing Unit Tests, please:

* Always try to write Unit Tests for both the happy and unhappy scenarios.
* Put all assertions in the Test itself, not in external classes or functions (even if this means code duplication between tests).
* Always try to split the test logic into `AAA` callbacks `arrange()`, `act()`, `assert()` and `rollback()` for each Test.
* If you change any global settings, make sure that you reset to the default in the `rollback()`.
* Don't over-complicate test code by testing several behaviors in the same test.

This makes it easier to see exactly what is being tested when reviewing the PR. We want to be able to see it in the PR, without having to hunt in other unchanged classes to see what the test is doing.

---