<?php

namespace Diside\SecurityBundle\Gateway\ORM;

use Diside\SecurityBundle\Entity\Page;

class ORMPageGateway extends AbstractORMPageGateway
{
    protected function buildPage()
    {
        return new Page();
    }

    protected function getPageRepository()
    {
        return $this->getRepository('DisideSecurityBundle:Page');
    }

    protected function getUserRepository()
    {
        return $this->getRepository('DisideSecurityBundle:User');
    }

}