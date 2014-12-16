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
        $model = new Model(null, 'en', 'url', 'title', 'content');
        $model->addTranslation(new PageTranslation(null, 'it', 'it/url', 'titolo', 'contenuto'));

        $entity = new Entity();
        $entity->fromModel($model);

        /** @var Model $converted */
        $converted = $entity->toModel();

        $this->assertField($converted, $model, 'language');
        $this->assertField($converted, $model, 'url');
        $this->assertField($converted, $model, 'title');
        $this->assertField($converted, $model, 'content');
        $this->assertThat($converted->countTranslations(), $this->equalTo(1));
    }
}