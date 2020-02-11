<?php

namespace WPTest\Test;

class PHPSpecBootstrap extends TestRunnerBootstrap
{
    public function __construct()
    {
        parent::__construct();
        $src_path = dirname(__DIR__);
        require_once $src_path . '/stubs.php';
        require_once $src_path . '/Util/Util.php';
    }

    protected function getIncludePath()
    {
        return $this->util->getWPDevelopDirectory() . '/src/wp-includes';
    }

    public function load()
    {
        define('WP_CONTENT_DIR', $this->util->getWPContentDirectory());

        $this->require('class-wp-post.php');
        $this->require('class-wp-post-type.php');
        $this->require('class-wp-term.php');
        $this->require('class-wp-taxonomy.php');
        $this->require('class-wp-error.php');
    }
}