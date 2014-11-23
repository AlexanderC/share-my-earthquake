<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

//Request::setTrustedProxies(['127.0.0.1']);

$app->get('/', function () use ($app) {
    return $app['twig']->render('index.html.twig', []);
})
->bind('homepage');

$app->get('/login', function () use ($app) {
    $twitter = $app['auth.twitter'];

    if(!$twitter->isLoggedIn()) {
        return $twitter->acquireToken($app['request']);
    }

    return new RedirectResponse('/dashboard');
})
->bind('login');

$app->get('/logout', function () use ($app) {
    $twitter = $app['auth.twitter'];

    if($twitter->isLoggedIn()) {
        $twitter->logout();
    }

    return new RedirectResponse('/');
})
->bind('logout');

$app->get('/dashboard', function () use ($app) {
    $twitter = $app['auth.twitter'];

    if(!$twitter->isLoggedIn()) {
        return new RedirectResponse('/login');
    }

    $dm = $app['odm'];
    $securityUser = $twitter->getUser();
    $user = $securityUser->loadFromDb($dm);

    if(!$user) {
        list($firstName, $lastName) = explode(' ', $securityUser->name);

        $user = new \SMYQ\Document\User();
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setIdentifier($securityUser->getId());

        $socialAccount = new \SMYQ\Document\SocialAccount();
        $socialAccount->setType(\SMYQ\Document\SocialAccount::TWITTER);
        $socialAccount->setName(sprintf("Default (%s)", $securityUser->name));
        $socialAccount->setIdentifier($twitter->getToken());
        $socialAccount->setSecret($twitter->getTokenSecret());

        $user->addSocialAccount($socialAccount);

        $dm->persist($socialAccount);
        $dm->persist($user);
        $dm->flush();
    }

    return $app['twig']->render('dashboard.html.twig', [
        'user' => $user
    ]);
})
->bind('dashboard');

$app->error(function (\Exception $e, $code) use ($app) {
    if ($app['debug']) {
        return;
    }

    // 404.html.twig, or 40x.html.twig, or 4xx.html.twig, or error.html.twig
    $templates = array(
        'errors/'.$code.'.html.twig',
        'errors/'.substr($code, 0, 2).'x.html.twig',
        'errors/'.substr($code, 0, 1).'xx.html.twig',
        'errors/default.html.twig',
    );

    return new Response($app['twig']->resolveTemplate($templates)->render(['code' => $code]), $code);
});
