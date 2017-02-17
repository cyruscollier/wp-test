<?php 

namespace WPTest\Test;

class HasFilterConstraint extends \PHPUnit_Framework_Constraint
{
    
    /**
     * @var callable
     */
    protected $callable;

    /**
     * @param numeric $value
     */
    public function __construct( $call, $method_name = null)
    {
        parent::__construct();
        if ( empty( $call ) ) throw new \PHPUnit_Framework_Exception( 'Not hook provided for filter assertion' );
        $this->callable = is_null($method_name) ? $call : array( $call, $method_name );
    }

    /**
     * Evaluates the constraint for parameter $other. Returns true if the
     * constraint is met, false otherwise.
     *
     * @param  mixed $other Value or object to evaluate.
     * @return bool
     */
    protected function matches($hook)
    {
        return false !== has_filter( $hook, $this->callable );
    }

    /**
     * Returns a string representation of the constraint.
     *
     * @return string
     */
    public function toString()
    {
        $filter = is_array( $this->callable ) ? sprintf( '%s::%s', get_class( $this->callable[0] ), $this->callable[1] ) : $this->callable;
        return $this->exporter->export($filter);
    }
    
    protected function failureDescription($hook)
    {
        return "hook '$hook' has filter/action " . $this->toString();
    }
}