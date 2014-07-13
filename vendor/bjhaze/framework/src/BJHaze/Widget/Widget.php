<?php
/**
 *
 * Application class file.
 *
 * @author zhifeng <a_3722@hotmail.com>
 */
namespace BJHaze\Widget;

use BJHaze\Foundation\Container;

abstract class Widget extends Container
{

    protected $controller;

    /**
     * Constructor
     *
     * @param array $params            
     */
    public function __construct(array $params = array())
    {
        foreach ($params as $key => $value)
            if (property_exists($this, $key))
                $this->$key = $value;
    }

    abstract public function run();

    /**
     * Renders a view with a layout.
     *
     * @param string $view            
     * @param array $data            
     * @return void
     */
    protected function render($view, array $data = null)
    {
        extract($data);
        include $this->getViewFile($view);
    }

    /**
     * Looks for the view file according to the given view name.
     *
     * @param string $viewName            
     * @return string
     */
    protected function getViewFile($viewName)
    {
        return $this['widgetPath'] . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . $viewName . '.php';
    }
}