<?php

namespace Diside\SecurityBundle\Tests\Entity;

use Diside\SecurityBundle\Entity\Page as Entity;
use Diside\SecurityBundle\Tests\EntityTest;
use Diside\SecurityComponent\Model\Page as Model;
use Diside\SecurityComponent\Model\PageTranslation;

class PageTest extends EntityTest
{
    /**
     * @test
     */
    public function testConversion()
    {
        $model = new Model(null);
        $model->addTranslation(new PageTranslation(null, 'en', 'url', 'title', 'content'));

        $entity = new Entity();
        $entity->fromModel($model);

        /** @var Model $converted */
        $converted = $entity->toModel();

        $this->assertThat($converted->countTranslations(), $this->equalTo(1));
    }
}