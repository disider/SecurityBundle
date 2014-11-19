<?php


namespace Diside\SecurityBundle\Gateway\ORM;

use Diside\SecurityBundle\Entity\Log as LogEntity;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Diside\SecurityComponent\Gateway\LogGateway;
use Diside\SecurityComponent\Model\Log as LogModel;

class ORMLogGateway implements LogGateway
{
    /** @var EntityManager */
    private $objectManager;

    /** @var EntityRepository */
    private $repository;

    /** @var EntityRepository */
    private $userRepository;

    public function getName()
    {
        return self::NAME;
    }

    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;

        $this->repository = $objectManager->getRepository('DisideSecurityBundle:Log');

        $this->userRepository = $objectManager->getRepository('DisideSecurityBundle:User');
    }

    public function save(LogModel $model)
    {
        $entity = new LogEntity();

        if ($model->getId() != null) {
            $entity = $this->repository->findOneById($model->getId());
        }

        $user = $this->userRepository->findOneById($model->getUserId());

        $entity->setAction($model->getAction());
        $entity->setDetails($model->getDetails());
        $entity->setUser($user);
        $entity->setDate($model->getDate());

        $this->objectManager->persist($entity);
        $this->objectManager->flush();

        return $this->convertEntity($entity);
    }

    public function findAll(array $filters = array(), $pageIndex = 0, $pageSize = PHP_INT_MAX)
    {
        $qb = $this->findAllQuery($filters, $pageIndex, $pageSize);

        return $this->convertEntities($qb->getQuery()->execute());
    }

    public function countAll(array $filters = array())
    {
        return $this->findAllQuery($filters)
            ->select('COUNT(l.id)')
            ->getQuery()->getSingleScalarResult();
    }

    private function findAllQuery($filters, $pageIndex = 0, $pageSize = PHP_INT_MAX)
    {
        $qb = $this->repository->createQueryBuilder('l')
            ->setFirstResult($pageIndex * $pageSize)
            ->setMaxResults($pageSize);

        if (array_key_exists(self::FILTER_BY_COMPANY_ID, $filters)) {
            $qb = $this->filterByCompanyId($filters[self::FILTER_BY_COMPANY_ID], $qb);
        }

        if (array_key_exists(self::FILTER_BY_ACTION, $filters)) {
            $qb = $this->filterByAction($filters[self::FILTER_BY_ACTION], $qb);
        }

        return $qb;
    }

    private function convertEntities($entities)
    {
        $models = array();
        foreach ($entities as $entity)
            $models[] = $this->convertEntity($entity);

        return $models;
    }

    private function convertEntity(LogEntity $entity = null)
    {
        if ($entity != null) {
            return LogEntity::toModel($entity);
        }

        return null;
    }

    private function filterByCompanyId($companyId, $qb)
    {
        $qb->leftJoin('l.user', 'u');

//        if ($companyId != null) {
        $qb->andWhere('IDENTITY(u.company) = :companyId')
            ->setParameter('companyId', $companyId);
//        } else
//            $qb->andWhere('u.company IS NULL');

        return $qb;
    }

    private function filterByAction($action, $qb)
    {
        return $qb->andWhere('l.action = :action')
            ->setParameter('action', $action);
    }
}