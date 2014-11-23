<?php
/**
 * Created by PhpStorm.
 * User: AlexanderC <alex@mitocgroup.com>
 * Date: 11/23/14
 * Time: 17:53
 */

namespace SMYQ\Share;


use SMYQ\Document\SharePoint;
use SMYQ\Event\AbstractEvent;

/**
 * Class Template
 * @package SMYQ\Share
 */
class Template 
{
    /**
     * @var \Twig_Environment
     */
    protected $engine;

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        $this->engine = new \Twig_Environment(new \Twig_Loader_String());
    }

    /**
     * @return \Twig_Environment
     */
    public function getEngine()
    {
        return $this->engine;
    }

    /**
     * @param SharePoint $sharePoint
     * @param AbstractEvent $event
     * @return string
     */
    public function render(SharePoint $sharePoint, AbstractEvent $event)
    {
        return $this->engine->render($sharePoint->getTemplate(), $this->createContext($event));
    }

    /**
     * @param AbstractEvent $event
     * @return array
     */
    protected function createContext(AbstractEvent $event)
    {
        return [
            $event->getType() => $event
        ];
    }
} 