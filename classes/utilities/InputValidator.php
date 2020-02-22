<?php


namespace nigiri\utilities;

/**
 * Class to validate variables and values coming from GET and POST requests
 * @package nigiri\utilities
 */
class InputValidator
{
    private $config;

    const RULE_NOT_EMPTY = 1;
    const RULE_NUMERIC = 2;

    public function __construct($conf)
    {
        $this->config = $conf;
    }

    public function validate(){
        $errors = [];

        foreach($this->config as $field => $c){
            switch($c['rule']){
                case self::RULE_NOT_EMPTY:
                    $this->validateNotEmpty($field, $c);
                    break;
                case self::RULE_NUMERIC:
                    $this->validateNumeric($field, $c);
            }
        }
    }

    private function validateNotEmpty($field, $config){
        //todo
    }

    private function validateNumeric($field, $config){
        //todo
    }
}