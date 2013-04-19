<?php
namespace DlcDoctrine;

return array(
    'dlcdoctrine' => array(
        'resolveTargetEntities' => array(
            //$originalEntity => array($newEntity, $mapping)//Params of ResolveTargetEntityListener::addResolveTargetEntity
            //Example
            /*'DlcDoctrine\Entity\UserInterface' => array(
                'newEntity' => 'DlcUser\Entity\User',
                'mapping'   => array(),
            ),*/
        ),
    ),
    
    // Doctrine config
    'doctrine' => array(
        'eventmanager' => array(
            // configuration for the `doctrine.eventmanager.orm_default` service
            'orm_default' => array(
                'subscribers' => array('dlcdoctrine_resolvetargetentitylistener')
            )
        ),
    ),
);