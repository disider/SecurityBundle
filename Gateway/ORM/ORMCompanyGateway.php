<?php

namespace Diside\SecurityBundle\Gateway\ORM;

use Diside\SecurityBundle\Entity\Company;

class ORMCompanyGateway extends AbstractORMCompanyGateway
{
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

        return Company::toModel($entity);
    }

}