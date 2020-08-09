<?php

namespace nigiri\exceptions;

/**
 * Represents an HTTP 403 error
 * @package site\exceptions
 */
class Forbidden extends HttpException
{
    public function __construct($str = "", $detail = "")
    {
        $this->theme = [
            self::DEFAULT_THEME_KEY => ':' . dirname(__DIR__) . '/views/http403.php',
            'nigiri\\themes\\AjaxTheme:ajax_exception'
        ];

        if (empty($str)) {
            $str = 'Non hai i permessi per accedere a questa pagina';
        }
        $this->httpString = 'Forbidden';

        parent::__construct($str, 403, $detail);
    }
}
