<?php

/**
 *
 * Controller class
 * @author zhifeng <a_3722@hotmail.com>
 */
namespace BJHaze\Routing;

use ArrayAccess, Closure, ReflectionClass, ReflectionFunctionAbstract, ReflectionMethod, ReflectionFunction;
use BJHaze\Foundation\Container;

class Controller extends Container
{

    /**
     * This id will influence the class view path
     *
     * @var string
     */
    protected $id;

    /**
     * The view layout file, default in app view path.
     *
     * @var string
     */
    protected $layout;

    /**
     * The cache engine used.
     *
     * @var string
     */
    protected $cacheEngine;

    /**
     * Cache actions and their cache time(second)
     *
     * @var int
     */
    protected $cacheActions = array();

    /**
     * Get the default action
     *
     * @return string
     */
    public function getDefaultAction()
    {
        return 'index';
    }

    /**
     * Returns the request parameters that will be used for action parameter
     * binding.
     *
     * @return array
     */
    protected function getActionParams()
    {
        return $this->request->getParams();
    }

    /**
     *
     * @param string $action            
     */
    public function beforeAction($actionID)
    {}

    /**
     *
     * @param string $actionID            
     */
    public function afterAction($actionID)
    {}

    /**
     * (non-PHPdoc)
     *
     * @see \BJHaze\Foundation\Component::runAction()
     */
    public function runAction($actionID, array $parameters = array(), $fixParam = false)
    {
        if ('' == ($actionID = trim($actionID, '/')))
            $actionID = $this->getDefaultAction();
        $action = 'action' . $actionID;
        if (method_exists($this, $action)) {
            $this->beforeAction($actionID);
            
            $function = new ReflectionMethod($this, $action);
            if ($fixParam)
                $parameters = parent::fixParameters($function, array_merge($this->getActionParams(), $parameters));
            $result = $function->invokeArgs($this, $parameters);
            
            $this->afterAction($actionID);
            return $result;
        } else
            return $this->missingAction($actionID);
    }

    /**
     * Render a widget
     *
     * @param string $widget            
     * @param array $parameters            
     */
    protected function widget($widget, array $parameters = array())
    {
        $parameters['controller'] = $this;
        (new $widget($parameters))->run();
    }

    /**
     * Processes the request using another action.
     *
     * @param string $path
     *            the new route.
     */
    protected function forward($path)
    {
        return $this->router->forward($path);
    }

    /**
     * Handles the request whose action is not recognized.
     *
     * @param string $actionID            
     * @throws BadMethodCallException
     */
    protected function missingAction($actionID)
    {
        throw new \BadMethodCallException(sprintf('The system is unable to find the action "%s".', $actionID), 404);
    }

    /**
     * This method is invoked at the beginning of {@link render()}.
     *
     * @param string $view            
     * @param array $data            
     */
    protected function beforeRender($view, array &$data = null)
    {}

    /**
     * This method is invoked after the specified view is rendered by calling
     * {@link render()}.
     */
    protected function afterRender()
    {}

    /**
     * Renders JOSN data.
     *
     * @param array $data            
     */
    protected function renderJSON(array $data)
    {
        $this->response->setHeader('Content-Type', 'charset=utf8', true);
        $this->response->setHeader('Content-Type', 'application/json');
        
        $charset = $this->response->getCharset();
        if ($charset != 'utf8')
            array_walk_recursive($data, function (&$item, $key) use ($charset)
            {
                $item = mb_convert_encoding($item, 'utf8', $charset);
            });
        
        $this->response->setContent(json_encode($data));
    }

    /**
     * Renders XML data.
     *
     * @param array $data            
     */
    protected function renderXML($data)
    {
        $this->response->setHeader('Content-Type', 'text/xml');
        $this->response->setContent(is_array($data) ? $this->writeXML($data, null, $this->response->getCharset()) : $data);
    }

    /**
     * Renders a view with a layout.
     *
     * @param string $view            
     * @param array $data            
     * @return void
     */
    protected function render($view, array $data = array())
    {
        $this->beforeRender($view, $data);
        
        $viewFile = $this->getViewFile($view);
        extract($data);
        $this->response->setHeader('Content-Type', 'text/html');
        ob_start();
        if (($layoutFile = $this->getLayoutFile()) != null)
            include $layoutFile;
        else
            include $viewFile;
        $content = ob_get_clean();
        // ob_end_clean();
        $this->response->setContent($content);
        
        $this->afterRender();
    }

    /**
     * XML writer
     *
     * @param array $data            
     * @param \XMLWriter $writer            
     */
    protected function writeXML(array $data,\XMLWriter $writer = null, $charset = 'utf-8')
    {
        if (null == $writer) {
            $writer = new \XMLWriter();
            $writer->openMemory();
            $writer->startDocument('1.0', $charset);
            $writer->startElement('response');
            $this->writeXML($data, $writer);
            $writer->endElement();
            $writer->endDocument();
            return $writer->flush();
        } else
            foreach ($data as $key => $value) {
                $writer->startElement(is_int($key) ? 'data' : $key);
                if (is_array($value))
                    $this->writeXML($value, $writer);
                else
                    $writer->text($value);
                $writer->endElement();
            }
    }

    /**
     * Looks for the view file according to the given view name.
     *
     * @param string $viewName            
     * @return string
     */
    protected function getViewFile($viewName)
    {
        if ($viewName[0] === '/')
            return $this['viewPath'] . DIRECTORY_SEPARATOR . $viewName . '.php';
        else
            return $this->getViewPath() . DIRECTORY_SEPARATOR . $viewName . '.php';
    }

    /**
     * Returns the directory containing view files for this controller.
     *
     * @return string
     */
    protected function getViewPath()
    {
        return $this['viewPath'] . DIRECTORY_SEPARATOR . $this->getId();
    }

    /**
     * Returns the controller ID.
     *
     * @return string
     */
    protected function getId()
    {
        if (empty($this->id)) {
            $id = true == ($pos = strrpos(get_class($this), '\\')) ? substr(get_class($this), $pos + 1) : get_class($this);
            $this->id = str_replace('controller', '', strtolower($id));
        }
        
        return $this->id;
    }

    /**
     * Looks for the layout view script based on the layout name.
     *
     * @return string the view file for the layout. null if the view file
     *         cannot be found
     */
    protected function getLayoutFile()
    {
        if (null !== $this->layout)
            return $this['viewPath'] . DIRECTORY_SEPARATOR . $this->layout . '.php';
    }

    /**
     * Creates a relative URL.
     *
     * @param string $route            
     * @param array $params            
     * @return string
     */
    protected function createUrl($route, array $params = array())
    {
        $url = $this->router->buildUrl($route, $params);
        if (strpos($url, 'http') === 0)
            return preg_replace('/http(s?):\/\/[^\/]+/is', '', $url);
        else
            return $url;
    }

    /**
     * Creates an absolute URL.
     *
     * @param string $route            
     * @param array $params            
     * @return string
     */
    protected function createAbsoluteUrl($route, $params = array())
    {
        $url = $this->router->buildUrl($route, $params);
        if (strpos($url, 'http') === 0)
            return $url;
        else
            return $this->request->getBaseUrl(true) . $url;
    }

    /**
     * Redirects the browser to the specified URL.
     *
     * @param string $url
     *            the URL to be redirected to.
     * @param boolean $terminate
     *            whether to terminate the current application.
     * @param integer $statusCode
     *            the HTTP status code.
     */
    public function redirect($url, $terminate = true, $statusCode = 302)
    {
        $this->request->redirect($url, $terminate, $statusCode);
    }
}
