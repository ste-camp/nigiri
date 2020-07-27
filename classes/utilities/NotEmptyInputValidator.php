<?php


namespace nigiri\utilities;


use nigiri\exceptions\ArgumentNotFoundException;
use nigiri\exceptions\BadArgumentException;

/**
 * Checks that the specified input is not empty.
 * If a default value is given, it is assigned to the input if it is found to be empty, without generating any error
 * @package nigiri\utilities
 */
class NotEmptyInputValidator extends InputValidator
{
    /** @var int A particular value used as default for the default parameter, to indicate that there is no default for this input */
    const DEFAULT_EMPTY = -6526135;
    private $defaultValue;

    public function __construct($name, $desc, $from, $default = self::DEFAULT_EMPTY)
    {
        parent::__construct($name, $desc, $from);
        $this->defaultValue = $default;
    }

    /**
     * @inheritDoc
     * @throws BadArgumentException
     */
    public function validate()
    {
        try {
            $v = $this->getValue();
            return !empty($v);
        } catch (ArgumentNotFoundException $e) {
            if ($this->defaultValue != self::DEFAULT_EMPTY) {//Value not found, but we can set a default value for it
                $this->setValue($this->defaultValue);
                return true;
            } else {
                return l("%s cannot be empty", $this->description);
            }
        }
    }

}
