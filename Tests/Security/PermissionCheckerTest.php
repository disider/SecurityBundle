<?php

namespace Diside\SecurityBundle\Tests\Security;

use Diside\SecurityBundle\Security\PermissionChecker;
use Diside\SecurityBundle\Tests\Mock\DummyToken;
use Diside\SecurityComponent\Model\User;
use Mockery as m;
use Symfony\Component\Security\Core\SecurityContextInterface;

class PermissionCheckerTest extends \PHPUnit_Framework_TestCase
{
    /** @var SecurityContextInterface */
    private $securityContext;

    /**
     * @test
     */
    public function testCheck()
    {
        $securityContext = m::mock('Symfony\Component\Security\Core\SecurityContextInterface');
        $this->givenLoggedAdmin($securityContext);

        $checker = new PermissionChecker($securityContext);

        $user = $this->givenUser();
        $this->assertTrue($checker->check('set_password', $user));
    }

    /**
     * @param $securityContext
     */
    protected function givenLoggedAdmin($securityContext)
    {
        $admin = new User(1, 'admin@example.com', '', '');
        $admin->addRole(User::ROLE_ADMIN);

        $securityContext->shouldReceive('getToken')
            ->andReturn(new DummyToken($admin));
    }

    /**
     * @return User
     */
    protected function givenUser()
    {
        return new User(2, 'user@example.com', '', '');
    }
}