<?php
/**
 *
 * Router Interface
 * @author zhifeng <a_3722@hotmail.com>
 */
namespace BJHaze\Routing;
use BJHaze\Http\RequestInterface;

interface RouterInterface
{

    /**
     * Run the Router
     * 
     * @param RequestInterface $request
     * @return void
     */
    public function dispatch (RequestInterface $request);

    /**
     * Internal redirect
     *
     * @param string $requestPath
     * @return array (action , params)
     */
    public function forward ($requestPath);

    /**
     * Build a request url
     *
     * @param string $action
     * @param array $params
     * @return string
     */
    public function buildUrl ($action, array $params);

    /**
     * No action found action
     *
     * @return void
     */
    public function missingAction ();
}
