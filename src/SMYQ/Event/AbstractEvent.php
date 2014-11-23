<?php
/**
 * Created by PhpStorm.
 * User: AlexanderC <alex@mitocgroup.com>
 * Date: 11/23/14
 * Time: 17:40
 */

namespace SMYQ\Event;


/**
 * Class AbstractEvent
 * @package SMYQ\Event
 */
abstract class AbstractEvent extends \stdClass implements EventInterface
{
    /**
     * @return string
     */
    abstract public function getType();

    /**
     * @param array $rawData
     * @throws Exception\BrokenDataException
     * @return $this
     */
    abstract public function populate(array $rawData);

    /**
     * @param array $data
     * @return $this
     */
    protected function arrayToProperties(array $data)
    {
        foreach($data as $key => $value) {
            $this->{$key} = $value;
        }

        return $this;
    }
} 