<?php

namespace WPTest\Test\Traits;

use WPTest\Test\DidActionConstraint;
use WPTest\Test\HasFilterConstraint;

trait AssertsFilters
{
    /**
     *
     * @param string $hook
     * @param mixed $call either function name or object instance
     * @param string $method_name
     * @param string $message
     */
    public function assertHasFilter($hook, $call, $method_name = null, string $message = '') {
        self::assertThat($hook, new HasFilterConstraint($call, $method_name), $message);
    }

    /**
     * @param string $hook
     * @param mixed $call either function name or object instance
     * @param string $method_name
     * @param string $message
     */
    public function assertHasAction($hook, $call, $method_name = null, string $message = '') {
        $this->assertHasFilter($hook, $call, $method_name, $message);
    }

    /**
     *
     * @param string $hook
     * @param int $count
     * @param string $message
     *
     */
    public function assertDidAction( $hook, $count = 1, string $message = '') {
        self::assertThat($hook, new DidActionConstraint($count), $message);
    }

    /**
     * @param string $hook
     * @param string $message
     */
    public function assertDidNotDoAction($hook, string $message = '') {
        $this->assertDidAction($hook, 0, $message);
    }
}