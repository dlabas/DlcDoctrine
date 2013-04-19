<?php
namespace DlcDoctrine;

use DlcBase\Module\AbstractModule;
use Zend\Mvc\SendResponseListener;

/**
 * The module class
 */
class Module extends AbstractModule
{
    /**
     * Registering module-specific listeners
     * 
     * @param  \Zend\Mvc\MvcEvent $e The MvcEvent instance
     * @return void
     */
    public function onBootstrap($e)
    {
        $eventManager   = $e->getApplication()->getEventManager();
        $serviceManager = $e->getApplication()->getServiceManager();
        $options        = $serviceManager->get('dlcdoctrine_module_options');
        
        if ($options->getEnableAutoFlushFinishListener()) {
            $serviceManager->get('dlcdoctrine_event_finishlistener')
                           ->attach($eventManager);
        }
    }
    
    /**
     * (non-PHPdoc)
     * @see \DlcBase\Module\AbstractModule::getServiceConfig()
     */
    public function getServiceConfig()
    {
        return array(
            'invokables' => array(
                'dlcdoctrine_event_finishlistener' => 'DlcDoctrine\Event\FinishListener',
            ),
            'factories' => array(
                'dlcdoctrine_module_options' => function ($sm) {
                    $config = $sm->get('Config');
                    return new Options\ModuleOptions(isset($config['dlcdoctrine']) ? $config['dlcdoctrine'] : array());
                },
                'dlcdoctrine_resolvetargetentitylistener' => function ($sm) {
                    $options = $sm->get('dlcdoctrine_module_options');
                    //Create instance of ResolveTargetEntityListener
                    $resolveTargetEntityListener = new \Doctrine\ORM\Tools\ResolveTargetEntityListener();
                    //Add all resolve target entities
                    foreach ($options->getResolveTargetEntities() as $originalEntity => $newConfig) {
                        $resolveTargetEntityListener->addResolveTargetEntity($originalEntity, $newConfig['newEntity'], $newConfig['mapping']);
                    }
                    
                    return $resolveTargetEntityListener;
                }
            ),
            'initializers' => array(
                function ($instance, $sm) {
                    if ($instance instanceof \DoctrineModule\Persistence\ObjectManagerAwareInterface) {
                        $instance->setObjectManager($sm->get('doctrine.entitymanager.orm_default'));
                    }
                }
            ),
        );
    }
}