<?php

namespace WPTest\Test;

class PHPSpecBootstrap extends TestRunnerBootstrap
{
    public function __construct()
    {
        parent::__construct();
        require_once dirname(__DIR__) . '/stubs.php';
    }

    public function load()
    {
        define('WP_CONTENT_DIR', sprintf('%s/%s', $this->util->getProjectDirectory(), $this->util->getWPContentPath()));

        $this->require('class-wp-post.php');
        $this->require('class-wp-post-type.php');
        $this->require('class-wp-term.php');
        $this->require('class-wp-taxonomy.php');
        $this->require('class-wp-error.php');
    }
}