<?php
/**
 *
 * Response Interface.
 * @author zhifeng <a_3722@hotmail.com>
 */
namespace BJHaze\Http;

interface ResponseInterface
{

    /**
     * Set the http header
     *
     * @param string $header
     * @return void
     */
    public function setHeader ($key, $values, $replace = true);

    /**
     * Set The response content
     *
     * @return void
     */
    public function setContent ($content);

    /**
     * Get The response content
     *
     * @return void
     */
    public function getContent ();

    /**
     * send content
     * 
     * @return void
     */
    public function send ();
}