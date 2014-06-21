<?php
/**
 *
 * Router class
 * @author zhifeng <a_3722@hotmail.com>
 */
namespace BJHaze\Routing;

use BJHaze\Http\RequestInterface;
use BJHaze\Foundation\Container;

class Router extends Container implements RouterInterface
{

    /**
     * Url separator
     *
     * @var string
     */
    protected $separator;

    /**
     * default controller name
     *
     * @var string
     */
    protected $defaultController;

    /**
     * default action name
     *
     * @var string
     */
    protected $defaultAction;

    public function __construct($separator = '/', $defaultController = 'home', $defaultAction = 'index')
    {
        $this->defaultController = $defaultController;
        $this->defaultAction = $defaultAction;
        $this->separator = '/';
        parent::__construct();
    }

    /**
     * (non-PHPdoc)
     *
     * @see \BJHaze\Routing\RouterInterface::forward()
     */
    public function dispatch(RequestInterface $request)
    {
        return $this->forward($request->getPathInfo()) ?  : $this->missingAction();
    }

    /**
     * Internal redirect
     *
     * @param string $requestPath            
     * @return mixed
     */
    public function forward($requestPath, array $params = array())
    {
        $parts = explode($this->separator, trim($requestPath, $this->separator), 3);
        if (in_array($parts[0], (array) $this['modules'])) {
            $this->registerPaths($this['modulePath'] . DIRECTORY_SEPARATOR . $parts[0]);
            $controllerName = isset($parts[1]) ? $parts[1] : $this->defaultController;
            if (isset($parts[2])) {
                $part = explode($this->separator, $parts[2], 2);
                $action = $part[0];
                if (! empty($part[1]))
                    $params = array_merge(self::fetchParams($part[1]), $params);
            } else
                $action = $this->defaultAction;
        } else {
            $this->registerPaths($this['basePath']);
            $controllerName = ! empty($parts[0]) ? $parts[0] : $this->defaultController;
            $action = isset($parts[1]) ? $parts[1] : $this->defaultAction;
            if (isset($parts[2]))
                $params = array_merge(self::fetchParams($parts[2]), $params);
        }
        
        $controllerName = ucfirst($controllerName) . 'Controller';
        $controller = new $controllerName();
        return $controller->runActionWithBehavior($action, $params, true);
    }

    /**
     * Fetch parameters from uri
     *
     * @param string $part            
     */
    public function fetchParams($part)
    {
        $parts = explode($this->separator, $part);
        $params = array();
        foreach ($parts as $key => $value)
            $params[$key] = isset($parts[$key + 1]) ? $parts[$key + 1] : null;
        return $params;
    }

    /**
     * Set application paths
     *
     * @param string $basePath            
     * @return void
     */
    public function registerPaths($basePath)
    {
        foreach (array(
            'view',
            'controller',
            'model',
            'component'
        ) as $key) {
            if (null === $this[$key . 'Path'])
                $this[$key . 'Path'] = $basePath . DIRECTORY_SEPARATOR . $key . 's';
            set_include_path(get_include_path() . PATH_SEPARATOR . $this[$key . 'Path']);
        }
    }

    /**
     * Build url
     *
     * @param string $path            
     * @param array $params            
     * @return string
     */
    public function buildUrl($path, array $params = null)
    {
        $path = trim($path, $this->separator);
        foreach ($params as $key => $value)
            $path .= $this->separator . $key . $this->separator . $value;
        
        return $path;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \BJHaze\Routing\RouterInterface::missingAction()
     */
    public function missingAction()
    {
        throw new \RuntimeException('No matched action found', 404);
    }
}