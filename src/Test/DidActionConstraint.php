<?php 

namespace WPTest\Test;

use PHPUnit\Framework\Constraint\Constraint;

class DidActionConstraint extends Constraint
{

    /**
     * @var int
     */
    protected $count;

    /**
     * DidActionConstraint constructor.
     *
     * @param int $count
     */
    public function __construct($count = 1)
    {
        parent::__construct();
        $this->count = $count;
    }

    /**
     * @param  string $hook Hook name
     * @return bool
     */
    protected function matches($hook): bool
    {
        
        return $this->count == did_action($hook);
    }

    /**
     * Returns a string representation of the constraint.
     *
     * @return string
     */
    public function toString(): string
    {
        $output = $this->exporter->export($this->count) . ' time';
        if ($this->count > 1) {
            $output .= 's';
        }
        return $output;
    }

    /**
     * @param string $hook
     * @return string
     */
    protected function failureDescription($hook): string
    {
        return "action '$hook' has been fired " . $this->toString();
    }
}