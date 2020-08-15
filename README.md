![WP Test](https://github.com/cyruscollier/wp-test/blob/master/logo.png?raw=true)

WP Test is a library for quickly and easily setting up and executing WordPress unit and integration tests.
It allows you to initialize an automated test suite on any new or existing WordPress theme, plugin or full website project.

## Basic Features

* Uses the same [PHPUnit](https://github.com/sebastianbergmann/phpunit) based framework that powers the [WordPress Core](https://github.com/wordpress/wordpress) test suite
* CLI command to create and customize config files inside your project required by the Core test suite
* [Subclass of `WP_UnitTestCase`](https://github.com/cyruscollier/wp-test/blob/master/src/Test/TestCase.php) with additional convenience methods and custom assertions
* [Enhanced PHPUnit bootstrap process](https://github.com/cyruscollier/wp-test/blob/master/src/Test/PHPUnitBootstrap.php) to automatically activate your project's theme and plugins
* In addition to unit testing your code, there is a separate test group for integration tests with external services (Stripe, Facebook, etc.)

## Advanced TDD mode
* Uses [phpspec](https://github.com/phpspec/phpspec) for designing and unit testing a dependency-free domain model
* Uses Basic setup above to drive integration tests only, treating WordPress itself as an external dependency in specs
* [Includes bare minimum of WP classes](https://github.com/cyruscollier/wp-test/blob/master/src/Test/PHPSpecBootstrap.php) into phpspec to facilitate common spec use cases without running WordPress
* [Includes stubs for most common WP functions](https://github.com/cyruscollier/wp-test/blob/master/src/stubs.php)
* Uses [PhpSpec - PHP-Mock](http://github.com/cyruscollier/phpspec-php-mock) to mock other WP function on demand in specs

## Installation via Composer

Add the dedicated package repository for WordPress Core<sup>[[1]](#footnote-1)</sup> and download the Composer package as a dev dependency to your WordPress project:

```shell
composer config repositories.cyruscollier composer https://packages.cyruscollier.com
composer require cyruscollier/wp-test --dev
```

Run the initialization console command. You may leave off the full path if your system `$PATH` already includes your local Composer bin directory.

```shell
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
```shell
brew update
brew install mysql@5.7
brew link mysql@5.7 --force
brew services start mysql@5.7
mysql -uroot -e "CREATE DATABASE IF NOT EXISTS wp_tests CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

Docker
```shell
docker run -d -p 3306:3306 --name wp_tests -e MYSQL_ALLOW_EMPTY_PASSWORD=yes -e MYSQL_DATABASE=wp_tests mysql:5.7
```
Docker requires a TCP connection to the mysql container, so in `wp-tests-config.php`, change `DB_HOST` to `127.0.0.1`.

A VM (vagrant, etc.) or Docker mysql server can also be used,
but either the server must be setup to accept requests from any host using the configured user over an open port (like 3306),
or all test run commands must be executed directly in that environment instead of in the host machine's terminal.

## Usage

In your project root, run PHPUnit:

```shell
./vendor/bin/phpunit
```

or [run the watcher](https://github.com/spatie/phpunit-watcher) to re-run tests whenever any of your code changes:

```shell
./vendor/bin/phpunit-watcher watch
```

For integration tests:

```shell
./vendor/bin/phpunit --group integration
```

Full PHPUnit documentation: https://phpunit.readthedocs.io/en/7.5/

If using Advanced TDD Mode, run phpspec:

```shell
./vendor/bin/phpspec run
```

or [run the watcher](https://github.com/fetzi/phpspec-watcher) to re-run tests whenever any of your code changes:

```shell
./vendor/bin/phpspec-watcher watch
```

Full phpspec documentation: https://www.phpspec.net/en/stable/manual/introduction.html

## Writing WordPress PHPUnit Tests

### Class Setup

For each PHPUnit test class, extend WP Test's `WPTest\Test\TestCase` class,
a subclass of WordPress's `WP_UnitTestCase` class, which itself extends PHPUnit's `PHPUnit\Framework\TestCase` class.

If you add `setup()` or `teardown()` method to your test class, you must be sure to call the parent method inside it.
WordPress starts a database transaction and prepares global state during `setup()`
and rolls back the transaction and resets global and other state back to baseline.
This ensures each test method starts with a clean WordPress environment exactly as expected.

### Factories & Managing State

Although you have full access to the entire WordPress core API,
the test environment Factories are useful for easily creating posts, terms, users and other entities.
Dummy data will be added to any database field that is not supplied.
For the Post Factory for example, in one line you can create and return a new post with whatever custom data you need on it:

```php
/* Test */
$event_post = $this->factory()->post->create_and_get([
    'post_type' => 'event',
    'meta_input' => [
        'event_start' => '2021-01-01 18:00:00',
        'event_end' => '2021-01-01 21:00:00'
    ]
]);
```

Like most unit tests, start a test method with setting up whatever state is needed before executing the method/function under test.
For WordPress, this usually means one or more of the following:

1. Creating WordPress entities: posts, terms, or users
1. Adding meta to newly-created entities or adding terms to posts.
1. Setting built-in or custom options
1. Populating PHP super globals manually, such as `$_GET`, `$_POST`, etc.
1. Calling WordPress functions that set various global state, such as `set_current_screen()`, `wp_set_current_user()`, etc.
1. Manipulating WordPress globals manually, such as `$post`, `$wp_query`, etc.

Then execute the function/method and make PHPUnit assertions about how database or other state has changed.
WP Test provides several additional WordPress-specific assertions on top of the ones supplied by WordPress.

Since the WordPress database and global state remains throughout the duration of a single test method,
it can sometime be a helpful technique to test several different variations or stages of the function/method under test within one test method,
Otherwise you have to recreate the same initial or resulting state in a separate method, which duplicates work and slows down the test suite.
Use with caution though, as you still want to only be testing one discreet behavior of your code base in a single test method.

### Mocking HTTP Requests

Making real HTTP requests inside unit tests make the test suite slow and brittle, so it's best to mock the request and response.
Assuming your code is using `wp_remote_get()`, `wp_remote_post()` or similar wrappers of `WP_Http`,
use the `pre_http_request` filter to make assertions on expected inputs and return a fake response array containing a `body` array. You should also simluate returning a `WP_Error` object as the response, so your code can handle it appropriately:

```php
/* Source */
function make_api_request($parameter)
{
    $response = wp_remote_post('https://yourapi.com/path/to/resource/', [
        'body' => [
           'keyword' => $parameter,
           'apikey' => 'your api key'
        ]
    ]);
    if (is_wp_error($response)) {
        throw new Exception($response->get_error_message());
    }
    return json_decode($response['body']);
);

```

```php
/* Test */
add_filter('pre_http_request', function($pre, $parsed_args, $url) {
    $this->assertEquals('https://yourapi.com/path/to/resource/', $url);
    $this->assertContains(['method' => 'POST', 'body' => [
        'keyword' => 'test keyword',
        'apikey' => 'your api key'
    ]], $parsed_args);
    return ['body' => json_encode(['message' => 'success']])];
}, 10, 3);
$this->assertEquals(['message' => 'success'], make_api_request('test keyword'));
```

```php
/* Test */
add_filter('pre_http_request', function($pre, $parsed_args, $url) {
    $this->assertContains(['method' => 'POST', 'body' => [
        'keyword' => 'bad keyword',
        'apikey' => 'invalid api key'
    ]], $parsed_args);
    return new WP_Error('http_request_failed', 'Invalid API Key');
}, 10, 3);
$this->expectException(Exception::class);
$this-expectExceptionMessage('Invalid API Key');
make_api_request('test keyword');
```

### Mocking Redirects

Most unit tests are directed at lower-level code and typically won't deal with higher-level application logic like redirects. However, if you decide to unit test application logic like form submissions and redirects, you need to be able to verify the redirect URL without actually outputting a Location header. Outputting the header will trigger a "Headers Already Sent" warning in the test environment because it is an long-running PHP process that isn't serving a response to a browser. Most calls to `wp_redirect()` are followed shortly after with `exit`/`die`, which also can't happen in the test envionment since it will terminate the process. Since a successful `wp_redirect()` will return `true`, check the return value of `wp_redirect()` before exiting using this useful, one-line conditional:

```php
/* Source */
return wp_redirect($redirect_url) && exit;
```

Then in your test, add a filter similar to mocking HTTP requests that returns false instead, thereby avoiding the header and exit:

```php
/* Test */
add_filter('wp_redirect', function($url) {
    $this->assertEquals('https://yoursite.com/form-success-page', $url);
    return false;
});
$this->assertFalse($Form->redirect());
```

### Testing Output

Testing PHP output from `echo`, `printf()`, etc. is not WordPress-specific, but it comes up a lot more because so much of the API requires output to be echoed rather than simply returning a value. To test these scenarios, use output buffering. You can either capture the output directly using `ob_get_clean()` and use WP Test's `assertHTMLEquals()` assertion to compare it to an expected HTML snippet, or if the data used to prepare the output is more useful or easier to work it, just return it after the output and assert against it.

```php
/* Source */
function output_something()
{
    // ...
    $data = ['thing 1', 'thing 2'];
    printf('<ul><li>%s</li></ul>', implode('</li><li>', $data));
    return $data;
}
```

```php
/* Test */
ob_start();
output_something();
$output = ob_get_clean();
$this->assertHTMLEquals('<ul><li>thing 1</li><li>thing 2</li></ul>', $output);
// or
ob_start();
$data = output_something();
ob_get_clean();
$this->assertEquals(['thing 1', 'thing 2'], $data);
```

### Testing Hooks

Testing hooks involves two parts. First, verify that the hook has been added with its assigned callback. Second, either fire that hook or execute the function directly, and make assertions based on its return value, state change, etc. Make sure the hook's callback function is a referenceable function or method, not an anonymous function. It's helpful for action callbacks to return useful data that can be asserted against, even though that return value isn't used in live execution:

```php
/* Source */
function perform_custom_action()
{
    // ...
    $data = ['thing 1', 'thing 2'];
    // ...
    return $data;
}

add_action('init', 'perform_custom_action');
```

```php
/* Test */
$this->assertHasAction('init', 'perform_custom_action');
$this->assertEquals(['thing 1', 'thing 2'], perform_custom_actio());
```


## Changelog

v1.2 - Setup improvements and fixes, added `wp-test reset` command, internal refactoring, updated readme.

v1.1 - Revised `wp-test init` command to allow choice to use phpspec or not for unit tests. Improvements and fixes to config templates. Added phpunit-watcher. Added readme.

v1.0.2 - Fix phpunit.xml template to align with WP Core

v1.0.1 - New repo for WP Core, fix config paths and PHPUnit version to WP compatibility

v1.0 - Initial version

## Footnotes

<sup id="footnote-1">[1]</sup> There is an [open WordPress Trac ticket](https://core.trac.wordpress.org/ticket/49077) to add WordPress Core to Packagist so the separate repository won't be required at some point in the future.
