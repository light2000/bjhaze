<?php
/**
 *
 * Component
 * 
 * @author zhifeng <a_3722@hotmail.com>
 */
namespace BJHaze\Foundation;

use ArrayAccess, Closure, ReflectionClass, ReflectionFunctionAbstract, ReflectionMethod, ReflectionFunction;
use BJHaze\Behavior\BehaviorInterface;

/**
 *
 * @property \BJHaze\Foundation\Application $app
 * @property \BJHaze\Behavior\Cache $cacheProvider
 * @property \BJHaze\Database\Manager $db
 * @property \BJHaze\Cache\CacheManager $cache
 * @property \BJHaze\Encryption\Encrypter $encrypter
 * @property \BJHaze\Http\Request $request
 * @property \BJHaze\Http\Response $response
 * @property \BJHaze\Routing\RegexRouter $router
 * @property \BJHaze\Session\Manager $session
 * @property \BJHaze\Validation\Validator $validator
 */
abstract class Component extends Container
{

    /**
     * Get action before behaviors
     *
     * @param string $action            
     * @param array $parameters            
     * @return array
     */
    abstract public function getBeforeBehaviors($action, array $parameters = null);

    /**
     * Get after action behaviors
     *
     * @param string $action            
     * @param array $parameters            
     * @return array
     */
    abstract public function getAfterBehaviors($action, array $parameters = null);

    /**
     * Run action
     *
     * @param mixed $action            
     * @param array $parameters            
     * @return mixed
     */
    public function runAction($action, array $parameters = array(), $fixParam = false)
    {
        if ($fixParam && ! empty($parameters))
            $parameters = $this->fixActionParameters($action, $parameters);
        if ($action instanceof ReflectionMethod)
            return $action->invokeArgs($this, $parameters);
        elseif ($action instanceof ReflectionFunction)
            return $action->invokeArgs($parameters);
        elseif (is_string($action) && method_exists($this, $action))
            return self::methodInvoke($this, $action, $parameters);
        elseif ($action instanceof Closure)
            return self::anonymousInvoke($action, $parameters);
    }

    /**
     * Fixed action parameters
     *
     * @param mixed $action            
     * @param array $parameters            
     * @return array
     */
    public function fixActionParameters(&$action, array $parameters)
    {
        if (is_string($action) && method_exists($this, $action)) {
            $action = new ReflectionMethod($this, $action);
            return parent::fixParameters($action, $parameters);
        } elseif ($action instanceof Closure) {
            $action = new ReflectionFunction($action);
            return parent::fixParameters($action, $parameters);
        }
    }

    /**
     * Run Behavior
     *
     * @param mixed $behavior            
     * @param mixed $action            
     * @param array $parameters            
     * @param array $before            
     * @param array $after            
     * @param string $result            
     * @throws \LogicException
     * @return void
     */
    public function runBehavior($behavior, &$action, array &$parameters = null, array &$before = null, array &$after = null, &$result = null)
    {
        if ($behavior instanceof BehaviorInterface)
            $behavior->handle($action, $parameters, $before, $after, $result);
        elseif ($behavior instanceof Closure)
            $behavior($action, $parameters, $before, $after, $result);
        else
            throw new \LogicException(sprintf('Behavior: %s is not BJHaze\Behavior\Behavior or Closure', print_r($behavior, true)));
    }

    /**
     * Fetch action result in a behavior chain
     *
     * @param mixed $action            
     * @param array $parameters            
     * @param boolean $fixParam            
     * @return mixed
     */
    public function runActionWithBehavior($action, array $parameters = array(), $fixParam = false)
    {
        $result = null;
        $before = $this->getBeforeBehaviors($action, $parameters);
        $after = $this->getAfterBehaviors($action, $parameters);
        while (null !== ($behavior = array_shift($before))) {
            $this->runBehavior($behavior, $action, $parameters, $before, $after, $result);
        }
        if (null !== $action)
            $result = $this->runAction($action, $parameters, $fixParam);
        while (null !== ($behavior = array_shift($after))) {
            $this->runBehavior($behavior, $action, $parameters, $before, $after, $result);
        }
        
        return $result;
    }

    /**
     * Execute a method
     *
     * @param object $owner            
     * @param string $action            
     * @param array $parameters            
     * @return mixed
     */
    public static function methodInvoke($owner, $action, array $parameters = array())
    {
        if ($action instanceof ReflectionMethod)
            return $action->invokeArgs($owner, $parameters);
        elseif (empty($parameters))
            return $owner->$action();
        else
            return (new ReflectionMethod($owner, $action))->invokeArgs($owner, $parameters);
    }

    /**
     * Execute a anonymous function with parameters
     *
     * @param Closure $func            
     * @param array $parameters            
     * @return mixed
     */
    public static function anonymousInvoke(Closure $func, array $parameters = array())
    {
        if (empty($parameters))
            return $func();
        else
            return (new ReflectionFunction($func))->invokeArgs($parameters);
    }

    /**
     * Invoke string action like controller@action
     *
     * @param string $action            
     * @param array $parameters            
     * @return mixed
     */
    public static function atInvoke($action, array $parameters = array())
    {
        list ($class, $method) = explode('@', $action);
        
        $instance = self::build(array_merge($parameters, array(
            'class' => ucfirst($class)
        )));
        
        if (! $instance instanceof Component) {
            if (! empty($parameters)) {
                $action = new ReflectionMethod($instance, $method);
                $parameters = parent::fixParameters($action, $parameters);
            }
            return self::methodInvoke($instance, $action, $parameters);
        } else
            return $instance->runActionWithBehavior($method, $parameters, true);
    }
}