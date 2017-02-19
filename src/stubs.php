<?php

/* Mocked WP functions used in domain objects */
function apply_filters($tag, $value) { return $value; }
function do_action($tag) { }
function get_the_excerpt($post) { return $post->post_excerpt; }
function maybe_unserialize($value) { return $value; }
function maybe_serialize($value) { return $value; }
function sanitize_title($title) { return $title; }

/* Stubbed WP classes */
class WP_Widget {}