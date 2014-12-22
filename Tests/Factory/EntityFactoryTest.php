<?php

namespace Diside\SecurityBundle\Tests\Factory;

use Diside\SecurityBundle\Factory\EntityFactory;
use Diside\SecurityBundle\Tests\Mock\DummyToken;
use Mockery as m;
use Diside\SecurityBundle\Factory\RequestFactory;
use Diside\SecurityComponent\Model\User;
use Symfony\Component\Security\Core\SecurityContextInterface;

class EntityFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var SecurityContextInterface */
    private $securityContext;

    /** @var EntityFactory */
    private $factory;

    /**
     * @before
     */
    public function setUp()
    {
        $this->securityContext = m::mock('Symfony\Component\Security\Core\SecurityContextInterface');
        $this->factory = new EntityFactory($this->securityContext);

        $this->factory->register('user', 'Diside\SecurityBundle\Entity\User');
    }

    /**
     * @test
     * @expectedException \Diside\SecurityBundle\Exception\UndefinedFactoryException
     */
    public function testUnknownClass()
    {
        $this->assertNull($this->factory->getClass('unknown'));

        $this->factory->create('unknown');
    }

    /**
     * @test
     */
    public function testCreateNew()
    {
        $user = $this->factory->create('user');

        $this->assertThat($this->factory->getClass('user'), $this->equalTo('Diside\SecurityBundle\Entity\User'));
    }

    /**
     * @test
     */
    public function testCreateFromModel()
    {
        $user = $this->givenUser();
        $user = $this->factory->create('user', $user);
    }

    /**
     * @return User
     */
    protected function givenUser()
    {
        return new User(2, 'user@example.com', 'password', 'salt');
    }
}