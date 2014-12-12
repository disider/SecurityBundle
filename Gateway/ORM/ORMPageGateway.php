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

    public function findOneByLanguageAndUrl($language, $url)
    {
        $qb = $this->createQueryBuilder()
            ->leftJoin(self::ROOT_ALIAS . 'translations', 'translation')
            ->where('translation.language = :language')
            ->andWhere('translation.url = :url')
            ->setParameter('language', $language)
            ->setParameter('url', $url);

        return $this->convertEntity($qb->getQuery()->getOneOrNullResult());
    }

}