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
     * @param array $config
     */
    function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @param SocialAccount $socialAccount
     * @param string $text
     * @return bool
     */
    public function share(SocialAccount $socialAccount, $text)
    {
        // TODO: implement
    }
} 