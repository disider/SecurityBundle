<?php


namespace Diside\SecurityBundle\Gateway\ORM;

use Diside\SecurityBundle\Entity\User;
use Diside\SecurityComponent\Gateway\UserGateway;
use Diside\SecurityComponent\Model\User as UserModel;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

abstract class AbstractORMUserGateway extends AbstractORMBaseGateway implements UserGateway
{
    /** @var EntityRepository */
    private $userRepository;

    /** @var EntityRepository */
    private $companyRepository;

    abstract protected function buildUser();

    abstract protected function getUserRepository();

    abstract protected function getCompanyRepository();

    public function getName()
    {
        return self::NAME;
    }

    public function __construct(ObjectManager $objectManager)
    {
        parent::__construct($objectManager);

        $this->userRepository = $this->getUserRepository();
        $this->companyRepository = $this->getCompanyRepository();
    }

    public function save(UserModel $model)
    {
        $entity = $this->buildUser();

        if ($model->getId() != null) {
            $entity = $this->userRepository->findOneById($model->getId());
        }

        $this->prePersist($entity, $model);

        $this->persistAndFlush($entity);

        return $this->convertEntity($entity);
    }

    public function delete($id)
    {
        $entity = $this->userRepository->findOneById($id);

        $this->removeAndFlush($entity);

        return $this->convertEntity($entity);
    }

    public function findOneById($id)
    {
        return $this->convertEntity($this->userRepository->findOneById($id));
    }

    public function findOneByEmail($email)
    {
        $qb = $this->userRepository->createQueryBuilder('u')
            ->where('u.email = :email')
            ->setParameter('email', $email);

        $user = $qb->getQuery()->getOneOrNullResult();

        return $this->convertEntity($user);
    }

    public function findOneByRegistrationToken($token)
    {
        return $this->convertEntity($this->userRepository->findOneByRegistrationToken($token));
    }

    public function findOneByResetPasswordToken($token)
    {
        return $this->convertEntity($this->userRepository->findOneByResetPasswordToken($token));
    }

    public function findByIds(array $userIds)
    {
        $entities = $this->userRepository->findById($userIds);

        return $this->convertEntities($entities);
    }

    protected function findAllQuery(array $filters, $pageIndex = 0, $pageSize = PHP_INT_MAX)
    {
        $qb = $this->userRepository->createQueryBuilder('u')
            ->setFirstResult($pageIndex * $pageSize)
            ->setMaxResults($pageSize);

        if (!array_key_exists(self::FILTER_SUPERADMIN, $filters)) {
            $qb = $this->filterOutSuperadmins($qb);
        }

        if (array_key_exists(self::FILTER_BY_COMPANY_ID, $filters)) {
            $qb = $this->filterByCompanyId($filters, $qb);
        }

        if (array_key_exists(self::FILTER_ACTIVE, $filters)) {
            $qb = $this->filterActive($filters, $qb);
        }

        return $qb;
    }

    private function filterByCompanyId($filters, $qb)
    {
        $companyId = $filters[self::FILTER_BY_COMPANY_ID];

        $qb->andWhere('IDENTITY(u.company) = :companyId')
            ->setParameter('companyId', $companyId);

        return $qb;
    }

    private function filterActive($filters, $qb)
    {
        $qb->andWhere('u.isActive = :isActive')
            ->setParameter('isActive', $filters[self::FILTER_ACTIVE] == true);

        return $qb;
    }

    private function filterOutSuperadmins($qb)
    {
        $qb->andWhere('u.roles NOT LIKE :roles')
            ->setParameter('roles', '%' . UserModel::ROLE_SUPERADMIN . '%');

        return $qb;
    }

    protected function prePersist($entity, $model)
    {
        $company = $this->getCompanyRepository()->findOneById($model->getCompanyId());

        $entity->setEmail($model->getEmail());
        $entity->setPassword($model->getPassword());
        $entity->setSalt($model->getSalt());
        $entity->setIsActive($model->isActive());
        $entity->setRoles($model->getRoles());
        $entity->setRegistrationToken($model->getRegistrationToken());
        $entity->setResetPasswordToken($model->getResetPasswordToken());
        $entity->setCompany($company);

        return $entity;
    }

}