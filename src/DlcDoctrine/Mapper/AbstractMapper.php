<?php
namespace DlcDoctrine\Mapper;

use DlcBase\Mapper\AbstractMapper as AbstractBaseMapper;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
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
    const JOIN_ALIAS_PREFIX = 'joined_';

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
     * return ObjectManager
     * @return EntityManager
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
     * Returns the column identifier for the order by property
     *
     * @param sting $orderBy
     * @throws \InvalidArgumentException
     * @return string
     */
    public function getOrderByIdentifier($orderBy)
    {
        $objectManager = $this->getObjectManager();
        $classMetadata = $objectManager->getClassMetadata($this->getEntityClass());

        if (strpos($orderBy, '::') === false) {

            if (!$classMetadata->hasField($orderBy)) {
                throw new \InvalidArgumentException(sprintf(
                    'Invalid order by column: Entity "%s" has no field "%s"',
                    $this->getEntityClass(),
                    $orderBy
                ));
            }

            $orderByIdentifier = $this->getEntityClassAlias()
                               . '.'
                               . $orderBy;
        } else {
            list($association, $assocProperty) = explode('::', $orderBy);

            if (!$classMetadata->hasAssociation($association)) {
                throw new \InvalidArgumentException(sprintf(
                    'Invalid order by column: Entity "%s" has no association "%s"',
                    $this->getEntityClass(),
                    $association
                ));
            }

            if (in_array($assocProperty, array('count'))) {
                //@FIXME
                $orderByIdentifier = $this->getEntityClassAlias()
                                   . '.id';

            } else {
                $assocTargetClass = $classMetadata->getAssociationTargetClass($association);

                if (!$objectManager->getClassMetadata($assocTargetClass)->hasField($assocProperty)) {
                    throw new \InvalidArgumentException(sprintf(
                        'Invalid order by column: Entity "%s" has no field "%s"',
                        $assocTargetClass,
                        $assocProperty
                    ));
                }

                $joinAlias         = self::JOIN_ALIAS_PREFIX . $association;
                $orderByIdentifier = $joinAlias . '.' . $assocProperty;
            }
        }

        return $orderByIdentifier;
    }

    /**
     * Adds joins for all association mappings to the query builder
     *
     * @param QueryBuilder $queryBuilder
     */
    protected function addJoinTablesToQueryBuilder(QueryBuilder $queryBuilder)
    {
        $objectManager = $this->getObjectManager();
        $entityAlias   = $this->getEntityClassAlias();
        $classMetaData = $objectManager->getClassMetadata($this->getEntityClass());

        $assocMappings = $classMetaData->getAssociationMappings();

        foreach ($assocMappings as $fieldName => $assocMapping) {
            $join  = $entityAlias . '.' . $fieldName;
            $alias = self::JOIN_ALIAS_PREFIX . $fieldName;

            $queryBuilder->addSelect($alias);
            $queryBuilder->leftJoin($join, $alias);
        }
    }

    /**
     * Adds filter conditions to query bilder
     *
     * @param array $filter
     * @param QueryBuilder $queryBuilder
     * @throws \InvalidArgumentException
     * @return integer
     */
    protected function addFilterToQueryBuilder(array $filter, QueryBuilder $queryBuilder)
    {
        $objectManager    = $this->getObjectManager();
        $entityClassAlias = $this->getEntityClassAlias();
        $classMetaData    = $objectManager->getClassMetadata($this->getEntityClass());
        $assocMappings    = $classMetaData->getAssociationMappings();

        $andExpressions = array();
        $paramCounter   = 0;

        foreach ($filter as $property => $value) {
            if (empty($value)) {
                continue;
            }

            if (!isset($assocMappings[$property])) {
                throw new \InvalidArgumentException('Unkown association "' . $property . '"');
            }
            $paramCounter++;
            $queryBuilder->andWhere($queryBuilder->expr()->eq($entityClassAlias . '.' .$property, '?' . $paramCounter));
            $queryBuilder->setParameter($paramCounter, $value);

        }

        return $paramCounter;
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
     * @param null|array $filter
     * @return \Zend\Paginator\Paginator
     */
    public function pagination($page, $limit, $query = null, $orderBy = null, $sort = 'ASC', $filter = null)
    {
        $entityClassAlias = $this->getEntityClassAlias();

        // Create a Doctrine Collection
        $queryBuilder = $this->getObjectManager()->createQueryBuilder();
        $queryBuilder->select($entityClassAlias)
                     ->from($this->getEntityClass(), $entityClassAlias);

        $paramCounter = 0;

        if (is_array($filter)) {
            $paramCounter = $this->addFilterToQueryBuilder($filter, $queryBuilder);
        }

        //Add joins to query builder
        $this->addJoinTablesToQueryBuilder($queryBuilder);

        if (null !== $query) {
            $properties    = $this->getQueryableProperties();
            $orExpressions = array();
            $paramCounter++;

            foreach ($properties as $property) {
                $orExpressions[] = $queryBuilder->expr()->like($property, '?' . $paramCounter);
            }

            $queryBuilder->andWhere(
                call_user_func_array(array($queryBuilder->expr(), "orX"), $orExpressions)
            );

            $queryBuilder->setParameter($paramCounter, $query);
        }

        if ($orderBy) {
            $orderByIdentifier = $this->getOrderByIdentifier($orderBy);

            if (!$sort) {
                $sort = 'ASC';
            }
            $queryBuilder->orderBy($orderByIdentifier, $sort);
        }

        // Create the paginator itself
        $paginator = new Paginator(
            new DoctrinePaginator(new ORMPaginator($queryBuilder))
        );

        $paginator->setCurrentPageNumber($page)
                  ->setItemCountPerPage($limit)
                  ->setPageRange(5);

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