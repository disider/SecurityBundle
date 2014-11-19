<?php


namespace Diside\SecurityBundle\Gateway\ORM;

use Diside\SecurityBundle\Entity\Company as CompanyEntity;
use Diside\SecurityComponent\Gateway\CompanyGateway;
use Diside\SecurityComponent\Model\Company as CompanyModel;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

abstract class AbstractORMCompanyGateway extends AbstractORMBaseGateway implements CompanyGateway
{
    /** @var EntityRepository */
    private $companyRepository;

    abstract protected function getCompanyRepository();

    public function getName()
    {
        return self::NAME;
    }

    public function __construct(ObjectManager $objectManager)
    {
        parent::__construct($objectManager);

        $this->companyRepository = $this->getCompanyRepository();
    }

    public function save(CompanyModel $model)
    {
        $entity = new CompanyEntity();

        if ($model->getId() != null) {
            $entity = $this->companyRepository->findOneById($model->getId());
        }

        $entity = $this->prePersist($entity, $model);

        $this->persistAndFlush($entity);

        return $this->convertEntity($entity);
    }

    public function delete($id)
    {
        $entity = $this->companyRepository->findOneById($id);

        $this->removeAndFlush($entity);

        return $this->convertEntity($entity);
    }

    public function findOneByName($name)
    {
        return $this->convertEntity($this->companyRepository->findOneByName($name));
    }

    public function findOneById($id)
    {
        return $this->convertEntity($this->companyRepository->findOneById($id));
    }

    protected function findAllQuery(array $filters, $pageIndex = 0, $pageSize = PHP_INT_MAX)
    {
        $qb = $this->companyRepository->createQueryBuilder('c')
            ->setFirstResult($pageIndex * $pageSize)
            ->setMaxResults($pageSize);

        return $qb;
    }

    protected function prePersist($entity, $model)
    {
        $entity->setName($model->getName());

        return $entity;
    }
}