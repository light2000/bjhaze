<?php
define('BJHAZE_DEBUG', true);

// whether use composer
define('USE_COMPOSER', false);

// autoloader without composer
if (! USE_COMPOSER) {
    define('BJHAZE_PATH', dirname(dirname(dirname(__DIR__))) . '/vendor/bjhaze/framework/src');
    set_include_path(get_include_path() . PATH_SEPARATOR . BJHAZE_PATH);
    spl_autoload_register(function ($className)
    {
        include str_replace('\\', DIRECTORY_SEPARATOR, $className) . '.php';
        return class_exists($className, false) or interface_exists($className, false) or trait_exists($className);
        false;
    }, true, true);
} else {
    require dirname(dirname(dirname(__DIR__))) . '/vendor/autoload.php';
}

// application configure
$config = array(
    'basePath' => dirname(__DIR__),
    'composer' => USE_COMPOSER,
    'timezone' => 'PRC',
    'blog_page_size' => 10,
    'comment_page_size' => 10,
    'blog_bottom_page_numbers' => 5,
    'site_name' => 'bjhaze demo',
    'site_keywords' => 'bjhaze,php5.4 framework',
    'site_description' => 'bjhaze is a simple php5.4 mvc framework',
    'modules' => array('admin'),
    'components' => array(
        'sessionHandler' => array(
            'class' => 'BJHaze\Session\Handler\File',
            'path' => dirname(__DIR__) . '/tmp'
        ),
        'login' => array(
            'class' => 'LoginFilter'
        ),
        'db' => require 'db.php',
        'router' => require 'routes.php',
        'cache' => require 'cache.php',
        'response' => array(
            'charset' => 'gbk',
            'cachePaths' => array()
        )
    )
)
;

return new \BJHaze\Foundation\Application($config);