<?php

namespace WPTest\Test;

use WPTest\Util\Util;

abstract class TestRunnerBootstrap
{
    protected $util;
    protected $include_path;

    public function __construct()
    {
        $this->util = new Util();
        $this->include_path = $this->getIncludePath();
    }

    public function require($filename)
    {
        require_once $this->include_path . '/'. $filename;
    }

    abstract protected function getIncludePath();

    abstract public function load();
}