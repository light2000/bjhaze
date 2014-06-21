<?php
return array(
    'keyPrefix' => 'bjhaze',
    'default' => 'apc',
    'servers' => array(
        'apc' => array(
            'driver' => 'apc'
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
        ),
        'memcache' => array(
            'driver' => 'memcache',
            'servers' => array(
                array(
                    'host' => 'localhost',
                    'port' => 11211
                )
            )
        )
    )
    
);