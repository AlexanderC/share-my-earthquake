<?php
/**
 * Created by PhpStorm.
 * User: AlexanderC <alex@mitocgroup.com>
 * Date: 11/23/14
 * Time: 17:35
 */

namespace SMYQ\Document;


use SMYQ\Event\EventInterface;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * Class SharedEvent
 * @package SMYQ\Document
 * @ODM\Document(collection="shared_events")
 */
class SharedEvent extends AbstractDocument
{
    /**
     * @var array
     * @ODM\Collection()
     */
    protected $originalData;

    /**
     * @var string
     * @ODM\String()
     */
    protected $sharedText;

    /**
     * @var \DateTime
     * @ODM\Date()
     */
    protected $datetime;

    /**
     * @var string
     * @ODM\String()
     */
    protected $eventId;

    /**
     * @var string
     * @ODM\String()
     */
    protected $type = EventInterface::EARTHQUAKE;

    /**
     * @var SharePoint
     * @ODM\ReferenceOne(targetDocument="SMYQ\Document\SharePoint", cascade={"persist"})
     */
    protected $sharePoint;

    /**
     * @return \DateTime
     */
    public function getDatetime()
    {
        return $this->datetime;
    }

    /**
     * @param \DateTime $datetime
     * @return $this
     */
    public function setDatetime($datetime)
    {
        $this->datetime = $datetime;
        return $this;
    }

    /**
     * @return string
     */
    public function getEventId()
    {
        return $this->eventId;
    }

    /**
     * @param string $eventId
     * @return $this
     */
    public function setEventId($eventId)
    {
        $this->eventId = $eventId;
        return $this;
    }

    /**
     * @return array
     */
    public function getOriginalData()
    {
        return $this->originalData;
    }

    /**
     * @param array $originalData
     * @return $this
     */
    public function setOriginalData(array $originalData)
    {
        $this->originalData = $originalData;
        return $this;
    }

    /**
     * @return string
     */
    public function getSharedText()
    {
        return $this->sharedText;
    }

    /**
     * @param string $sharedText
     * @return $this
     */
    public function setSharedText($sharedText)
    {
        $this->sharedText = $sharedText;
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

    /**
     * @return SharePoint
     */
    public function getSharePoint()
    {
        return $this->sharePoint;
    }

    /**
     * @param SharePoint $sharePoint
     * @return $this
     */
    public function setSharePoint(SharePoint $sharePoint)
    {
        $this->sharePoint = $sharePoint;
        return $this;
    }
} 