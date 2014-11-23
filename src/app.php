<?php

use Silex\Application;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\MongoDB\Connection;
use Doctrine\ODM\MongoDB\Configuration;
use SMYQ\Share\Manager;
use Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver;

$app = new Application();
$app->register(new UrlGeneratorServiceProvider());
$app->register(new ValidatorServiceProvider());
$app->register(new ServiceControllerServiceProvider());
$app->register(new TwigServiceProvider());
$app['twig'] = $app->share($app->extend('twig', function ($twig, $app) {
    // add custom globals, filters, tags, ...

    return $twig;
}));
$app['odm'] = $app->share(function() use ($app) {
    AnnotationDriver::registerAnnotationClasses();

    $connection = new Connection($app['odm.config']['server'], $app['odm.config']['options']);

    $configuration = new Configuration();
    $configurationReflection = new \ReflectionClass($configuration);
    $configurationAttributes = $configurationReflection->getProperty('attributes');
    $configurationAttributes->setAccessible(true);
    $configurationAttributes->setValue($configuration, $app['odm.config']['manager']);
    $configuration->setMetadataDriverImpl(AnnotationDriver::create(__DIR__.'/SMYQ/Document'));

    $documentManager = DocumentManager::create($connection, $configuration);

    return $documentManager;
});
$app['sharer'] = $app->share(function() use ($app) {
    $manager = new Manager($app['sharer.config']);

    return $manager;
});

return $app;
