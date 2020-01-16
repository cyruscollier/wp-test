# WP Test

WP Test is a library for rapid setup and execution of WordPress unit and integration tests on WordPress. 
It allows you to initialize an automated test suite on any new or existing WordPress projects.

## Basic Features

* Uses the same [PHPUnit](https://github.com/sebastianbergmann/phpunit) framework that powers the [WordPress Core](https://github.com/wordpress/wordpress) test suite
* CLI command to create and customize config files inside your project required by the Core test suite
* Subclass of `WP_UnitTestCase` with added convenience methods and custom assertions for testing actions and filters
* Enhanced PHPUnit bootstrap process to automatically activate your project's theme and plugins
* Use for unit testing your code and for integration tests with external services (Stripe, Facebook, etc.)

## Advanced TDD mode
* Uses [phpspec](https://github.com/phpspec/phpspec) for design and unit test a dependency-free domain model
* Uses Basic setup above to drive integration tests only, treating WordPress itself as an external dependency
* Includes bare minimum of WP classes into phpspec to facilitate common spec use cases without running WordPress
* Includes stubs for most common WP functions
* Uses [PhpSpec - PHP-Mock](http://github.com/cyruscollier/phpspec-php-mock) to mock other WP function on demand in specs

## Changelog

v1.1-alpha - Revised `wp-test init` command to allow choice to use phpspec or not for unit tests. Improvements and fixes to config templates. Added readme. 

v1.0.2 - Fix phpunit.xml template to align with WP Core

v1.0.1 - New repo for WP Core, fix config paths and PHPUnit version to WP compatibility

v1.0 - Initial version

## Installation

Download Composer package as a dev dependency to your WordPress project:

```
composer require cyruscollier/wp-test --dev
```

Run the initialization console command. You may leave off the full path if your system `$PATH ` already includes your local composer bin directory

```
./vendor/bin/wp-test init
```

Follow the prompts in the console to configure your testing environment.

##Configuration:

*Choose Unit Testing Architecture:*

1. Basic (default): Run WordPress application using PHPUnit for unit and integration tests.     
1. Advanced TDD: Dependency-free unit tests using phpspec. Default setup for integration tests.

