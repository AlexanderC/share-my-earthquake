<?php
/**
 * Created by PhpStorm.
 * User: AlexanderC <alex@mitocgroup.com>
 * Date: 11/23/14
 * Time: 16:55
 */

namespace SMYQ\Document;


use SMYQ\Share\SocialAccountInterface;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * Class SocialAccount
 * @package SMYQ\Document
 * @ODM\Document(collection="social_accounts")
 */
class SocialAccount extends AbstractDocument implements SocialAccountInterface
{
    /**
     * @var string
     * @ODM\String()
     */
    protected $name;

    /**
     * @var string
     * @ODM\String()
     */
    protected $type = self::TWITTER;

    /**
     * @var string
     * @ODM\String()
     */
    protected $identifier;

    /**
     * @var string
     * @ODM\String()
     */
    protected $secret;

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @param string $identifier
     * @return $this
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * @param string $secret
     * @return $this
     */
    public function setSecret($secret)
    {
        $this->secret = $secret;
        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }
} 