<?php
namespace DlcDoctrine\Mapper;

use DlcBase\Mapper\AbstractMapper as AbstractBaseMapper;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator;
use Zend\Paginator\Paginator;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Abstract mapper class for doctrine entities 
 */
class AbstractMapper extends AbstractBaseMapper implements ObjectManagerAwareInterface
{
    /**
     * Class name of entity class
     * 
     * @var string
     */
    protected $entityClass;
    
    /**
     * Alias for entity class name
     * 
     * @var string
     */
    protected $entityClassAlias;
    
    /**
     * @var ObjectManager
     */
    protected $objectManager;
    
    /**
     * Getter for $entityClass
     *
     * @return string $entityClass
     */
    public function getEntityClass()
    {
        if ($this->entityClass === null) {
            $class = get_class($this);
            $method = 'get' . substr($class, strrpos($class, '\\')+1) . 'EntityClass';
            $this->setEntityClass($this->getOptions()->$method());
        }
        return $this->entityClass;
    }
    
    /**
     * Setter for $entityClass
     *
     * @param  string $entityClass
     * @return AbstractService
     */
    public function setEntityClass($entityClass)
    {
        $this->entityClass = $entityClass;
        return $this;
    }
    
    /**
     * Getter for $entityClassAlias
     *
     * @return string $entityClassAlias
     */
    public function getEntityClassAlias()
    {
        if ($this->entityClassAlias === null) {
            $entityClass = $this->getEntityClass();
            $this->setEntityClassAlias(strtolower(substr($entityClass, strrpos($entityClass, '\\')+1, 1)));
        }
        return $this->entityClassAlias;
    }
    
    /**
     * Setter for $entityClassAlias
     *
     * @param  string $entityClassAlias
     * @return AbstractService
     */
    public function setEntityClassAlias($entityClassAlias)
    {
        $this->entityClassAlias = $entityClassAlias;
        return $this;
    }
    
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
     * Returns a list of fields for the query condition
     * 
     * @return array
     */
    public function getQueryableProperties()
    {
        $classMetadata = $this->getObjectManager()
                              ->getClassMetadata($this->getEntityClass());
        
        $fields = $classMetadata->getFieldNames();
        $alias  = $this->getEntityClassAlias();
        
        foreach ($fields as $key => &$field) {
            $fieldType = $classMetadata->getTypeOfField($field);
            if (!in_array($fieldType, array('string', 'text'))) {
                unset($fields[$key]);
            } else {
                $field = $alias . '.' . $field;
            }
        }
        
        return $fields;
    }
    
    /**
     * Returns all entities
     * 
     * @return null|array
     */
    public function findAll()
    {
        return $this->getObjectManager()
                    ->getRepository($this->getEntityClass())
                    ->findAll();
    }
    
    /**
     * Returns the entity for the primary key $id
     * 
     * @param \DlcDoctrine\Entity\AbstractEntity $id
     * @return object
     */
    public function find($id)
    {
        return $this->getObjectManager()
                    ->getRepository($this->getEntityClass())
                    ->find($id);
    }
    
    /**
     * Returns a pagination object with entities
     * 
     * @param int $page
     * @param int $limit
     * @param null|string $query
     * @param null|string $orderBy
     * @param string $sort
     * @return \Zend\Paginator\Paginator
     */
    public function pagination($page, $limit, $query = null, $orderBy = null, $sort = 'ASC')
    {
        $entityClassAlias = $this->getEntityClassAlias();
        
        // Create a Doctrine Collection
        $queryBuilder = $this->getObjectManager()->createQueryBuilder();
        $queryBuilder->select($entityClassAlias)
                     ->from($this->getEntityClass(), $entityClassAlias);
    
        if (null !== $query) {
            $properties    = $this->getQueryableProperties();
            $orExpressions = array();
            
            foreach ($properties as $property) {
                $orExpressions[] = $queryBuilder->expr()->like($property, '?1');
            }
            
            $queryBuilder->where(
                call_user_func_array(array($queryBuilder->expr(), "orX"), $orExpressions)
            );
            
            $queryBuilder->setParameter(1, $query);
        }
    
        if($orderBy) {
            if (!$sort) {
                $sort = 'ASC';
            }
            $queryBuilder->orderBy($entityClassAlias . '.' . $orderBy, $sort);
        }
    
        // Create the paginator itself
        $paginator = new Paginator(
            new DoctrinePaginator(new ORMPaginator($queryBuilder))
        );
    
        $paginator->setCurrentPageNumber($page)
                  ->setItemCountPerPage($limit);
    
        return $paginator;
    }
    
    /**
     * Saves an entity
     * 
     * @param \DlcDoctrine\Entity\AbstractEntity $entity
     */
    public function save($entity)
    {
        
        $objectManager = $this->getObjectManager();
        
        //@TODO UnitOfWork::STATE_NEW == $em->getUnitOfWork()->getEntityState($entity) 
        //@see http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/working-with-objects.html#entity-state
        $objectManager->persist($entity);
        $objectManager->flush();
    }
    
    /**
     * Deletes an entity
     * 
     * @param \DlcDoctrine\Entity\AbstractEntity $entity
     */
    public function remove($entity)
    {
        $objectManager = $this->getObjectManager();
        $objectManager->remove($entity);
        $objectManager->flush();
    }
}