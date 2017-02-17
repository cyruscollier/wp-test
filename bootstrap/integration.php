<?php

use WPTest\Util\Util;
use WPTest\Util\IntegrationBootstrap;

define('WP_TESTS_PATH', dirname(__DIR__));

require_once WP_TESTS_PATH . '/src/Test/IntegrationBootstrap.php';
require_once WP_TESTS_PATH . '/Util/Util.php';

$Util = new Util();

define('WP_PATH', $Util->getWPDevelopDirectory());
define('WP_INCLUDE_PATH', WP_PATH . '/tests/phpunit/includes');

$bootstrap = new IntegrationBootstrap();
$bootstrap->load();

require_once WP_INCLUDE_PATH . '/bootstrap.php';
require_once WP_TESTS_PATH . '/Test/TestCase.php';