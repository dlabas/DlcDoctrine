<?php
namespace DlcDoctrine\Event;

use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\MvcEvent;

/**
 * Rollback transaction listener class for automatically rolling back a transaction on an error event
 */
class RollbackTransactionListener extends AbstractListener
{
    /**
     * Attach to an event manager
     *
     * @param  EventManagerInterface $events
     * @return void
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH_ERROR, array($this, 'onDispatchError'));
        $this->listeners[] = $events->attach(MvcEvent::EVENT_RENDER_ERROR, array($this, 'onRenderError'));
    }
    
    /**
     * Listen to the "dispatch error" event and attempt to rollback a transaction
     *
     * @param  MvcEvent $e
     */
    public function onDispatchError(MvcEvent $event)
    {
        $this->rollbackTransaction();
    }
    
    /**
     * Listen to the "render error" event and attempt to rollback a transaction
     *
     * @param  MvcEvent $e
     */
    public function onRenderError(MvcEvent $event)
    {
        $this->rollbackTransaction();
    }
    
    /**
     * Try to rollback a transaction is one is active
     */
    protected function rollbackTransaction()
    {
        $connection = $this->getObjectManager()->getConnection();
        
        if ($connection->isTransactionActive()) {
            $connection->rollback();
        }
    }
}
