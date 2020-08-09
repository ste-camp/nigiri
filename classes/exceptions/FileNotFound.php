<?php

namespace nigiri\exceptions;

/**
 * Represents an HTTP 404 error
 * @package site\exceptions
 */
class FileNotFound extends HttpException
{
    public function __construct($str = "", $detail = "")
    {
        $this->theme = [
            self::DEFAULT_THEME_KEY => ':' . dirname(__DIR__) . '/views/http404.php',
          'nigiri\\themes\\AjaxTheme:ajax_exception'
        ];

        if (empty($str)) {
            $str = 'La pagina richiesta non esiste';
        }
        $this->httpString = 'Not Found';

        parent::__construct($str, 404, $detail);
    }
}
