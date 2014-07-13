<?php
/**
 *
 * Application class file.
 *
 * @author zhifeng <a_3722@hotmail.com>
 */
namespace BJHaze\Foundation;

use Closure, SplPriorityQueue;

/**
 *
 * @property \BJHaze\Routing\RegexRouter $router Http Request Router
 * @property \BJHaze\Http\Request $request Http Request
 */
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
        static $components = array(
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
        
        static::$bindings = $config;
        static::$lateBindings = array_replace_recursive($components, $config['components']);
        
        if (null === $this['basePath'])
            throw new \LogicException('basePath must be set in application config');
        
        if (null === $this['modulePath'])
            $this['modulePath'] = $this['basePath'] . DIRECTORY_SEPARATOR . 'modules';
        
        set_include_path(get_include_path() . PATH_SEPARATOR . $this['basePath'] . DIRECTORY_SEPARATOR . 'components');
        
        $this->initExceptionHandler();
        
        if (! empty($config['timezone']))
            date_default_timezone_set($config['timezone']);
        
        $this['container'] = $this;
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
     * Run the Application.
     *
     * @return void
     */
    public function run()
    {
        $this->startRun();
        $this->router->forward($this->request->getPathInfo(), $this->request->getBaseUrl(true));
        $this->response->send();
        $this->finishRun();
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