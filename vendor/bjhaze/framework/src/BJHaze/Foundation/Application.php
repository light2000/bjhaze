<?php
/**
 *
 * Application class file.
 *
 * @author zhifeng <a_3722@hotmail.com>
 */
namespace BJHaze\Foundation;

use Closure, SplPriorityQueue;
use BJHaze\Http\RequestInterface;
use BJHaze\Routing\RouterInterface;

class Application extends Container
{

    /**
     * startRun callback queue
     *
     * @var SplPriorityQueue
     */
    protected $startCallbackQueue;

    /**
     * finishRun callback queue
     *
     * @var SplPriorityQueue
     */
    protected $finishCallbackQueue;

    /**
     * Constructor
     *
     * @param array $config            
     */
    public function __construct(array $config)
    {
        $components = array(
            'request' => array(
                'class' => 'BJHaze\Http\Request'
            ),
            'validator' => array(
                'class' => 'BJHaze\Validation\Validator'
            ),
            'cacheProvider' => array(
                'class' => 'BJHaze\Behavior\Cache'
            ),
            'exceptionHandler' => array(
                'class' => 'BJHaze\Exception\Handler'
            ),
            'response' => array(
                'class' => 'BJHaze\Http\Response'
            ),
            'sessionHandler' => array(
                'class' => 'BJHaze\Session\Handler\File'
            ),
            'session' => array(
                'class' => 'BJHaze\Session\Manager',
                'sessionHandler' => null
            ),
            'router' => array(
                'class' => 'BJHaze\Routing\RegexRouter'
            ),
            'encrypter' => array(
                'class' => 'BJHaze\Encryption\Encrypter'
            ),
            'db' => array(
                'class' => 'BJHaze\Database\Manager'
            ),
            'cache' => array(
                'class' => 'BJHaze\Cache\CacheManager'
            )
        );
        
        static::$lateBindings = array_replace_recursive($components, $config);
        
        $this['app'] = $this;
        
        $this->registerPaths($config);
        
        $this->initExceptionHandler();
        
        if (false == $config['composer'])
            $this->registerIncludePath();
        
        if (! empty($config['timezone']))
            date_default_timezone_set($config['timezone']);
    }

    /**
     * Set application paths
     *
     * @param array $config            
     * @return void
     */
    public function registerPaths(array $config)
    {
        foreach ($config as $key => $value)
            if ('Path' == substr($key, - 4))
                $this[$key] = $value;
        
        if (null === $this['basePath'])
            throw new \LogicException('basePath must be set in application config');
        
        foreach (array(
            'view',
            'controller',
            'model',
            'component'
        ) as $key)
            if (null === $this[$key . 'Path'])
                $this[$key . 'Path'] = $this['basePath'] . DIRECTORY_SEPARATOR . $key . 's';
    }

    /**
     * If you dont use composer, set config composer false, this method will work
     *
     * @return void
     */
    public function registerIncludePath()
    {
        $includePath = '';
        foreach (static::$bindings as $key => $value)
            if ('Path' == substr($key, - 4))
                $includePath .= PATH_SEPARATOR . $value;
        set_include_path(get_include_path() . $includePath);
    }

    /**
     * Set Exception handler
     *
     * @return void
     */
    public function initExceptionHandler()
    {
        if (null !== $this['exceptionHandler']) {
            set_exception_handler(array(
                $this['exceptionHandler'],
                'handleException'
            ));
            set_error_handler(array(
                $this['exceptionHandler'],
                'handleError'
            ), error_reporting());
        }
    }

    /**
     * Run the application and send the response.
     *
     * @return void
     */
    public function dispatch(RouterInterface $router, RequestInterface $request)
    {
        list ($action, $params) = $router->dispatch($request);
        
        if ($action instanceof Closure) {
            $response = Component::anonymousInvoke($action, $params);
        } elseif (is_string($action) && strpos($action, '@')) {
            $response = Component::atInvoke($action, $params);
        }
        
        if ($response)
            $this->response->setContent($response);
        
        $this->response->send();
    }

    /**
     * Register a "start" callback.
     *
     * @return void
     */
    public function startRun(Closure $callback = null, $priority = 1)
    {
        if (null !== $callback) {
            if (null === $this->startCallbackQueue)
                $this->startCallbackQueue = new SplPriorityQueue();
            $this->startCallbackQueue->insert($callback, $priority);
        } elseif (null !== $this->startCallbackQueue)
            foreach ($this->startCallbackQueue as $start)
                $start();
    }

    /**
     * Run the Application.
     *
     * @return void
     */
    public function run(RouterInterface $router = null, RequestInterface $request = null)
    {
        if (empty($router))
            $router = $this->router;
        if (empty($request))
            $request = $this->request;
        
        $this->startRun();
        $this->dispatch($router, $request);
        $this->finishRun();
    }

    /**
     * Register a "shutdown" callback.
     *
     * @param Closure $callback            
     * @return void
     */
    public function finishRun(Closure $callback = null, $priority = 1)
    {
        if (null !== $callback) {
            if (null === $this->finishCallbackQueue)
                $this->finishCallbackQueue = new SplPriorityQueue();
            $this->finishCallbackQueue->insert($callback, $priority);
        } elseif (null !== $this->finishCallbackQueue)
            foreach ($this->finishCallbackQueue as $finish)
                $finish();
    }

    /**
     * Determine if we are running in the console.
     *
     * @return bool
     */
    public function runningInConsole()
    {
        return php_sapi_name() == 'cli';
    }
}