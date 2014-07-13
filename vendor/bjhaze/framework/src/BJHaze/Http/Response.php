<?php
/**
 *
 * Response Class.
 * @author zhifeng <a_3722@hotmail.com>
 */
namespace BJHaze\Http;

use BJHaze\Cache\CacheManager;

class Response
{

    /**
     * Response status code
     *
     * @var int
     */
    protected $statusCode;

    /**
     *
     * @var string
     */
    protected $charset;

    /**
     * Response headers
     *
     * @var array
     */
    protected $headers;

    /**
     * Response content
     *
     * @var string
     */
    protected $content;

    /**
     * CacheManager instance
     *
     * @var CacheManager
     */
    protected $cache;

    /**
     * Cache path info
     *
     * @var array
     */
    protected $cachePaths;

    /**
     * Response cache uri
     *
     * @var string
     */
    protected $cacheUri;

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
     * Constructor
     *
     * @param string $charset            
     */
    public function __construct($charset = 'utf8', CacheManager $cache, $cachePaths = array())
    {
        $this->charset = $charset;
        $this->cache = $cache;
        $this->cachePaths = $cachePaths;
        $this->setHeader('Content-Type', 'charset=' . $charset);
    }

    /**
     * get response from cache
     *
     * @param string $uri            
     * @param string $baseUrl            
     * @return boolean
     */
    public function restoreFromCache($uri, $baseUrl = '')
    {
        foreach ($this->cachePaths as $path => $exprie) {
            if (0 === strpos($path, 'http') && 0 === strpos($baseUrl . $uri, $path) || 0 === strpos($uri, $path)) {
                $response = $this->cache->get($path);
                if (! empty($response)) {
                    $this->headers = $response['headers'];
                    $this->content = $response['content'];
                    $this->cacheUri = null;
                    return true;
                } else
                    $this->cacheUri = $uri;
            }
        }
        
        return false;
    }

    /**
     * Get the response charset
     *
     * @return string
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * Set the http header
     *
     * @param string $header            
     * @return void
     */
    public function setHeader($key, $values, $replace = false)
    {
        if (true === $replace || ! isset($this->headers[$key]))
            $this->headers[$key] = $values;
        else {
            if (! is_array($this->headers[$key]))
                $this->headers[$key] = array(
                    $this->headers[$key]
                );
            if (! in_array($values, $this->headers[$key]))
                array_unshift($this->headers[$key], $values);
        }
    }

    /**
     * Set response http status
     *
     * @param int $status            
     * @return void
     */
    public function setStatus($statusCode)
    {
        $this->statusCode = $statusCode;
    }

    /**
     * Set The response content
     *
     * @param string $content            
     * @return void
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * Append content
     *
     * @param string $content            
     * @return void
     */
    public function appendContent($content)
    {
        $this->content .= $content;
    }

    /**
     * Get The response content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Send http response header
     *
     * @return void
     */
    public function sendHeaders()
    {
        if (headers_sent())
            return;
        http_response_code($this->statusCode);
        
        foreach ((array) $this->headers as $name => $value)
            header($name . ': ' . (is_array($value) ? implode('; ', $value) : $value), false);
    }

    /**
     * Send Content
     *
     * @return void
     */
    public function sendContent()
    {
        echo $this->content;
    }

    /**
     * Sends HTTP headers and content.
     *
     * @return void
     */
    public function send()
    {
        $this->beforeSend();
        $this->sendHeaders();
        $this->sendContent();
        
        if (function_exists('fastcgi_finish_request'))
            fastcgi_finish_request();
        elseif ('cli' !== PHP_SAPI && ob_get_level() > 0)
            flush();
        
        if (! empty($this->cacheUri))
            $this->cache->set($this->cacheUri, array(
                'headers' => $this->headers,
                'content' => $this->content
            ));
        
        $this->afterSend();
    }

    /**
     * startRun callback queue
     *
     * @var SplPriorityQueue
     */
    protected $startCallbackQueue;

    /**
     * finishRun callback queue
     *
     * @var SplPriorityQueue
     */
    protected $finishCallbackQueue;

    /**
     * Register a "before response" callback.
     *
     * @return void
     */
    public function beforeSend(Closure $callback = null, $priority = 1)
    {
        if (null !== $callback) {
            if (null === $this->startCallbackQueue)
                $this->startCallbackQueue = new SplPriorityQueue();
            $this->startCallbackQueue->insert($callback, $priority);
        } elseif (null !== $this->startCallbackQueue)
            foreach ($this->startCallbackQueue as $start)
                $start();
    }

    /**
     * Register a "after response" callback.
     *
     * @param Closure $callback            
     * @return void
     */
    public function afterSend(Closure $callback = null, $priority = 1)
    {
        if (null !== $callback) {
            if (null === $this->finishCallbackQueue)
                $this->finishCallbackQueue = new SplPriorityQueue();
            $this->finishCallbackQueue->insert($callback, $priority);
        } elseif (null !== $this->finishCallbackQueue)
            foreach ($this->finishCallbackQueue as $finish)
                $finish();
    }
}