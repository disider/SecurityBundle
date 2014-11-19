<?php


namespace Diside\SecurityBundle\Gateway\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Diside\SecurityComponent\Gateway\CompanyGateway;
use Diside\SecurityComponent\Model\Company as CompanyModel;
use Diside\SecurityBundle\Entity\Company as CompanyEntity;

class ORMCompanyGateway implements CompanyGateway
{
    /** @var EntityManager */
    private $objectManager;

    /** @var EntityRepository */
    private $repository;

    public function getName()
    {
        return self::NAME;
    }

    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;

        $this->repository = $objectManager->getRepository('DisideSecurityBundle:Company');
    }

    public function save(CompanyModel $model)
    {
        $entity = new CompanyEntity();

        if ($model->getId() != null) {
            $entity = $this->repository->findOneById($model->getId());
        }

        $entity->setName($model->getName());

        $this->objectManager->persist($entity);
        $this->objectManager->flush();

        return $this->convertEntity($entity);
    }

    public function delete($id)
    {
        $entity = $this->repository->findOneById($id);

        $this->objectManager->remove($entity);
        $this->objectManager->flush();

        return $this->convertEntity($entity);
    }

    public function findOneByName($name)
    {
        return $this->convertEntity($this->repository->findOneByName($name));
    }

    public function findAll(array $filters = array(), $pageIndex = 0, $pageSize = PHP_INT_MAX)
    {
        $qb = $this->findAllQuery($filters, $pageIndex, $pageSize);

        return $this->convertEntities($qb->getQuery()->execute());
    }

    public function findOneById($id)
    {
        return $this->convertEntity($this->repository->findOneById($id));
    }

    public function countAll(array $filters = array())
    {
        $qb = $this->findAllQuery($filters);
        $qb->select('COUNT(c.id)');

        return $qb->getQuery()->getSingleScalarResult();
    }

    private function convertEntities($entities)
    {
        $models = array();
        foreach ($entities as $entity)
            $models[] = $this->convertEntity($entity);

        return $models;
    }

    private function convertEntity(CompanyEntity $entity = null)
    {
        if ($entity != null) {
            $model = new CompanyModel($entity->getId(), $entity->getName());

            return $model;
        }

        return null;
    }

    private function findAllQuery(array $filters, $pageIndex = 0, $pageSize = PHP_INT_MAX)
    {
        $qb = $this->repository->createQueryBuilder('c')
            ->setFirstResult($pageIndex * $pageSize)
            ->setMaxResults($pageSize);

        return $qb;
    }
}