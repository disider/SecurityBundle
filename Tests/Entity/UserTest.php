<?php

namespace Diside\SecurityBundle\Tests\Entity;

use Diside\SecurityBundle\Entity\User;
use Diside\SecurityBundle\Entity\Company;
use Diside\SecurityBundle\Tests\EntityTest;
use Diside\SecurityComponent\Model\User as Model;
use Diside\SecurityComponent\Model\Company as CompanyModel;

class UserTest extends EntityTest
{
    /**
     * @test
     */
    public function testConstructor()
    {
        $user = new User();
        $this->assertNotNull($user->getSalt());
    }

    /**
     * @test
     */
    public function testConversion()
    {
        $model = new Model(1, 'adam@example.com', 'password', 'salt');

        $entity = new User();
        $entity->fromModel($model);

        $expected = $entity->toModel();

        $this->assertField($expected, $model, 'email');
        $this->assertField($expected, $model, 'password');
        $this->assertField($expected, $model, 'salt');
        $this->assertField($expected, $model, 'active');
        $this->assertField($expected, $model, 'roles');
        $this->assertField($expected, $model, 'registrationToken');
        $this->assertField($expected, $model, 'resetPasswordToken');
    }
}