<?php
/**
 *
 * Container
 * 
 * @author zhifeng <a_3722@hotmail.com>
 */
namespace BJHaze\Foundation;
use ArrayAccess, ReflectionClass, ReflectionFunctionAbstract;

class Container implements ArrayAccess
{

    /**
     * Binding items.(key => object)
     *
     * @var array
     */
    protected static $bindings;

    /**
     * Late binding items.(key => classname)
     *
     * @var array
     */
    protected static $lateBindings = array();

    /**
     * Register Late binding item
     *
     * @param array $items
     * @return void
     */
    public function bind ($key, $item)
    {
        static::$lateBindings[$key] = $item;
    }

    /**
     * Reset late binding items
     *
     * @param array $items
     * @return void
     */
    public function resetLateBindings (array $items)
    {
        static::$lateBindings = $items;
    }

    /**
     * Build a new class instance
     *
     * @param mixed $class
     * @param array $parameters
     * @param string $id
     */
    public static function build ($class, array $parameters = null)
    {
        if (is_array($class)) {
            if (! isset($class['class']))
                throw new \LogicException('Item config array must have a "class" key');
            $parameters = $class;
            $class = (string) $class['class'];
        }
        
        if (! empty($parameters)) {
            $reflector = new ReflectionClass($class);
            $constructor = $reflector->getConstructor();
            
            if (is_null($constructor))
                return new $class();
            else
                return $reflector->newInstanceArgs(self::fixParameters($constructor, $parameters));
        } else
            return new $class();
    }

    /**
     * Fill the function with right parameters
     *
     * @param ReflectionFunctionAbstract $function
     * @param array $parameters
     * @throws \InvalidArgumentException
     * @return array
     */
    public static function fixParameters (ReflectionFunctionAbstract $function, 
            array $parameters = null)
    {
        $params = [];
        foreach ($function->getParameters() as $parameter) {
            $name = $parameter->getName();
            if ($parameters && array_key_exists($name, $parameters))
                $params[] = $parameters[$name];
            elseif ($parameter->isDefaultValueAvailable())
                $params[] = $parameter->getDefaultValue();
            elseif (array_key_exists($name, static::$lateBindings)) {
                $binding = is_array(static::$lateBindings[$name]) ? static::$lateBindings[$name]['class'] : static::$lateBindings[$name];
                if ($binding == $parameter->getClass()->name ||
                         is_a($binding, $parameter->getClass()->name, true)) {
                    $params[] = isset(static::$bindings[$name]) ? static::$bindings[$name] : static::$bindings[$name] = static::build(
                            static::$lateBindings[$name]);
                }
            } else
                throw new \InvalidArgumentException(
                        sprintf('Parameter $%s in %s not found', $name, $function->getName()));
        }
        
        return $params;
    }

    /**
     * Get the value at a given offset.
     * if key found in components load build
     *
     * @param string $key
     * @return mixed
     */
    public function offsetGet ($key)
    {
        if (isset(static::$bindings[$key]))
            return static::$bindings[$key];
        elseif (! empty(static::$lateBindings[$key]))
            return static::$bindings[$key] = static::build(static::$lateBindings[$key]);
    }

    /**
     * Set the value at a given offset.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function offsetSet ($key, $value)
    {
        static::$bindings[$key] = $value;
    }

    /**
     * Unset the value at a given offset.
     *
     * @param string $key
     * @return void
     */
    public function offsetUnset ($key)
    {
        unset(static::$bindings[$key], static::$lateBindings[$key]);
    }

    /**
     * Determine if a given offset exists.
     *
     * @param string $key
     * @return bool
     */
    public function offsetExists ($key)
    {
        return isset(static::$bindings[$key]) || isset(static::$lateBindings[$key]);
    }

    /**
     * Dynamically set container property.
     *
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function __set ($name, $value)
    {
        return $this->offsetSet($name, $value);
    }

    /**
     * Dynamically access container property.
     *
     * @param string $name
     * @return mixed
     */
    public function __get ($name)
    {
        return $this->offsetGet($name);
    }
}