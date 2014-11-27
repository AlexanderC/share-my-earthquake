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

$app->post('/api/social-preview/{type}', function ($type) use ($app) {
    $twitter = $app['auth.twitter'];
    $template = $app['request']->get('template');

    if(!$twitter->isLoggedIn()) {
        return new JsonResponse([
            'status' => 401,
            'error' => 'Not Authorized',
            'errorDescription' => 'You must be logged in'
        ]);
    } elseif($type !== \SMYQ\Document\SharePoint::EARTHQUAKE) {
        return new JsonResponse([
            'status' => 406,
            'error' => 'Not Acceptable',
            'errorDescription' => sprintf('Only %s type is currently supported', \SMYQ\Document\SharePoint::EARTHQUAKE)
        ]);
    }

    $sharePoint = new \SMYQ\Document\SharePoint();
    $sharePoint->setName("Preview");
    $sharePoint->setType($type);
    $sharePoint->setDistance(777);
    $sharePoint->setCalculatedDistance(333);
    $sharePoint->setCoordinates(new \SMYQ\Document\Coordinates(77, 33));
    $sharePoint->setTemplate($template);

    $url = "http://earthquake.usgs.gov/earthquakes/feed/v1.0/summary/all_hour.geojson";
    $geoJsonData = @json_decode(@file_get_contents($url) ? : []) ? : [];

    /** @var \GeoJson\Feature\FeatureCollection $featureCollection */
    $featureCollection = \GeoJson\GeoJson::jsonUnserialize($geoJsonData);
    /** @var \GeoJson\Feature\Feature $feature */
    $feature = $featureCollection->getIterator()->current();
    $properties = $feature->getProperties();
    $template = new \SMYQ\Share\Template();
    $event = (new \SMYQ\Event\Earthquake())->populate($properties);

    $preview = $template->render($sharePoint, $event);

    return new JsonResponse([
        'status' => 200,
        'preview' => $preview
    ]);
})
->bind('social_preview');

$app->delete('/api/share-point/{id}', function ($id) use ($app) {
    $twitter = $app['auth.twitter'];

    if(!$twitter->isLoggedIn()) {
        return new JsonResponse([
            'status' => 401,
            'error' => 'Not Authorized',
            'errorDescription' => 'You must be logged in'
        ]);
    }

    $dm = $app['odm'];
    $securityUser = $twitter->getUser();
    $user = $securityUser->loadFromDb($dm);

    $sharePoint = $dm->getRepository(\SMYQ\Document\SharePoint::class)->find($id);

    if(!$sharePoint) {
        return new JsonResponse([
            'status' => 404,
            'error' => 'Not Found',
            'errorDescription' => 'No such share point found'
        ]);
    } elseif(!$user->getSharePoints()->contains($sharePoint)) {
        return new JsonResponse([
            'status' => 406,
            'error' => 'Not Acceptable',
            'errorDescription' => 'You can not delete foreign share point'
        ]);
    }

    $user->removeSharePoint($sharePoint);

    try {
        $dm->remove($sharePoint);
        $dm->persist($user);
        $dm->flush();

        return new JsonResponse([
            'status' => 200,
        ]);
    } catch(\Exception $e) {
        return new JsonResponse([
            'status' => 500,
            'error' => 'Internal Server Error',
            'errorDescription' => $e->getMessage()
        ]);
    }
})
->bind('api_delete_share_point');

$app->post('/api/share-point/{type}', function ($type) use ($app) {
    $twitter = $app['auth.twitter'];

    $request = $app['request'];

    foreach(['name', 'template', 'latitude', 'longitude', 'distance', 'social_account_id'] as $var) {
        $$var = $request->get($var, null);
    }

    if(!$twitter->isLoggedIn()) {
        return new JsonResponse([
            'status' => 401,
            'error' => 'Not Authorized',
            'errorDescription' => 'You must be logged in'
        ]);
    } elseif($type !== \SMYQ\Document\SharePoint::EARTHQUAKE) {
        return new JsonResponse([
            'status' => 406,
            'error' => 'Not Acceptable',
            'errorDescription' => sprintf('Only %s type is currently supported', \SMYQ\Document\SharePoint::EARTHQUAKE)
        ]);
    }

    $dm = $app['odm'];
    $securityUser = $twitter->getUser();
    $user = $securityUser->loadFromDb($dm);

    $socialAccount = $dm->getRepository(\SMYQ\Document\SocialAccount::class)->findOneBy(['id' => $social_account_id]);

    if(!$socialAccount) {
        return new JsonResponse([
            'status' => 404,
            'error' => 'Not Found',
            'errorDescription' => sprintf('There is no such social account')
        ]);
    }

    $coordinates = new \SMYQ\Document\Coordinates();
    $coordinates->setLatitude((float) $latitude);
    $coordinates->setLongitude((float) $longitude);

    $sharePoint = new \SMYQ\Document\SharePoint();
    $sharePoint->setType($type);
    $sharePoint->setName($name);
    $sharePoint->setDistance((float) $distance);
    $sharePoint->setCoordinates($coordinates);
    $sharePoint->setSocialAccount($socialAccount);
    $sharePoint->setTemplate($template);

    $user->addSharePoint($sharePoint);

    $dm->persist($sharePoint);
    $dm->persist($user);

    try {
        $dm->flush();

        return new JsonResponse($sharePoint);
    } catch(\Exception $e) {
        return new JsonResponse([
            'status' => 500,
            'error' => 'Internal Server Error',
            'errorDescription' => $e->getMessage()
        ]);
    }
})
->bind('api_create_share_point');

$app->get('/share-point/create', function () use ($app) {
    $twitter = $app['auth.twitter'];

    if(!$twitter->isLoggedIn()) {
        return new RedirectResponse('/login');
    }

    $dm = $app['odm'];
    $securityUser = $twitter->getUser();
    $user = $securityUser->loadFromDb($dm);

    return $app['twig']->render('create_share_point.html.twig', [
        'user' => $user
    ]);
})
->bind('create_share_point');

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
