<?php


namespace nigiri\utilities;


use nigiri\exceptions\BadArgumentException;
use nigiri\exceptions\ArgumentNotFoundException;

class NumericInputValidator extends InputValidator
{
    /** @var int checks that the number is positive */
    const NUMBER_POSITIVE = 0x1;

    /** @var int checks that the number is negative */
    const NUMBER_NEGATIVE = 0x2;

    /** @var int casts the number to a float instead of an int */
    const NUMBER_FLOAT = 0x4;

    private $mask;
    private $min;
    private $max;

    public function __construct($name, $desc, $from, $validation_mask = 0x0, $minValue = null, $maxValue = null)
    {
        parent::__construct($name, $desc, $from);
        $this->mask = $validation_mask;
        $this->min = $minValue;
        $this->max = $maxValue;
    }

    /**
     * @inheritDoc
     * @throws BadArgumentException
     * @throws ArgumentNotFoundException
     */
    public function validate()
    {
        $val = $this->getValue();

        if(!is_numeric($val)) {
            return l("%s must be a number", $this->description);
        }

        $castVal = (int)$val;
        if($this->mask & self::NUMBER_FLOAT != 0x0) {
            $castVal = (float) $val;
        }

        if($this->mask & self::NUMBER_POSITIVE != 0x0 and $castVal < 0) {
            return l("%s must be positive", $this->description);
        }

        if($this->mask & self::NUMBER_NEGATIVE != 0x0 and $castVal >= 0) {
            return l("%s must be negative", $this->description);
        }

        if($this->min !== null and $castVal < $this->min) {
            return l("%s must be higher than %d", $this->description, $this->min);
        }
        if($this->max !== null and $castVal > $this->max) {
            return l("%s must be lower than %d", $this->description, $this->max);
        }

        $this->setValue($castVal);

        return true;
    }
}