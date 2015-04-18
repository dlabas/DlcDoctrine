<?php
namespace DlcDoctrine\Event;

use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\MvcEvent;

/**
 * Commit transaction listener class for automatically committing a transaction on the MvcEvent::EVENT_FINISH
 */
class CommitTransactionListener extends AbstractListener
{
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
     * Listen to the "finish" event and attempt to commit a transaction
     *
     * @param  MvcEvent $e
     */
    public function onFinish(MvcEvent $event)
    {
        $connection = $this->getObjectManager()->getConnection();
        
        if ($connection->isTransactionActive()) {
            $connection->commitTransaction();
        }
    }
}
