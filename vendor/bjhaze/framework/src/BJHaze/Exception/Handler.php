<?php
/**
 *
 * Exception Handler, use BJHaze\Exception\Template.php.
 * @author zhifeng <a_3722@hotmail.com>
 */
namespace BJHaze\Exception;

class Handler
{

    public static function handleException (\Exception $exception)
    {
        $code = isset(\BJHaze\Http\Response::$statusTexts[$exception->getCode()]) ? $exception->getCode() : 500;
        http_response_code($code);
        if (! defined('BJHAZE_DEBUG') || BJHAZE_DEBUG == false) {
            error_log($exception->getMessage() . " \nfile:" .
                                 $exception->getFile() . " on line " .
                                 $exception->getLine() . ' \nTrace :' .
                                 $exception->getTraceAsString());
            $message = \BJHaze\Http\Response::$statusTexts[$code];
        }
        include __DIR__ . DIRECTORY_SEPARATOR . 'Template.php';
    }

    public static function handleError ($errno, $errstr, $errfile, $errline)
    {
        throw new \ErrorException($errstr, 500, $errno, $errfile, $errline);
    }
}