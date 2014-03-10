<?php
/**
 *
 * Response Class.
 * @author zhifeng <a_3722@hotmail.com>
 */
namespace BJHaze\Http;

class Response implements ResponseInterface
{

    protected $statusCode;

    protected $headers;

    protected $content;

    /**
     * Status codes translation table.
     *
     * The list of codes is complete according to the
     * {@link http://www.iana.org/assignments/http-status-codes/ Hypertext Transfer Protocol (HTTP) Status Code Registry}
     * (last updated 2012-02-13).
     *
     * Unless otherwise noted, the status code is defined in RFC2616.
     *
     * @var array
     */
    public static $statusTexts = array(
            100 => 'Continue',
            101 => 'Switching Protocols',
            102 => 'Processing', // RFC2518
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            207 => 'Multi-Status', // RFC4918
            208 => 'Already Reported', // RFC5842
            226 => 'IM Used', // RFC3229
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            306 => 'Reserved',
            307 => 'Temporary Redirect',
            308 => 'Permanent Redirect', // RFC-reschke-http-status-308-07
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            418 => 'I\'m a teapot', // RFC2324
            422 => 'Unprocessable Entity', // RFC4918
            423 => 'Locked', // RFC4918
            424 => 'Failed Dependency', // RFC4918
            425 => 'Reserved for WebDAV advanced collections expired proposal', // RFC2817
            426 => 'Upgrade Required', // RFC2817
            428 => 'Precondition Required', // RFC6585
            429 => 'Too Many Requests', // RFC6585
            431 => 'Request Header Fields Too Large', // RFC6585
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',
            506 => 'Variant Also Negotiates (Experimental)', // RFC2295
            507 => 'Insufficient Storage', // RFC4918
            508 => 'Loop Detected', // RFC5842
            510 => 'Not Extended', // RFC2774
            511 => 'Network Authentication Required' // RFC6585
        );

    /**
     * Set the http header
     *
     * @param string $header
     * @return void
     */
    public function setHeader ($key, $values, $replace = true)
    {
        if (true === $replace || ! isset($this->headers[$key])) {
            $this->headers[$key] = $values;
        } else {
            $this->headers[$key] = array_merge($this->headers[$key], $values);
        }
    }

    /**
     * Set The response content
     *
     * @return void
     */
    public function setContent ($content)
    {
        $this->content = $content;
    }

    /**
     * Get The response content
     *
     * @return void
     */
    public function getContent ()
    {
        return $this->content;
    }

    /**
     * Send http response header
     *
     * @return void
     */
    public function sendHeaders ()
    {
        if (headers_sent())
            return;
        http_response_code($this->statusCode);
        foreach ((array) $this->headers as $name => $value) {
            header($name . ': ' . $value, false);
        }
    }

    /**
     * Send Content
     *
     * @return void
     */
    public function sendContent ()
    {
        echo $this->content;
    }

    /**
     * Sends HTTP headers and content.
     *
     * @return void
     */
    public function send ()
    {
        $this->sendHeaders();
        $this->sendContent();
        
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        } elseif ('cli' !== PHP_SAPI && ob_get_level() > 0) {
            flush();
        }
    }
}