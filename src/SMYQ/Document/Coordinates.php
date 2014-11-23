<?php
/**
 * Created by PhpStorm.
 * User: AlexanderC <alex@mitocgroup.com>
 * Date: 11/23/14
 * Time: 18:37
 */

namespace SMYQ\Document;


use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * Class Coordinates
 * @package SMYQ\Document
 * @ODM\EmbeddedDocument()
 */
class Coordinates 
{
    /**
     * @var float
     * @ODM\Float()
     */
    protected $latitude;

    /**
     * @var float
     * @ODM\Float()
     */
    protected $longitude;

    /**
     * @return float
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * @param float $latitude
     * @return $this
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;
        return $this;
    }

    /**
     * @return float
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * @param float $longitude
     * @return $this
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;
        return $this;
    }
} 