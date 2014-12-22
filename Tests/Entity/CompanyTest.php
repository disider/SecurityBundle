<?php

namespace Diside\SecurityBundle\Tests\Entity;

use Diside\SecurityBundle\Entity\Company;
use Diside\SecurityBundle\Tests\EntityTest;
use Diside\SecurityComponent\Model\Company as CompanyModel;
use Diside\SecurityComponent\Model\User as Model;

class CompanyTest extends EntityTest
{
    /**
     * @test
     */
    public function testToModel()
    {
        $model = new CompanyModel(2, 'Acme');
        $entity = new Company();
        $entity->fromModel($model);

        $expected = $entity->toModel();

        $this->assertField($expected, $model, 'name');
    }
}