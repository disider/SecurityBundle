<?php

namespace Diside\SecurityBundle\Tests\Entity;

use Diside\SecurityBundle\Entity\PageTranslation as Entity;
use Diside\SecurityBundle\Tests\EntityTest;
use Diside\SecurityComponent\Model\PageTranslation as Model;

class PageTranslationTest extends EntityTest
{
    /**
     * @test
     */
    public function testConversion()
    {
        $model = new Model(null, 'en', 'title', 'url', 'content');

        $entity = new Entity();
        $entity->fromModel($model);

        $converted = $entity->toModel();

        $this->assertField($converted, $model, 'language');
        $this->assertField($converted, $model, 'url');
        $this->assertField($converted, $model, 'title');
        $this->assertField($converted, $model, 'content');
    }
}