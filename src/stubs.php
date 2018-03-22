<?php

/* Mocked WP functions used in domain objects */
if (defined('PHPSPEC_VERSION')) {
    function apply_filters($tag, $value) { return $value; }
    function do_action($tag) { }
    function get_the_excerpt($post) { return $post->post_excerpt; }
    function maybe_unserialize($value) { return $value; }
    function maybe_serialize($value) { return $value; }
    function sanitize_title($title) { return $title; }
    function is_wp_error($thing) { return $thing instanceof WP_Error; }

    /* Stubbed WP classes */
    class WP_Widget {}
}