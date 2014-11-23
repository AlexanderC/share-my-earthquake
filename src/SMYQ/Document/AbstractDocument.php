<?php
/**
 * Created by PhpStorm.
 * User: AlexanderC <alex@mitocgroup.com>
 * Date: 11/23/14
 * Time: 18:05
 */

namespace SMYQ\Document;


use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * Class AbstractDocument
 * @package SMYQ\Document
 */
abstract class AbstractDocument
{
    /**
     * @var string
     * @ODM\Id()
     */
    protected $id;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param array $collection
     * @param mixed $element
     * @return $this
     */
    protected function removeFromCollection(array & $collection, $element)
    {
        foreach($collection as $key => $item) {
            if($item === $element) {
                unset($collection[$key]);
                $collection = array_values($collection);
                break;
            }
        }

        return $this;
    }

    /**
     * @param array $collection
     * @param mixed $element
     * @return bool
     */
    protected function collectionContains(array $collection, $element)
    {
        return false !== array_search($element, $collection, true);
    }
} 