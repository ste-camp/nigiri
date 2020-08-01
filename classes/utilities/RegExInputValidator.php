<?php


namespace nigiri\utilities;


class RegExInputValidator extends InputValidator
{
    private $pattern;

    public function __construct($name, $desc, $from, $pattern)
    {
        parent::__construct($name, $desc, $from);
        $this->pattern = $pattern;
    }

    public function validate()
    {
        $val = $this->getValue();

        if(preg_match($this->pattern, $val) == 0){
            return l("%s must follow this formatting rule: %s", $this->description, $this->pattern);
        }

        return true;
    }
}
