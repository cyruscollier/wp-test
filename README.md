# WP Test

WP Test is a library for quickly and easily setting up and executing WordPress unit and integration tests. 
It allows you to initialize an automated test suite on any new or existing WordPress theme, plugin or full website project.

## Basic Features

* Uses the same [PHPUnit](https://github.com/sebastianbergmann/phpunit) based framework that powers the [WordPress Core](https://github.com/wordpress/wordpress) test suite
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

## Installation via Composer

Add the dedicated package repository for WordPress Core<sup>[[1]](#footnote-1)</sup> and download the Composer package as a dev dependency to your WordPress project:

```
composer config repositories.cyruscollier composer https://packages.cyruscollier.com
composer require cyruscollier/wp-test --dev
```

Run the initialization console command. You may leave off the full path if your system `$PATH` already includes your local Composer bin directory.

```
./vendor/bin/wp-test init
```

Follow the prompts in the console to configure your testing environment.

1. Choose Unit Testing Architecture
1. Project namespace
1. Source files path
1. Path to unit tests
1. Path to integration tests (Advanced TDD only)
1. Path to wp-content directory, relative to project root
1. Active theme

## Installing a local MySQL database

Required for the PHPUnit/WordPress Core runtime environment. 
There are many ways to install a mysql server, depending on your operating system. 
Here are two recommended methods:

Homebrew (Mac OS only)
```
brew update
brew install mysql@5.7
brew link mysql@5.7 --force
brew services start mysql@5.7
mysql -uroot -e "CREATE DATABASE IF NOT EXISTS wp_tests CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

Docker
```
docker run -d -p 3306:3306 --name wp_tests -e MYSQL_ALLOW_EMPTY_PASSWORD=yes -e MYSQL_DATABASE=wp_tests mysql:5.7
```
Docker requires a TCP connection to the mysql container, so in `wp-tests-config.php`, change `DB_HOST` to `127.0.0.1`.

A VM (vagrant, etc.) or Docker mysql server can also be used, 
but either the server must be setup to accept requests from any host using the configured user over an open port (like 3306),
or all test run commands must be executed directly in that environment instead of in the host machine's terminal.

## Usage

In your project root, run PHPUnit:

```
./vendor/bin/phpunit
```

or run the watcher to re-run tests whenever any of your code changes:

```
./vendor/bin/phpunit-watcher watch
```

For integration tests:

```
./vendor/bin/phpunit --group integration
```

Full PHPUnit documentation: [https://phpunit.readthedocs.io/en/7.5/]

If using Advanced TDD Mode, run phpspec:

```
./vendor/bin/phpspec run
```

or run the watcher to re-run tests whenever any of your code changes:

```
./vendor/bin/phpspec-watcher watch
```

Full phpspec documentation: [https://www.phpspec.net/en/stable/manual/introduction.html]

## Changelog

v1.2 - Setup improvements and fixes, added `wp-test reset` command, internal refactoring, updated readme.

v1.1 - Revised `wp-test init` command to allow choice to use phpspec or not for unit tests. Improvements and fixes to config templates. Added phpunit-watcher. Added readme. 

v1.0.2 - Fix phpunit.xml template to align with WP Core

v1.0.1 - New repo for WP Core, fix config paths and PHPUnit version to WP compatibility

v1.0 - Initial version

## Footnotes

<sup id="footnote-1">[1]</sup> There is an [open WordPress Trac ticket](https://core.trac.wordpress.org/ticket/49077) to add WordPress Core to Packagist so the separate repository won't be required at some point in the future.