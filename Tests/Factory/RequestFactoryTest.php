<?php

namespace Diside\SecurityBundle\Tests\Factory;

use Diside\SecurityBundle\Tests\Mock\DummyToken;
use Mockery as m;
use Diside\SecurityBundle\Factory\RequestFactory;
use Diside\SecurityComponent\Model\User;
use Diside\SecurityBundle\Form\Data\RegistrationFormData;
use Symfony\Component\Security\Core\SecurityContextInterface;

class RequestFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var SecurityContextInterface */
    private $securityContext;

    /** @var RequestFactory */
    private $factory;

    /**
     * @before
     */
    public function setUp()
    {
        $encoder = m::mock('Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface');
        $encoder->shouldReceive('encodePassword')
            ->andReturn('encodedPassword');

        $encoderFactory = m::mock('Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface');
        $encoderFactory->shouldReceive('getEncoder')
            ->andReturn($encoder);

        $this->securityContext = m::mock('Symfony\Component\Security\Core\SecurityContextInterface');
        $this->factory = new RequestFactory($this->securityContext, $encoderFactory);
    }

    /**
     * @test
     */
    public function testCreateUser()
    {
        $this->givenLoggedAdmin();

        $user = $this->givenUser();
        $request = $this->factory->create('save_user', $user, array('set_password' => false, 'set_company' => false));

        $this->assertInstanceOf('\Diside\SecurityComponent\Interactor\Request\SaveUserRequest', $request);
    }

    /**
     * @test
     */
    public function testRegisterUser()
    {
        $user = $this->givenUser();
        $data = new RegistrationFormData();
        $data->setEmail('user@example.com');
        $data->setPassword('password');
        $request = $this->factory->create('register_user', $data, array('user' => $user));

        $this->assertInstanceOf('\Diside\SecurityComponent\Interactor\Request\RegisterUserRequest', $request);
    }

    protected function givenLoggedAdmin()
    {
        $admin = new User(1, 'admin@example.com', '', '');
        $admin->addRole(User::ROLE_ADMIN);

        $this->securityContext->shouldReceive('getToken')
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