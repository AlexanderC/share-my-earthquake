<?php
/**
 * Created by PhpStorm.
 * User: AlexanderC <alex@mitocgroup.com>
 * Date: 11/23/14
 * Time: 23:40
 */

namespace SMYQ\Authentication;


use Doctrine\ODM\MongoDB\DocumentManager;

/**
 * Class User
 * @package SMYQ\Authentication
 */
class User extends \stdClass
{
    /**
     * @param array $userData
     * @return static
     */
    public static function fromArray(array $userData)
    {
        $self = new static;

        foreach($userData as $key => $value) {
            $self->{$key} = $value;
        }

        return $self;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return sprintf('twitter:%d:%s', $this->id, $this->screen_name);
    }

    /**
     * @param DocumentManager $dm
     * @return array
     */
    public function loadFromDb(DocumentManager $dm)
    {
        return $dm->getRepository(\SMYQ\Document\User::class)
            ->findOneBy(['identifier' => $this->getId()]);
    }
} 