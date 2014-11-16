<?php


namespace Diside\SecurityBundle\Gateway\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use SecurityComponent\Gateway\UserGateway;
use SecurityComponent\Model\User as UserModel;
use Diside\SecurityBundle\Entity\User;

class ORMUserGateway implements UserGateway
{
    /** @var EntityManager */
    private $objectManager;

    /** @var EntityRepository */
    private $repository;

    /** @var EntityRepository */
    private $companyRepository;

    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;

        $this->repository = $objectManager->getRepository('DisideSecurityBundle:User');

        $this->companyRepository = $objectManager->getRepository('DisideSecurityBundle:Company');
    }

    public function save(UserModel $model)
    {
        $entity = new User();

        if ($model->getId() != null) {
            $entity = $this->repository->findOneById($model->getId());
        }

        $entity->setEmail($model->getEmail());
        $entity->setPassword($model->getPassword());
        $entity->setSalt($model->getSalt());
        $entity->setIsActive($model->isActive());
        $entity->setRoles($model->getRoles());
        $entity->setRegistrationToken($model->getRegistrationToken());
        $entity->setResetPasswordToken($model->getResetPasswordToken());

        $company = $this->companyRepository->findOneById($model->getCompanyId());

        $entity->setCompany($company);

        $this->objectManager->persist($entity);
        $this->objectManager->flush();

        return User::toModel($entity);
    }

    public function delete($id)
    {
        $entity = $this->repository->findOneById($id);

        $this->objectManager->remove($entity);
        $this->objectManager->flush();

        return User::toModel($entity);
    }

    public function findOneById($id)
    {
        return User::toModel($this->repository->findOneById($id));
    }

    public function findAll($filters = array(), $pageIndex = 0, $pageSize = PHP_INT_MAX)
    {
        $qb = $this->findAllQuery($filters, $pageIndex, $pageSize);

        return User::toModels($qb->getQuery()->execute());
    }

    public function findOneByEmail($email)
    {
        $qb = $this->repository->createQueryBuilder('u')
            ->where('u.email = :email')
            ->setParameter('email', $email);

        $user = $qb->getQuery()->getOneOrNullResult();

        return User::toModel($user);
    }

    public function findOneByRegistrationToken($token)
    {
        return User::toModel($this->repository->findOneByRegistrationToken($token));
    }

    public function findOneByResetPasswordToken($token)
    {
        return User::toModel($this->repository->findOneByResetPasswordToken($token));
    }

    public function findByIds(array $userIds)
    {
        $entities = $this->repository->findById($userIds);

        return User::toModels($entities);
    }

    public function countAll($filters = array())
    {
        return $this->findAllQuery($filters)
            ->select('COUNT(u.id)')
            ->getQuery()->getSingleScalarResult();
    }

    private function findAllQuery($filters, $pageIndex = 0, $pageSize = PHP_INT_MAX)
    {
        $qb = $this->repository->createQueryBuilder('u')
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

//        if ($companyId != null) {
        $qb->andWhere('IDENTITY(u.company) = :companyId')
            ->setParameter('companyId', $companyId);
//        } else
//            $qb->andWhere('u.company IS NULL');

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
}