<?php


namespace nigiri\utilities;


class ArrayInputValidator extends InputValidator
{
    /**
     * @var InputValidator
     */
    private $inner;

    /**
     * ArrayInputValidator constructor.
     * @param string $name
     * @param string $desc
     * @param string $from
     * @param InputValidator $innerValidator
     */
    public function __construct($name, $desc, $from, $innerValidator)
    {
        parent::__construct($name, $desc, $from);
        $this->inner = $innerValidator;
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        $arr = $this->getValue();
        if(is_array($arr)){
            foreach($arr as $k => $val){
                $this->inner->inputName = $this->inputName.".".$k;
                if($valid = $this->inner->validate() !== true){
                    return $valid;
                }
            }

            return true;
        }
        else{
            return l("%s is not an array of values.", $this->description);
        }
    }
}
