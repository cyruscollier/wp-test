<?php

use WPTest\Util\Util;

define('WP_TESTS_PATH', dirname(__DIR__));
define('WP_TESTS_SRC_PATH', dirname(__DIR__) . '/src');

require_once WP_TESTS_SRC_PATH . '/Util/Util.php';
$Util = new Util();

define('WP_PATH', $Util->getWPDevelopDirectory());
define('WP_CONTENT_DIR', $Util->getWPContentDirectory());

require_once WP_PATH . '/src/wp-includes/class-wp-post.php';
require_once WP_PATH . '/src/wp-includes/class-wp-post-type.php';
require_once WP_PATH . '/src/wp-includes/class-wp-error.php';
