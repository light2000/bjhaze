<?php
/**
 *
 * Router class
 * @author zhifeng <a_3722@hotmail.com>
 */
namespace BJHaze\Routing;

use BJHaze\Foundation\Container;

class Router
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

    /**
     * Application instance
     *
     * @var Container
     */
    protected $container;

    public function __construct(Container $container, $separator = '/', $defaultController = 'home', $defaultAction = 'index')
    {
        $this->container = $container;
        $this->separator = $separator;
        $this->defaultController = $defaultController;
        $this->defaultAction = $defaultAction;
    }

    /**
     * Internal redirect
     *
     * @param string $requestPath            
     * @return mixed
     */
    public function forward($requestPath, $baseUrl = null)
    {
        // response cache hit
        if ($this->container->response->restoreFromCache($requestPath, $baseUrl))
            return;
        $parts = explode($this->separator, trim($requestPath, $this->separator), 3);
        $params = array();
        // path name found in modules
        if (in_array($parts[0], (array) $this->container['modules'])) {
            $this->registerPaths($this->container['modulePath'] . DIRECTORY_SEPARATOR . $parts[0]);
            $controller = isset($parts[1]) ? $parts[1] : $this->defaultController;
            if (isset($parts[2])) {
                $actionParts = explode($this->separator, $parts[2], 2);
                $action = $actionParts[0];
                if (! empty($actionParts[1]))
                    $params = $this->fetchParams($actionParts[1]);
            } else
                $action = $this->defaultAction;
        } else {
            // normal path handle
            $this->registerPaths($this->container['basePath']);
            $controller = ! empty($parts[0]) ? $parts[0] : $this->defaultController;
            $action = isset($parts[1]) ? $parts[1] : $this->defaultAction;
            if (isset($parts[2]))
                $params = $this->fetchParams($parts[2]);
        }
        
        $controller = ucfirst($controller) . 'Controller';
        
        if (! class_exists($controller, true))
            $this->missingAction();
        else
            return (new $controller())->runAction($action, $params, true);
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
        $i = 0;
        $n = sizeof($parts);
        while ($i < $n) {
            $params[$parts[$i]] = isset($parts[$i + 1]) ? $parts[$i + 1] : null;
            $i += 2;
        }
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
        $includePath = '';
        foreach (array(
            'view',
            'controller',
            'model',
            'component',
            'widget'
        ) as $key) {
            if (null === $this->container[$key . 'Path'])
                $this->container[$key . 'Path'] = $basePath . DIRECTORY_SEPARATOR . $key . 's';
            $includePath .= PATH_SEPARATOR . $this->container[$key . 'Path'];
        }
        set_include_path(get_include_path() . $includePath);
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