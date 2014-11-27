<?php
/**
 * Created by PhpStorm.
 * User: AlexanderC <alex@mitocgroup.com>
 * Date: 11/23/14
 * Time: 19:11
 */

namespace SMYQ\Share;


use SMYQ\Document\SocialAccount;

/**
 * Class Manager
 * @package SMYQ\Share
 */
class Manager 
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @var \SMYQ\Authentication\Twitter
     */
    protected $twitter;

    /**
     * @param array $config
     */
    function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @return \SMYQ\Authentication\Twitter
     */
    public function getTwitter()
    {
        return $this->twitter;
    }

    /**
     * @param \SMYQ\Authentication\Twitter $twitter
     * @return $this
     */
    public function setTwitter(\SMYQ\Authentication\Twitter $twitter)
    {
        $this->twitter = $twitter;
        return $this;
    }

    /**
     * @param SocialAccount $socialAccount
     * @param string $text
     * @return bool
     */
    public function share(SocialAccount $socialAccount, $text)
    {
        $client = clone $this->twitter->getClient();
        $client->setToken($socialAccount->getIdentifier(), $socialAccount->getSecret());

        $result = (array) $client->statuses_update(['status' => $text]);

        return !isset($result['errors']) || empty($result['errors']);
    }
} 