<?php
use BJHaze\Routing\RegexRouter as Router;
return array(
    'rules' => array(
        array(
            'path' => '/',
            'action' => 'HomeController@index',
            'from' => Router::GET
        ),
        array(
            'path' => '/{page}',
            'action' => 'HomeController@page',
            'from' => Router::GET
        ),
        array(
            'path' => '/category/{tag}',
            'action' => 'HomeController@category',
            'from' => Router::GET
        ),
        array(
            'path' => '/post/{id}',
            'action' => 'HomeController@post',
            'from' => Router::GET
        ),
        array(
            'path' => '/comment',
            'action' => 'HomeController@comment',
            'from' => Router::POST
        ),
        array(
            'path' => '/admin/{action}?', // "?" in here is the same in regex
            'action' => 'AdminController@{action}',
            'from' => Router::GET | Router::POST
        )
    ),
    'patterns' => array(
        'id' => '\d+',
        'page' => "[0-9]+",
        'tag' => '[0-9]+',
        'action' => '(login|logout|index|blogs|newblog|editblog|removeblog|categories|newcategory|editcategory|removecategory|comments|removecomment)'
    )
);