<?php
return array(
    'defaultConnection' => 'access',
    'connections' => array(
        'access' => array(
            'masters' => array(
                "odbc:driver={microsoft access driver (*.mdb)};dbq=" . dirname(__DIR__) . "/data/blog.mdb"
                //"mysql:host=192.168.189.139;dbname=blog;charset=gbk"
            ),
            'username' => 'root',
            'password' => '',
            'prefix' => 'bjhaze_',
            'attributes' => null
        )
    )
);