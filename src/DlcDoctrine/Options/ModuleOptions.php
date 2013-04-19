<?php

namespace DlcDoctrine\Options;

use Zend\Stdlib\AbstractOptions;

/**
 * The doctine module options class
 */
class ModuleOptions extends AbstractOptions
{
    /**
     * Turn off strict options mode
     */
    protected $__strictMode__ = false;

    /**
     * Definitions for the resolve target entities listener
     * 
     * @var array
     */
    protected $resolveTargetEntities = array();
    
    /**
     * Enable the auto flush listener?
     * 
     * @var bool
     */
    protected $enableAutoFlushFinishListener = true;
    
    /**
     * Getter for $resolveTargetEntities
     *
     * @return multitype: $resolveTargetEntities
     */
    public function getResolveTargetEntities()
    {
        return $this->resolveTargetEntities;
    }

    /**
     * Setter for $resolveTargetEntities
     *
     * @param  multitype: $resolveTargetEntities
     * @return ModuleOptions
     */
    public function setResolveTargetEntities($resolveTargetEntities)
    {
        $this->resolveTargetEntities = $resolveTargetEntities;
        return $this;
    }
	
    /**
     * Getter for $enableAutoFlushFinishListener
     *
     * @return boolean $enableAutoFlushFinishListener
     */
    public function getEnableAutoFlushFinishListener()
    {
        return $this->enableAutoFlushFinishListener;
    }

    /**
     * Setter for $enableAutoFlushFinishListener
     *
     * @param  boolean $enableAutoFlushFinishListener
     * @return ModuleOptions
     */
    public function setEnableAutoFlushFinishListener($enableAutoFlushFinishListener)
    {
        $this->enableAutoFlushFinishListener = $enableAutoFlushFinishListener;
        return $this;
    }
}