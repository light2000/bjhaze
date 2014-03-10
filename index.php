<?php
$start = microtime(true);
define('BJHAZE_DEBUG', true);
error_reporting(E_ALL);
date_default_timezone_set('PRC');
use BJHaze\Routing\RegexRouter as Router;
require __DIR__ . '/vendor/autoload.php';
$config = array(
        'dir' => __DIR__ . '/app',
        'sessionHandler' => array(
                'class' => 'BJHaze\Session\Handler\Database',
                'path' => __DIR__ . '/app/tmp',
                'connection' => 'def',
                'minutes' => 30,
                'table' => 'session'
        ),
        'db' => array(
                'defaultConnection' => 'def',
                'connections' => array(
                        'def' => array(
                                'driver' => 'mysql',
                                'masters' => array(
                                        'mysql:host=localhost;dbname=test;charset=utf8'
                                ),
                                'slaves' => array(
                                        'mysql:host=localhost;dbname=test;charset=utf8'
                                ),
                                'username' => 'root',
                                'password' => '',
                                'prefix' => 'ts_',
                                'attributes' => null,
                                'queue' => null
                        )
                )
        ),
        'encrypter' => array(
                'key' => 'dddd'
        ),
        'router' => array(
                'rules' => array(
                        array(
                                'path' => '/{nn}/{ee}',
                                'action' => '{nn}Controller@index',
                                'from' => Router::GET
                        ),
                        array(
                                'path' => '/{ff}/{dd}/{ss}',
                                'action' => function  ($dd, $ff, $ss)
                                {
                                    var_dump($dd, $ff, $ss);
                                },
                                'from' => Router::GET
                        )
                ),
                'patterns' => array(
                        'nn' => "[a-z]+"
                )
        ),
        'cache' => array(
                'keyPrefix' => 'bjhaze',
                'default' => 'memcache',
                'servers' => array(
                        'apc' => array(
                                'driver' => 'apc'
                        ),
                        'eaccelerator' => array(
                                'driver' => 'eaccelerator'
                        ),
                        'memcache' => array(
                                'driver' => 'memcache',
                                'servers' => array(
                                        array(
                                                'host' => 'localhost',
                                                'port' => 11211,
                                                'weight' => 1
                                        )
                                )
                        ),
                        'memcached' => array(
                                'driver' => 'memcached',
                                'servers' => array(
                                        array(
                                                'host' => 'localhost',
                                                'port' => 11211,
                                                'weight' => 1
                                        )
                                )
                        ),
                        'redis' => array(
                                'driver' => 'redis',
                                'server' => array(
                                        'host' => 'localhost',
                                        'port' => 6379,
                                        'timeout' => 0,
                                        'pconnect' => false,
                                        'auth' => null
                                )
                        )
                )
        )
);

$a = new \BJHaze\Foundation\Application($config);
$a->run();

echo '<br />runtime', microtime(true) - $start, '<br />', memory_get_usage(true) / 1024, '<br />', nl2br(
        print_r(get_included_files(), true));
