<?php
namespace DlcDoctrine\Event;

use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\MvcEvent;

/**
 * Begin transaction listener class for automatically starting a Transaction on the MvcEvent::EVENT_BOOTSTRAP
 */
class BeginTransactionListener extends AbstractListener
{
    /**
     * Attach to an event manager
     *
     * @param  EventManagerInterface $events
     * @return void
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_BOOTSTRAP, array($this, 'onBootstrap'));
    }
    
    /**
     * Listen to the "bootstrap" event and attempt to begin a transaction
     *
     * @param  MvcEvent $e
     */
    public function onBootstrap(MvcEvent $event)
    {
        $this->getObjectManager()->getConnection()->beginTransaction();
    }
}
