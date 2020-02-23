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

    public function __construct($name, $desc, $from, $validation_mask = 0x0)
    {
        parent::__construct($name, $desc, $from);
        $this->mask = $validation_mask;
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

        if($this->mask & self::NUMBER_POSITIVE != 0x0 && $castVal < 0) {
            return l("%s must be positive", $this->description);
        }

        if($this->mask & self::NUMBER_NEGATIVE != 0x0 && $castVal >= 0) {
            return l("%s must be negative", $this->description);
        }

        $this->setValue($castVal);

        return true;
    }
}