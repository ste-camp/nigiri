<?php

namespace nigiri\exceptions;

/**
 * Represents an HTTP 400 error
 * @package site\exceptions
 */
class BadRequest extends HttpException
{
    public function __construct($str = "", $detail = "")
    {
        $this->theme = [
           self::DEFAULT_THEME_KEY => ':' . dirname(__DIR__) . '/views/http400.php',
          'nigiri\\themes\\AjaxTheme:ajax_exception'
        ];

        if (empty($str)) {
            $str = 'I dati inviati sono incorretti';
        }
        $this->httpString = 'Bad Request';

        parent::__construct($str, 400, $detail);
    }
}
