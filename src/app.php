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
$app['auth.twitter'] = $app->share(function() use ($app) {
    $config = $app['sharer.config']['twitter'];

    $twitter = new \SMYQ\Authentication\Twitter($config['key'], $config['secret']);
    $twitter->setSession(new \Symfony\Component\HttpFoundation\Session\Session());
    $twitter->loadFromSession();

    return $twitter;
});
$app['twig'] = $app->share($app->extend('twig', function (Twig_Environment $twig, $app) {
    $twig->addGlobal('_logged_in', $app['auth.twitter']->isLoggedIn());

    return $twig;
}));

return $app;
