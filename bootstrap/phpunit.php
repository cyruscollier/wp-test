<?php

use WPTest\Util\Util;
use WPTest\Test\PHPUnitBootstrap;

define('WP_TESTS_PATH', dirname(__DIR__));
define('WP_TESTS_SRC_PATH', dirname(__DIR__) . '/src');

require_once WP_TESTS_SRC_PATH . '/Test/PHPUnitBootstrap.php';
require_once WP_TESTS_SRC_PATH . '/Util/Util.php';

$Util = new Util();

define('WP_PATH', $Util->getWPDevelopDirectory());
define('WP_INCLUDE_PATH', WP_PATH . '/tests/phpunit/includes');
define('WP_TESTS_CONFIG_FILE_PATH', $Util->getProjectDirectory() . '/wp-tests-config.php');

$bootstrap = new PHPUnitBootstrap();
$bootstrap->load();

require_once WP_INCLUDE_PATH . '/bootstrap.php';
require_once WP_TESTS_SRC_PATH . '/Test/TestCase.php';