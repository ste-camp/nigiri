<?php


namespace nigiri\utilities;


class SameAsInputInputValidator extends InputValidator
{
    private $otherFrom;
    private $other;
    private $otherDesc;

    /**
     * SameAsInputInputValidator constructor.
     * @param $name
     * @param $desc
     * @param $from
     * @param $otherInput
     * @param $otherFrom
     * @param $otherDescr
     */
    public function __construct($name, $desc, $from, $otherInput, $otherFrom, $otherDescr)
    {
        parent::__construct($name, $desc, $from);
        $this->other = $otherInput;
        $this->otherFrom = $otherFrom;
        $this->otherDesc = $otherDescr;
    }

    /**
     * @inheritDoc
     * @throws \nigiri\exceptions\ArgumentNotFoundException
     * @throws \nigiri\exceptions\BadArgumentException
     */
    public function validate()
    {
        $val = $this->getValue();
        $otherVal = $this->getValueOfInput($this->otherFrom, $this->other);

        if($val != $otherVal) {
            return l("%s must be the same as %s", $this->description, $this->otherDesc);
        }

        return true;
    }
}