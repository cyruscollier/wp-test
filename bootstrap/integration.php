<?php

use WPTest\Util\Util;
use WPTest\Test\IntegrationBootstrap;

define('WP_TESTS_PATH', dirname(__DIR__));
define('WP_TESTS_SRC_PATH', dirname(__DIR__) . '/src');

require_once WP_TESTS_SRC_PATH . '/Test/IntegrationBootstrap.php';
require_once WP_TESTS_SRC_PATH . '/Util/Util.php';

$Util = new Util();

define('WP_PATH', $Util->getWPDevelopDirectory());
define('WP_INCLUDE_PATH', WP_PATH . '/tests/phpunit/includes');
define('WP_CONFIG_FILE_PATH', $Util->getProjectDirectory() . '/wp-test-config.php');

$bootstrap = new IntegrationBootstrap();
$bootstrap->load();

require_once WP_INCLUDE_PATH . '/bootstrap.php';
require_once WP_TESTS_SRC_PATH . '/Test/TestCase.php';