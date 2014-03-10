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
 * @property BJHaze\Foundation\Application $app
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
class Component extends Container
{

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
    protected $cacheActions;

    /**
     * Filter behaviors
     *
     * @var array
     */
    protected $filters;

    /**
     * Actions without filter
     *
     * @var array
     */
    protected $filterExcludes = array();

    /**
     * Get action cache and filter behaviors
     *
     * @param string $actionID
     * @param array $parameters
     * @return array
     */
    public function getBeforeBehaviors ($actionID, array $parameters = null)
    {
        $before = array();
        if (! empty($this->filters) && ! in_array($actionID, $this->filterExcludes))
            foreach ($this->filters as $filter)
                $before[] = $this[$filter];
        
        if (isset($this->cacheActions[$actionID])) {
            $this->cacheProvider->setEngine($this->cacheEngine);
            $this->cacheProvider->setKey($actionID . ($parameters ? serialize($parameters) : ''));
            $this->cacheProvider->setSecond($this->cacheActions[$actionID]);
            $before[] = $this->cacheProvider;
        }
        
        return $before;
    }

    /**
     * Get after action behaviors
     *
     * @param string $actionID
     * @param array $parameters
     * @return array
     */
    public function getAfterBehaviors ($actionID, array $parameters = null)
    {
        return array();
    }

    /**
     * Run action
     *
     * @param mixed $action
     * @param array $parameters
     * @return mixed
     */
    public function runAction ($action, array $parameters = null, $fixParam = false)
    {
        if (is_string($action)) {
            if (method_exists($this, $action))
                return self::methodInvoke($this, $action, $parameters, $fixParam);
            elseif (true == strpos($action, '@')) {
                return self::atInvoke($action, $parameters, $fixParam);
            } else
                throw new \LogicException('Action string must like "method" or "classname@method"');
        } elseif ($action instanceof Closure)
            return self::anonymousInvoke($action, $parameters, $fixParam);
        elseif ($action instanceof \ReflectionMethod)
            return $action->invokeArgs($this, $parameters);
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
    public function runBehavior ($behavior, &$action, array &$parameters = null, array &$before = null, 
            array &$after = null, &$result = null)
    {
        if ($behavior instanceof BehaviorInterface)
            $behavior->handle($action, $parameters, $before, $after, $result);
        elseif ($behavior instanceof Closure)
            $behavior($action, $parameters, $before, $after, $result);
        else
            throw new \LogicException(
                    sprintf('Behavior: %s is not BJHaze\Behavior\Behavior or Closure', 
                            print_r($behavior, true)));
    }

    /**
     * Fetch action result in a behavior chain
     *
     * @param mixed $action
     * @param array $parameters
     * @param array $before
     * @param array $after
     */
    public function runActionWithBehavior ($action, array $parameters = array(), array $before = array(), 
            array $after = array(), $fixParam = false)
    {
        $result = null;
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
     * @param string $fixParam
     * @return mixed
     */
    public static function methodInvoke ($owner, $action, array $parameters = null, $fixParam = false)
    {
        if (null === $parameters)
            return $owner->$action();
        else {
            $function = new ReflectionMethod($owner, $action);
            return $function->invokeArgs($owner, 
                    $fixParam ? self::fixParameters($function, $parameters) : $parameters);
        }
    }

    /**
     * Execute a anonymous function with parameters
     *
     * @param Closure $func
     * @param array $parameters
     * @return mixed
     */
    public static function anonymousInvoke (Closure $func, array $parameters = null, $fixParam = false)
    {
        if (null === $parameters)
            return $func();
        else {
            $function = new ReflectionFunction($function);
            return $function->invokeArgs(
                    $fixParam ? self::fixParameters($function, $parameters) : $parameters);
        }
    }

    /**
     * Invoke string action like controller@action
     *
     * @param string $action
     * @param array $parameters
     * @return mixed
     */
    public static function atInvoke ($action, array $parameters = null, $fixParam = true)
    {
        list ($class, $action) = explode('@', $action);
        $class = ucfirst($class);
        if (! class_exists($class, true))
            throw new \RuntimeException(sprintf('class %s not found', $class), 404);
        
        $instance = self::build($class, $parameters);
        
        if (method_exists($instance, 'dispatch'))
            return $instance->dispatch($action, $parameters, $fixParam);
        else
            return self::methodInvoke($instance, $action, $parameters, $fixParam);
    }
}