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
     * Binding items.(key => value)
     *
     * @var array
     */
    protected static $bindings;

    /**
     * Late binding items.(key => class configure array)
     *
     * @var array
     */
    protected static $lateBindings = array();

    /**
     * Add late binding items
     *
     * @param string $key            
     * @param mixed $value            
     * @return void
     */
    public function bind($key, $value)
    {
        if (is_string($value))
            $value = array(
                'class' => $value
            );
        static::$lateBindings[$key] = $value;
    }

    /**
     * Get binding item by key, if key found in late bindings build a new item instance
     *
     * @param string $key            
     * @return mixed
     */
    public static function getBinding($key)
    {
        if (isset(static::$bindings[$key]))
            return static::$bindings[$key];
        elseif (! empty(static::$lateBindings[$key]))
            return static::$bindings[$key] = is_array(static::$lateBindings[$key]) && isset(static::$lateBindings[$key]['class']) ? static::build(static::$lateBindings[$key]) : static::$lateBindings[$key];
    }

    /**
     * Build a new class instance
     *
     * @param array $class            
     * @return object
     */
    public static function build(array $class)
    {
        if (! isset($class['class']))
            throw new \LogicException('Item config array must have a "class" key');
        
        $className = (string) $class['class'];
        
        if (! class_exists($className, true))
            throw new \RuntimeException(sprintf('Class %s not found', $class), 404);
        
        if (sizeof($class) > 1) {
            $reflector = new ReflectionClass($className);
            $constructor = $reflector->getConstructor();
            
            if (is_null($constructor))
                return new $className();
            else
                return $reflector->newInstanceArgs(self::fixParameters($constructor, $class));
        } else
            return new $className();
    }

    /**
     * Fill the function with right parameters
     *
     * @param ReflectionFunctionAbstract $function            
     * @param array $parameters            
     * @throws \InvalidArgumentException
     * @return array
     */
    public static function fixParameters(ReflectionFunctionAbstract $function, array $parameters)
    {
        $params = array();
        foreach ($function->getParameters() as $parameter) {
            $name = $parameter->getName();
            if ($parameters && isset($parameters[$name]))
                $params[] = $parameters[$name];
            elseif ($parameter->isDefaultValueAvailable())
                $params[] = $parameter->getDefaultValue();
            elseif (array_key_exists($name, static::$lateBindings)) {
                $class = is_array(static::$lateBindings[$name]) ? static::$lateBindings[$name]['class'] : static::$lateBindings[$name];
                if (is_a($class, $parameter->getClass()->name, true))
                    $params[] = static::getBinding($name);
            } else
                throw new \InvalidArgumentException(sprintf('Parameter $%s in %s not found', $name, $function->getName()));
        }
        
        return $params;
    }

    /**
     * Get the value at a given offset.
     *
     * @param string $key            
     * @return mixed
     */
    public function offsetGet($key)
    {
        return static::getBinding($key);
    }

    /**
     * Set the value at a given offset.
     *
     * @param string $key            
     * @param mixed $value            
     * @return void
     */
    public function offsetSet($key, $value)
    {
        static::$bindings[$key] = $value;
    }

    /**
     * Unset the value at a given offset.
     *
     * @param string $key            
     * @return void
     */
    public function offsetUnset($key)
    {
        unset(static::$bindings[$key], static::$lateBindings[$key]);
    }

    /**
     * Determine if a given offset exists.
     *
     * @param string $key            
     * @return bool
     */
    public function offsetExists($key)
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
    public function __set($name, $value)
    {
        return $this->offsetSet($name, $value);
    }

    /**
     * Dynamically access container property.
     *
     * @param string $name            
     * @return mixed
     */
    public function __get($name)
    {
        return $this->offsetGet($name);
    }
}