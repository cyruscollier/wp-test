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

/* Mocked WP functions used in domain objects */
function apply_filters($tag, $value) { return $value; }
function do_action($tag) { }
function get_the_excerpt($post) { return $post->post_excerpt; }
function maybe_unserialize($value) { return $value; }
function maybe_serialize($value) { return $value; }
function sanitize_title($title) { return $title; }

/* Stubbed WP classes */
class WP_Widget {}