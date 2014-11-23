<?php
/**
 * Created by PhpStorm.
 * User: AlexanderC <alex@mitocgroup.com>
 * Date: 11/23/14
 * Time: 22:46
 */

namespace SMYQ\Authentication;


use Codebird\Codebird;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class Twitter
 * @package SMYQ\Authentication
 */
class Twitter 
{
    const TOKEN_REQUEST = '_token_request';
    const TOKEN = '_token';
    const TOKEN_SECRET = '_token_secret';
    const USER = '_user';

    /**
     * @var Codebird
     */
    protected $client;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var string
     */
    protected $token;

    /**
     * @var string
     */
    protected $tokenSecret;

    /**
     * @var User
     */
    protected $user;

    /**
     * @param string $key
     * @param string $secret
     */
    public function __construct($key, $secret)
    {
        Codebird::setConsumerKey($key, $secret);
        $this->client = Codebird::getInstance();
    }

    /**
     * @return Codebird
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @return Session
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * @param Session $session
     * @return $this
     */
    public function setSession(Session $session)
    {
        $this->session = $session;
        return $this;
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function acquireToken(Request $request)
    {
        if($request->query->has('oauth_verifier') && $this->session->has(self::TOKEN_REQUEST)) {
            $tokenInfo = $this->session->get(self::TOKEN_REQUEST);

            $this->client->setToken($tokenInfo['token'], $tokenInfo['token_secret']);

            $tokenRequest = $this->client->oauth_accessToken([
                'oauth_verifier' => $request->query->get('oauth_verifier')
            ]);

            $this->session->remove(self::TOKEN_REQUEST);
            $this->session->set(self::TOKEN, $tokenRequest->oauth_token);
            $this->session->set(self::TOKEN_SECRET, $tokenRequest->oauth_token_secret);

            $this->session->save();

            return new RedirectResponse($request->getUri());
        } else {
            $tokenRequest = $this->client->oauth_requestToken([
                'oauth_callback' => $request->getUri()
            ]);

            $this->client->setToken($tokenRequest->oauth_token, $tokenRequest->oauth_token_secret);
            $this->session->set(self::TOKEN_REQUEST, [
                'token' => $tokenRequest->oauth_token,
                'token_secret' => $tokenRequest->oauth_token_secret
            ]);

            $this->session->save();

            return new RedirectResponse($this->client->oauth_authorize());
        }
    }

    /**
     * @return bool
     */
    public function isLoggedIn()
    {
        return isset($this->token, $this->tokenSecret, $this->user);
    }

    /**
     * @return User
     */
    public function getUser()
    {
        if(!$this->isLoggedIn()) {
            return null;
        }

        return $this->user;
    }

    /**
     * @return void
     */
    public function logout()
    {
        $this->session->remove(self::TOKEN);
        $this->session->remove(self::TOKEN_SECRET);
        $this->session->remove(self::USER);
    }

    /**
     * @return void
     */
    public function loadFromSession()
    {
        if(!$this->session->isStarted()) {
            $this->session->start();
        }

        if(!$this->session->has(self::TOKEN)
            || !$this->session->has(self::TOKEN_SECRET)) {
            return;
        }

        $this->token = $this->session->get(self::TOKEN);
        $this->tokenSecret = $this->session->get(self::TOKEN_SECRET);

        $this->client->setToken($this->token, $this->tokenSecret);

        if(!$this->session->has(self::USER)) {
            $this->session->set(self::USER, User::fromArray((array) $this->client->account_verifyCredentials()));
            $this->session->save();
        }

        $this->user = $this->session->get(self::USER);
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return string
     */
    public function getTokenSecret()
    {
        return $this->tokenSecret;
    }
} 