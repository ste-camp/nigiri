<?php


namespace nigiri\utilities;


use nigiri\exceptions\ArgumentNotFoundException;
use \nigiri\exceptions\BadArgumentException;

/**
 * Checks that the value of an input matches exactly a fixed value
 * @package nigiri\utilities
 */
class ExactMatchInputValidator extends InputValidator
{
    private $match;

    public function __construct($name, $desc, $from, $match)
    {
        parent::__construct($name, $desc, $from);
        $this->match = $match;
    }

    /**
     * @inheritDoc
     * @throws ArgumentNotFoundException
     * @throws BadArgumentException
     */
    public function validate()
    {
        $val = $this->getValue();

        if($val != $this->match){
            return l("%s must be equal to %s", $this->description, $this->match);
        }

        return true;
    }
}
