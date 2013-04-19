<?php
namespace DlcDoctrine\Entity;

use DlcBase\Entity\AbstractEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Abstract entity class. Provides information about creation and update via lifecycle logging
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 */
abstract class AbstractProvidesHistoryEntity 
    extends AbstractEntity
    implements HistoryProviderInterface
{
    /**
     * @ORM\Column(name="created_at",type="datetime")
     * @var \DateTime
     */
    protected $createdAt;
    
    /**
     * ORM\ManyToOne(targetEntity="DlcDoctrine\Entity\UserInterface")
     * ORM\JoinColumn(name="created_by", referencedColumnName="id")
     *
     * @var \DlcDoctrine\Entity\UserInterface
     */
    protected $createdBy;
    
    /**
     * @ORM\Column(name="updated_at",type="datetime",nullable=true)
     * @var \DateTime
     */
    protected $updatedAt;
    
    /**
     * ORM\ManyToOne(targetEntity="DlcDoctrine\Entity\UserInterface")
     * ORM\JoinColumn(name="updated_by", referencedColumnName="id")
     *
     * @var \DlcDoctrine\Entity\UserInterface
     */
    protected $updatedBy;
    
    /**
     * Getter for $createdAt
     *
     * @return DateTime $createdAt
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }
    
    /**
     * Setter for $createdAt
     *
     * @param DateTime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }
    
    /**
     * Getter for $createdBy
     *
     * @return \DlcDoctrine\Entity\UserInterface $createdBy
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }
    
    /**
     * Setter for $createdBy
     *
     * @param \DlcDoctrine\Entity\UserInterface $createdBy
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;
        return $this;
    }
    
    /**
     * Getter for $updatedAt
     *
     * @return DateTime $updatedAt
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }
    
    /**
     * Setter for $updatedAt
     *
     * @param DateTime $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }
    
    /**
     * Getter for $updatedBy
     *
     * @return \DlcDoctrine\Entity\UserInterface $updatedBy
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }
    
    /**
     * Setter for $updatedBy
     *
     * @param \DlcDoctrine\Entity\UserInterface $updatedBy
     */
    public function setUpdatedBy($updatedBy)
    {
        $this->updatedBy = $updatedBy;
        return $this;
    }
    
    /**
     * @ORM\PrePersist
     */
    public function onPrePersist()
    {
        $this->createdAt = new \DateTime();
    }
    
    /**
     * @ORM\PreUpdate
     */
    public function onPreUpdate()
    {
        $this->updatedAt = new \DateTime();
    }
}