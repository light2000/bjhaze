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

class Application extends Component
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
    public function __construct (array $config)
    {
        $components = array(
                'request' => 'BJHaze\Http\Request',
                'response' => 'BJHaze\Http\Response',
                'validator' => 'BJHaze\Validation\Validator',
                'cacheProvider' => 'BJHaze\Behavior\Cache',
                'exceptionHandler' => 'BJHaze\Exception\Handler',
                'sessionHandler' => array(
                        'class' => 'BJHaze\Session\Handler\File'
                ),
                'session' => array(
                        'class' => 'BJHaze\Session\Manager'
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
        $this['app'] = $this;
        $this->registerPaths($config);
        $this->resetLateBindings(array_replace_recursive($components, $config));
        $this->initExceptionHandler();
    }

    /**
     * Set application paths
     *
     * @param array $config
     * @return void
     */
    public function registerPaths (array $config)
    {
        $this['dir'] = $config['dir'];
        $this['viewPath'] = isset($config['viewPath']) ? $config['viewPath'] : $this['dir'] .
                 DIRECTORY_SEPARATOR . 'views';
    }

    /**
     * Set Exception handler
     *
     * @return void
     */
    public function initExceptionHandler ()
    {
        if (null !== $this['exceptionHandler']) {
            set_exception_handler(
                    array(
                            $this['exceptionHandler'],
                            'handleException'
                    ));
            set_error_handler(
                    array(
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
    public function dispatch (RouterInterface $router, RequestInterface $request)
    {
        list ($action, $params) = $router->dispatch($request);
        
        $response = $this->runAction($action, (array) $params, true);
        
        if ($response)
            $this->response->setContent($response);
        
        $this->response->send();
    }

    /**
     * Register a "start" callback.
     *
     * @return void
     */
    public function startRun (Closure $callback = null, $priority = 1)
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
    public function run (RouterInterface $router = null, RequestInterface $request = null)
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
    public function finishRun (Closure $callback = null, $priority = 1)
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
    public function runningInConsole ()
    {
        return php_sapi_name() == 'cli';
    }
}