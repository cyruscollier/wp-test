<?php

use WPTest\Util\Util;

define('WP_TESTS_PATH', dirname(__DIR__));

require_once WP_TESTS_PATH . '/src/Util/Util.php';
$Util = new Util();

define('WP_PATH', $Util->getWPDevelopDirectory());

require_once WP_PATH . '/tests/phpunit/includes/bootstrap.php';