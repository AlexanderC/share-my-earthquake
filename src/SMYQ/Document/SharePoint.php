<?php
/**
 * Created by PhpStorm.
 * User: AlexanderC <alex@mitocgroup.com>
 * Date: 11/23/14
 * Time: 16:52
 */

namespace SMYQ\Document;


use SMYQ\Event\EventInterface;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * Class SharePoint
 * @package SMYQ\Document
 * @ODM\Document(collection="share_points")
 * @ODM\Index(keys={"coordinates"="2d"})
 */
class SharePoint extends AbstractDocument implements EventInterface
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
    protected $type = self::EARTHQUAKE;

    /**
     * @var Coordinates
     * @ODM\EmbedOne(targetDocument="SMYQ\Document\Coordinates")
     */
    protected $coordinates;

    /**
     * @var float
     * @ODM\Distance()
     */
    protected $distance;

    /**
     * @var SocialAccount
     * @ODM\ReferenceOne(targetDocument="SMYQ\Document\SocialAccount", cascade={"persist"})
     */
    protected $socialAccount;

    /**
     * @var string
     * @ODM\String()
     */
    protected $template;

    /**
     * @var SharedEvent[]
     * @ODM\ReferenceMany(targetDocument="SMYQ\Document\SharedEvent", cascade={"persist"})
     */
    protected $sharedEvents = [];

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
     * @return Coordinates
     */
    public function getCoordinates()
    {
        return $this->coordinates;
    }

    /**
     * @param Coordinates $coordinates
     * @return $this
     */
    public function setCoordinates(Coordinates $coordinates)
    {
        $this->coordinates = $coordinates;
        return $this;
    }

    /**
     * @return float
     */
    public function getDistance()
    {
        return $this->distance;
    }

    /**
     * @param float $distance
     * @return $this
     */
    public function setDistance($distance)
    {
        $this->distance = $distance;
        return $this;
    }

    /**
     * @return SharedEvent[]
     */
    public function getSharedEvents()
    {
        return $this->sharedEvents;
    }

    /**
     * @param SharedEvent $sharedEvent
     * @return $this
     */
    public function addSharedEvent(SharedEvent $sharedEvent)
    {
        $this->sharedEvents[] = $sharedEvent;
        return $this;
    }

    /**
     * @param SharedEvent $sharedEvent
     * @return $this
     */
    public function removeSharedEvent(SharedEvent $sharedEvent)
    {
        return $this->removeFromCollection($this->sharedEvents, $sharedEvent);
    }

    /**
     * @param SharedEvent $sharedEvent
     * @return bool
     */
    public function containsSharedEvent(SharedEvent $sharedEvent)
    {
        return $this->collectionContains($this->sharedEvents, $sharedEvent);
    }

    /**
     * @return SocialAccount
     */
    public function getSocialAccount()
    {
        return $this->socialAccount;
    }

    /**
     * @param SocialAccount $socialAccount
     * @return $this
     */
    public function setSocialAccount(SocialAccount $socialAccount)
    {
        $this->socialAccount = $socialAccount;
        return $this;
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param string $template
     * @return $this
     */
    public function setTemplate($template)
    {
        $this->template = $template;
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