<?php

namespace Diside\SecurityBundle\Gateway\ORM;

use Diside\SecurityBundle\Entity\Page;
use Diside\SecurityComponent\Gateway\PageGateway;
use Diside\SecurityComponent\Model\Page as PageModel;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

abstract class AbstractORMPageGateway extends AbstractORMBaseGateway implements PageGateway
{
    const ROOT_ALIAS = 'p';

    /** @var EntityRepository */
    private $pageRepository;

    /** @var EntityRepository */
    private $userRepository;

    abstract protected function buildPage();

    abstract protected function getPageRepository();

    abstract protected function getUserRepository();

    public function getName()
    {
        return self::NAME;
    }

    public function __construct(ObjectManager $objectManager)
    {
        parent::__construct($objectManager);

        $this->pageRepository = $this->getPageRepository();
        $this->userRepository = $this->getUserRepository();
    }

    public function save(PageModel $model)
    {
        $entity = $this->buildPage();

        if ($model->getId() != null) {
            $entity = $this->pageRepository->findOneById($model->getId());
        }

        $entity = $this->prePersist($entity, $model);

        $this->persistAndFlush($entity);

        return $this->convertEntity($entity);
    }

    public function delete($id)
    {
        $entity = $this->pageRepository->findOneById($id);

        $this->removeAndFlush($entity);

        return $this->convertEntity($entity);
    }

    public function findOneById($id)
    {
        return $this->convertEntity($this->pageRepository->findOneById($id));
    }

    protected function findAllQuery(array $filters, $pageIndex = 0, $pageSize = PHP_INT_MAX)
    {
        $qb = $this->pageRepository->createQueryBuilder(self::ROOT_ALIAS)
            ->setFirstResult($pageIndex * $pageSize)
            ->setMaxResults($pageSize);

        return $qb;
    }

    protected function convertEntity($entity)
    {
        /** @var Page $entity */
        if ($entity != null) {
            return $entity->toModel();
        }

        return null;
    }

    /**
     * @param Page $entity
     * @param PageModel $model
     */
    protected function prePersist($entity, $model)
    {
        $entity->fromModel($model);

        return $entity;
    }

}