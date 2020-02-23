<?php


namespace nigiri\utilities;

/**
 * Checks that an input is a syntactically valid email address (does NOT check its existence or the one of its domain)
 * @package nigiri\utilities
 */
class EmailInputValidator extends InputValidator
{

    /**
     * @inheritDoc
     */
    public function validate()
    {
        $val = $this->getValue();

        if(filter_var($val, FILTER_VALIDATE_EMAIL) == ''){
            return l("%s must be a valid email address", $this->description);
        }
        return true;
    }
}