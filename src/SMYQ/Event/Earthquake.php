<?php
/**
 * Created by PhpStorm.
 * User: AlexanderC <alex@mitocgroup.com>
 * Date: 11/23/14
 * Time: 17:40
 */

namespace SMYQ\Event;


/**
 * Class Earthquake
 * @package SMYQ\Event
 */
class Earthquake extends AbstractEvent
{
    /**
     * @return string
     */
    public function getType()
    {
        return self::EARTHQUAKE;
    }

    /**
     * @param array $rawData
     * @throws Exception\BrokenDataException
     * @return $this
     */
    public function populate(array $rawData)
    {
        return $this->arrayToProperties($rawData);
    }
} 