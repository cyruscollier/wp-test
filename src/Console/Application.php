<?php

namespace WPTest\Console;

use Symfony\Component\Console\Application as BaseApplication;

class Application extends BaseApplication
{
    /**
     * @param string $version
     */
    public function __construct($version)
    {
        parent::__construct('wp-test', $version);
        $this->add(new InitCommand());
    }
}