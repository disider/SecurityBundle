<?php


namespace Diside\SecurityBundle\Gateway\ORM;

use Diside\SecurityComponent\Gateway\Gateway;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

abstract class AbstractORMBaseGateway implements Gateway
{
    /** @var EntityManager */
    private $objectManager;

    /** @return QueryBuilder */
    abstract protected function findAllQuery(array $filters, $pageIndex = 0, $pageSize = PHP_INT_MAX);

    abstract protected function convertEntity($entity);

    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function findAll(array $filters = array(), $pageIndex = 0, $pageSize = PHP_INT_MAX)
    {
        $qb = $this->findAllQuery($filters, $pageIndex, $pageSize);

        return $this->convertEntities($qb->getQuery()->execute());
    }

    public function countAll(array $filters = array())
    {
        $qb = $this->findAllQuery($filters);

        $rootAliases = $qb->getRootAliases();

        return $qb->select(sprintf('COUNT(%s.id)', $rootAliases[0]))
            ->getQuery()->getSingleScalarResult();
    }

    protected function persistAndFlush($entity)
    {
        $this->objectManager->persist($entity);
        $this->objectManager->flush();
    }

    protected function removeAndFlush($entity)
    {
        $this->objectManager->remove($entity);
        $this->objectManager->flush();
    }


    protected function getRepository($name)
    {
        return $this->objectManager->getRepository($name);
    }

    protected function prePersist($entity, $model)
    {
        $entity->setName($model->getName());

        return $entity;
    }

    protected function convertEntities($entities)
    {
        $models = array();
        foreach ($entities as $entity)
            $models[] = $this->convertEntity($entity);

        return $models;
    }

    protected function getObjectManager()
    {
        return $this->objectManager;
    }
}