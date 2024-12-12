<?php

namespace WPTest\Test;

use PHPUnit\Framework\Constraint\Constraint;

class HasFilterConstraint extends Constraint
{

    /**
     * @var callable
     */
    protected $callable;

    /**
     * HasFilterConstraint constructor.
     *
     * @param string|callable $call
     * @param string $method_name
     */
    public function __construct($call, $method_name = null)
    {
        if (empty($call)) {
            throw new \PHPUnit_Framework_Exception( 'Not hook provided for filter assertion' );
        }
        $this->callable = is_null($method_name) ? $call : [$call, $method_name];
    }

    /**
     * @param  string $hook Hook name
     * @return bool
     */
    protected function matches($hook): bool
    {
        return false !== has_filter($hook, $this->callable);
    }

    /**
     * Returns a string representation of the constraint.
     *
     * @return string
     */
    public function toString(): string
    {
        $filter = is_array($this->callable) ?
            sprintf('%s::%s', get_class( $this->callable[0]), $this->callable[1] ) :
            $this->callable;
        return $this->exporter()->export($filter);
    }

    /**
     * @param string $hook
     * @return string
     */
    protected function failureDescription($hook): string
    {
        return "hook '$hook' has filter/action " . $this->toString();
    }
}