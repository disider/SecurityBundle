<?php


namespace Diside\SecurityBundle\Gateway\ORM;

use Diside\SecurityBundle\Entity\User;
use Diside\SecurityComponent\Gateway\UserGateway;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

class ORMUserGateway extends AbstractORMUserGateway
{
    protected function buildUser()
    {
        return new User();
    }

    protected function getUserRepository()
    {
        return $this->getRepository('DisideSecurityBundle:User');
    }

    protected function getCompanyRepository()
    {
        return $this->getRepository('DisideSecurityBundle:Company');
    }

    protected function convertEntity($entity)
    {
        if($entity == null)
            return null;

        return $entity->toModel();
    }
}