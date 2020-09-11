<?php


namespace nigiri\utilities;

use nigiri\exceptions\ArgumentNotFoundException;
use nigiri\exceptions\BadArgumentException;

/**
 * Class to validate variables and values coming from GET and POST requests
 * @package nigiri\utilities
 */
abstract class InputValidator
{
    //Request type from where this input is accepted
    const FROM_GET = "GET";
    const FROM_POST = "POST";
    const FROM_COOKIE = "COOKIE";
    const FROM_ANY = "ANY";

    protected $inputName;
    protected $description;
    protected $from;

    /**
     * InputValidator constructor.
     * @param string $name name of the input to check
     * @param string $desc Name of the parameter to be displayed
     * @param string $from where to find the input: GET, POST, COOKIE or ANY (for checking in the $_REQUEST array)
     */
    public function __construct($name, $desc, $from)
    {
        $this->inputName = $name;
        $this->description = $desc;
        $this->from = $from;
    }

    /**
     * @param InputValidator[] $items
     * @return array an array with all the errors that were found
     */
    public static function validateAll($items)
    {
        $errors = [];

        foreach ($items as $i) {
            try {
                $tmp = $i->validate();
                if ($tmp !== true) {
                    $errors[$i->inputName] = $tmp;
                }
            }
            catch(ArgumentNotFoundException $e){
                //If input is not found just jump the check, we assume that if you really want to check that the input exists you use NotEmptyInputValidator
            }
        }

        return $errors;
    }

    /**
     * @return string|bool
     */
    public abstract function validate();

    /**
     * Gets the value of the input from its superglobal
     * @return mixed
     * @throws ArgumentNotFoundException
     * @throws BadArgumentException
     */
    protected function getValue() {
        return $this->getValueOfInput($this->from, $this->inputName);
    }

    /**
     * Gets the value an input from a superglobal
     *
     * @param $from
     * @param $inputName
     * @return mixed
     * @throws ArgumentNotFoundException
     * @throws BadArgumentException
     */
    protected function getValueOfInput($from, $inputName) {
        $superg = null;
        switch (strtoupper($from)) {
            case self::FROM_ANY:
                $superg = &$_REQUEST;
                break;
            case self::FROM_GET:
                $superg = &$_GET;
                break;
            case self::FROM_POST:
                $superg = &$_POST;
                break;
            case self::FROM_COOKIE:
                $superg = &$_COOKIE;
                break;
            default:
                throw new BadArgumentException(l("Bad validation configuration"), 0, "Unknown input source: " . $from);
        }

        do {
            if (array_key_exists($inputName, $superg)) {
                return $superg[$inputName];
            } else {
                if (strpos($inputName, '.') !== false) {
                    $boom = explode('.', $inputName);
                    if (array_key_exists($boom[0], $superg)) {
                        $val = $superg[$boom[0]];
                        if(!is_array($val)){
                            break;
                        }
                        else{
                            $superg = &$superg[$boom[0]];
                            $inputName = substr($inputName, strlen($boom[0]) +1);
                        }
                    }
                    else{
                        break;
                    }
                }
                else{
                    break;
                }
            }
        } while(!empty($inputName));

        throw new ArgumentNotFoundException();
    }

    /**
     * @param $val
     * @throws BadArgumentException
     */
    protected function setValue($val) {
        $superg = null;
        switch (strtoupper($this->from)) {
            case self::FROM_ANY:
                $superg = &$_REQUEST;
                break;
            case self::FROM_GET:
                $superg = &$_GET;
                break;
            case self::FROM_POST:
                $superg = &$_POST;
                break;
            case self::FROM_COOKIE:
                $superg = &$_COOKIE;
                break;
            default:
                throw new BadArgumentException(l("Bad validation configuration"), 0, "Unknown input source: " . $this->from);
        }

        $origSuperG = &$superg;
        $input = $this->inputName;
        do {
            if (array_key_exists($input, $superg)) {
                $superg[$input] = $val;
                return;
            } elseif (strpos($input, '.') !== false) {
                $boom = explode('.', $input);
                if(!array_key_exists($boom[0], $superg) || !is_array($superg[$boom[0]])) {
                    $origSuperG[$this->inputName] = $val;
                    return;
                }
                else{
                    $superg = &$superg[$boom[0]];
                    $input = substr($input, strlen($boom[0]) +1);
                }
            } else {//make new value
                $superg[$input] = $val;
                return;
            }
        } while(!empty($input));
    }
}
