<?php
namespace DlcDoctrine\Entity;

/**
 * Interface for simple history providing entities
 */
interface HistoryProviderInterface
{
    /**
     * Getter for $createdAt
     *
     * @return DateTime $createdAt
     */
    public function getCreatedAt();
    
    /**
     * Setter for $createdAt
     *
     * @param DateTime $createdAt
     */
    public function setCreatedAt($createdAt);
    
    /**
     * Getter for $createdBy
     *
     * @return \DlcDoctrine\Entity\UserInterface $createdBy
     */
    public function getCreatedBy();
    
    /**
     * Setter for $createdBy
     *
     * @param \DlcDoctrine\Entity\UserInterface $createdBy
     */
    public function setCreatedBy($createdBy);
    
    /**
     * Getter for $updatedAt
     *
     * @return DateTime $updatedAt
     */
    public function getUpdatedAt();
    
    /**
     * Setter for $updatedAt
     *
     * @param DateTime $updatedAt
     */
    public function setUpdatedAt($updatedAt);
    
    /**
     * Getter for $updatedBy
     *
     * @return \DlcDoctrine\Entity\UserInterface $updatedBy
     */
    public function getUpdatedBy();
    
    /**
     * Setter for $updatedBy
     *
     * @param \DlcDoctrine\Entity\UserInterface $updatedBy
     */
    public function setUpdatedBy($updatedBy);
}