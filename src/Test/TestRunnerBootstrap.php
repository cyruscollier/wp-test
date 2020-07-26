<?php

namespace WPTest\Test;

use WPTest\Util\Util;

abstract class TestRunnerBootstrap
{
    protected $util;
    protected $wp_includes_path;
    protected $wp_admin_path;

    public function __construct()
    {
        $this->util = new Util();
        $this->wp_includes_path = $this->util->getWPDevelopDirectory() . '/src/wp-includes';
        $this->wp_admin_path = $this->util->getWPDevelopDirectory() . '/src/wp-admin';
    }

    public function require($filename)
    {
        require_once $this->wp_includes_path . DIRECTORY_SEPARATOR . $filename;
    }

    public function requireAdmin($filename)
    {
        require_once $this->wp_admin_path . DIRECTORY_SEPARATOR . $filename;
    }

    abstract public function load();
}