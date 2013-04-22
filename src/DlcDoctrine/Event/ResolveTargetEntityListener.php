<?php
namespace DlcDoctrine\Event;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\ORM\Tools\ResolveTargetEntityListener as DoctrineResolveTargetEntityListener;

class ResolveTargetEntityListener extends DoctrineResolveTargetEntityListener
    implements EventSubscriber
{
    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(Events::loadClassMetadata);
    }
}