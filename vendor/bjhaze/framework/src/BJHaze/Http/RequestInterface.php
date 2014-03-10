<?php
/**
 *
 * Request Interface.
 * @author zhifeng <a_3722@hotmail.com>
 */
namespace BJHaze\Http;

interface RequestInterface
{

    /**
     * Returns the request Base URL.
     *
     * @param boolean Whether return a absolute url.
     * @return string
     */
    public function getBaseUrl ($absolute = false);

    /**
     * Returns the path info of the currently requested URL
     *
     * @return string
     */
    public function getPathInfo ();

    /**
     * Returns the request send parameters.
     *
     * @return array
     */
    public function getParams ();

    /**
     * Returns the user agent, null if not present.
     */
    public function getUserAgent ();

    /**
     * Returns the request type, such as GET, POST, HEAD, PUT, DELETE.
     */
    public function getRequestType ();

    /**
     * Redirects the browser to the specified URL.
     *
     * @param string $url
     */
    public function redirect ($url);
}