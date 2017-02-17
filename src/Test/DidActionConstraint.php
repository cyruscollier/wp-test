<?php 

namespace WPTest\Test;

class DidActionConstraint extends \PHPUnit_Framework_Constraint
{

    /**
     * @var int
     */
    protected $count;

    /**
     * @param numeric $value
     */
    public function __construct( $count = 1 )
    {
        parent::__construct();
        $this->count = $count;
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
        
        return $this->count == did_action( $hook );
    }

    /**
     * Returns a string representation of the constraint.
     *
     * @return string
     */
    public function toString()
    {
        $output = $this->exporter->export($this->count) . ' time';
        if ( $this->count > 1 ) $output .= 's';
        return $output;
    }

    protected function failureDescription($hook)
    {
        return "action '$hook' has been fired " . $this->toString();
    }
}