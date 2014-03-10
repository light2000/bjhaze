<?php
/**
 *
 * Exception Handler, default use BJHaze\Exception\Template.php.
 * @author zhifeng <a_3722@hotmail.com>
 */
namespace BJHaze\Exception;

class Handler
{

    public function handleException (\Exception $exception)
    {
        $code = $exception->getCode() > 100 ? $exception->getCode() : 500;
        http_response_code($code);
        if (! defined('BJHAZE_DEBUG'))
            $message = \BJHaze\Http\Response::$statusTexts[$code];
        include __DIR__ . DIRECTORY_SEPARATOR . 'Template.php';
    }

    public function handleError ($errno, $errstr, $errfile, $errline)
    {
        throw new \ErrorException($errstr, 500, $errno, $errfile, $errline);
    }
}