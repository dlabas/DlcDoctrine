<?php
namespace DlcDoctrine\Event;

use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\Mvc\Application;
use Zend\Mvc\MvcEvent;

/**
 * Finish listener class for auto flushing on MvcEvent::EVENT_FINISH
 */
class FinishListener implements ListenerAggregateInterface, ObjectManagerAwareInterface
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * Get the object manager
     *
     * @return ObjectManager
     */
    public function getObjectManager()
    {
        if (!$this->objectManager instanceof ObjectManager) {
            $this->setObjectManager($this->getServiceManager()->get('doctrine.entitymanager.orm_default'));
        }
        return $this->objectManager;
    }
    
    /**
     * Set the object manager
     *
     * @param ObjectManager $objectManager
     * @return AclFactory
     */
    public function setObjectManager(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
        return $this;
    }
    
    /**
     * Attach to an event manager
     *
     * @param  EventManagerInterface $events
     * @return void
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_FINISH, array($this, 'onFinish'));
    }
    
    /**
     * Detach all our listeners from the event manager
     *
     * @param  EventManagerInterface $events
     * @return void
     */
    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
    }
    
    /**
     * Listen to the "finish" event and attempt to flush changes
     *
     * @param  MvcEvent $e
     */
    public function onFinish($event)
    {
        $objectManager = $this->getObjectManager();
        $unitOfWork    = $objectManager->getUnitOfWork();
        
        if ($unitOfWork->size() > 0) {
            $objectManager->flush();
        }
    }
}