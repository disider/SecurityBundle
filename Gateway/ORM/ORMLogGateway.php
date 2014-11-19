<?php

namespace Diside\SecurityBundle\Gateway\ORM;

use Diside\SecurityBundle\Entity\Log;

class ORMLogGateway extends AbstractORMLogGateway
{
    protected function buildLog()
    {
        return new Log();
    }

    protected function getLogRepository()
    {
        return $this->getRepository('DisideSecurityBundle:Log');
    }

    protected function getUserRepository()
    {
        return $this->getRepository('DisideSecurityBundle:User');
    }

    protected function convertEntity($entity)
    {
        return Log::toModel($entity);
    }
}