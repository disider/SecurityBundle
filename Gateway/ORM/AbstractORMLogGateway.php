<?php


namespace Diside\SecurityBundle\Gateway\ORM;

use Diside\SecurityBundle\Entity\Log as LogEntity;
use Diside\SecurityComponent\Gateway\LogGateway;
use Diside\SecurityComponent\Model\Log as LogModel;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

abstract class AbstractORMLogGateway extends AbstractORMBaseGateway implements LogGateway
{
    /** @var EntityRepository */
    private $logRepository;

    /** @var EntityRepository */
    private $userRepository;

    abstract protected function buildLog();

    abstract protected function getLogRepository();

    abstract protected function getUserRepository();

    public function getName()
    {
        return self::NAME;
    }

    public function __construct(ObjectManager $objectManager)
    {
        parent::__construct($objectManager);

        $this->logRepository = $this->getLogRepository();
        $this->userRepository = $this->getUserRepository();
    }

    public function save(LogModel $model)
    {
        $entity = $this->buildLog();

        if ($model->getId() != null) {
            $entity = $this->logRepository->findOneById($model->getId());
        }

        $entity = $this->prePersist($entity, $model);

        $this->persistAndFlush($entity);

        return $this->convertEntity($entity);
    }

    protected function findAllQuery(array $filters, $pageIndex = 0, $pageSize = PHP_INT_MAX)
    {
        $qb = $this->logRepository->createQueryBuilder('l')
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

    protected function convertEntity($entity)
    {
        if ($entity != null) {
            return $entity->toModel();
        }

        return null;
    }

    private function filterByCompanyId($companyId, $qb)
    {
        $qb->leftJoin('l.user', 'u');

        $qb->andWhere('IDENTITY(u.company) = :companyId')
            ->setParameter('companyId', $companyId);

        return $qb;
    }

    private function filterByAction($action, $qb)
    {
        return $qb->andWhere('l.action = :action')
            ->setParameter('action', $action);
    }

    protected function prePersist($entity, $model)
    {
        $user = $this->getUserRepository()->findOneById($model->getUserId());

        $entity->setAction($model->getAction());
        $entity->setDetails($model->getDetails());
        $entity->setUser($user);
        $entity->setDate($model->getDate());

        return $entity;
    }

}