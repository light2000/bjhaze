<?php
return array(
    'rules' => array(
        '/' => 'home/index',
        '/{page}' => 'home/page/page/{page}',
        '/category/{tag}' => 'home/category/tag/{tag}',
        '/post/{id}' => 'home/post/id/{id}',
        '/comment' => 'home/comment'
    ),
    'patterns' => array(
        'page' => "[0-9]+",
        'tag' => '[0-9]+',
        'id' => '\d+'
    )
);