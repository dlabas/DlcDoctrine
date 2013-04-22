<?php
namespace DlcDoctrine\Form\Element;

use DoctrineModule\Form\Element\Proxy as DoctrineProxy;

class Proxy extends DoctrineProxy
{
    /**
     * Load value options
     *
     * @throws \RuntimeException
     * @return void
     */
    protected function loadValueOptions()
    {
        $om = $this->objectManager;
        if (!$om) {
            throw new RuntimeException('No object manager was set');
        }
    
        if (!($targetClass = $this->targetClass)) {
            throw new RuntimeException('No target class was set');
        }
    
        $metadata   = $om->getClassMetadata($targetClass);
        $identifier = $metadata->getIdentifierFieldNames();
        $objects    = $this->getObjects();
        $options    = array();
    
        if (empty($objects)) {
            $options[''] = '';
        } else {
            foreach ($objects as $key => $object) {
                if (($property = $this->property)) {
                    if ($this->isMethod == false && !$metadata->hasField($property)) {
                        throw new RuntimeException(sprintf(
                            'Property "%s" could not be found in object "%s"',
                            $property,
                            $targetClass
                        ));
                    }
    
                    $getter = 'get' . ucfirst($property);
                    if (!is_callable(array($object, $getter))) {
                        throw new RuntimeException(sprintf(
                            'Method "%s::%s" is not callable',
                            $this->targetClass,
                            $getter
                        ));
                    }
    
                    $label = $object->{$getter}();
                } else {
                    if (!is_callable(array($object, '__toString'))) {
                        throw new RuntimeException(sprintf(
                            '%s must have a "__toString()" method defined if you have not set a property or method to use.',
                            $targetClass
                        ));
                    }
    
                    $label = (string) $object;
                }
    
                if (count($identifier) > 1) {
                    $value = $key;
                } else {
                    $value = current($metadata->getIdentifierValues($object));
                }
    
                $options[] = array('label' => $label, 'value' => $value);
            }
        }
    
        $this->valueOptions = $options;
    }
}