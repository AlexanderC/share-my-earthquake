<?php
/**
 * Created by PhpStorm.
 * User: AlexanderC <alex@mitocgroup.com>
 * Date: 11/23/14
 * Time: 16:50
 */

namespace SMYQ\Document;


use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * Class User
 * @package SMYQ\Document
 * @ODM\Document(collection="users")
 */
class User extends AbstractDocument
{
    /**
     * @var string
     * @ODM\String()
     */
    protected $firstName;

    /**
     * @var string
     * @ODM\String()
     */
    protected $lastName;

    /**
     * @var string
     * @ODM\String()
     */
    protected $email;

    /**
     * @var SharePoint[]
     * @ODM\ReferenceMany(targetDocument="SMYQ\Document\SharePoint", cascade={"persist"})
     */
    protected $sharePoints = [];

    /**
     * @var SocialAccount[]
     * @ODM\ReferenceMany(targetDocument="SMYQ\Document\SocialAccount", cascade={"persist"})
     */
    protected $socialAccounts = [];

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return $this
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     * @return $this
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     * @return $this
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
        return $this;
    }

    /**
     * @return SharePoint[]
     */
    public function getSharePoints()
    {
        return $this->sharePoints;
    }

    /**
     * @param SharePoint $sharePoint
     * @return $this
     */
    public function addSharePoint(SharePoint $sharePoint)
    {
        $this->sharePoints[] = $sharePoint;
        return $this;
    }

    /**
     * @param SharePoint $sharePoint
     * @return $this
     */
    public function removeSharePoint(SharePoint $sharePoint)
    {
        return $this->removeFromCollection($this->sharePoints, $sharePoint);
    }

    /**
     * @param SharePoint $sharePoint
     * @return bool
     */
    public function containsSharePoint(SharePoint $sharePoint)
    {
        return $this->collectionContains($this->sharePoints, $sharePoint);
    }

    /**
     * @return SocialAccount[]
     */
    public function getSocialAccounts()
    {
        return $this->socialAccounts;
    }

    /**
     * @param SocialAccount $socialAccount
     * @return $this
     */
    public function addSocialAccount(SocialAccount $socialAccount)
    {
        $this->socialAccounts[] = $socialAccount;
        return $this;
    }

    /**
     * @param SocialAccount $socialAccount
     * @return $this
     */
    public function removeSocialAccount(SocialAccount $socialAccount)
    {
        return $this->removeFromCollection($this->socialAccounts, $socialAccount);
    }

    /**
     * @param SocialAccount $socialAccount
     * @return bool
     */
    public function containsSocialAccount(SocialAccount $socialAccount)
    {
        return $this->collectionContains($this->socialAccounts, $socialAccount);
    }
} 