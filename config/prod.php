<?php

// configure your app for the production environment

$app['twig.path'] = [__DIR__.'/../templates'];
$app['twig.options'] = ['cache' => __DIR__.'/../var/cache/twig'];
$app['odm.config'] = [
    'server' => '127.0.0.1',
    'options' => [

    ],
    'manager' => [
        'proxyDir' =>  __DIR__.'/../var/cache/odm/Proxies',
        'proxyNamespace' => '/Proxies',
        'hydratorDir' =>  __DIR__.'/../var/cache/odm/Hydrators',
        'hydratorNamespace' => '/Hydrators',
        'defaultDB' => 'smeq'
    ]
];
$app['sharer.config'] = [

];