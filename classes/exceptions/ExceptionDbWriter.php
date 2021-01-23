<?php


namespace nigiri\exceptions;

/**
 * Interface to be used by Exception to log errors into the db
 * @package nigiri\exceptions
 */
interface ExceptionDbWriter
{
    /**
     * @param string $msg
     * @param Exception $e
     * @return mixed
     */
    public function logException($msg, $e);
}
