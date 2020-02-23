<?php


namespace nigiri\utilities;

/**
 * Checks that the string input is within a range of allowed lengths
 * @package nigiri\utilities
 */
class LengthInputValidator extends InputValidator
{
    private $min;
    private $max;

    public function __construct($name, $desc, $from, $min = null, $max = null)
    {
        parent::__construct($name, $desc, $from);
        $this->min = $min;
        $this->max = $max;
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        $val = $this->getValue();
        $len = strlen($val);

        if($this->min !== null and $len < $this->min){
            return l("%s must be longer than %d characters", $this->description, $this->min);
        }

        if($this->max !== null and $len > $this->max){
            return l("%s must be shorter than %d characters", $this->description, $this->min);
        }

        return true;
    }
}