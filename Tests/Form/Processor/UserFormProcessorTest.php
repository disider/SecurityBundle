<?php


namespace Diside\SecurityBundle\Tests\Form\Processor;

use Diside\SecurityBundle\Entity\User as UserEntity;
use Diside\SecurityBundle\Entity\Company as CompanyEntity;
use Diside\SecurityBundle\Factory\EntityFactory;
use Diside\SecurityBundle\Factory\RequestFactory;
use Diside\SecurityBundle\Form\Processor\UserFormProcessor;
use Diside\SecurityBundle\Security\PermissionChecker;
use Diside\SecurityBundle\Tests\FormProcessorTestCase;
use Diside\SecurityBundle\Tests\Mock\InteractorMock;
use Diside\SecurityComponent\Interactor\InteractorFactory;
use Diside\SecurityComponent\Interactor\SecurityInteractorRegister;
use Diside\SecurityComponent\Model\User;
use Mockery as m;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

class UserFormProcessorTest extends FormProcessorTestCase
{
    /** @var PasswordEncoderInterface */
    private $encoder;

    protected function getFormName()
    {
        return 'user';
    }

    protected function buildProcessor(
        FormFactoryInterface $formFactory,
        InteractorFactory $interactorFactory,
        SecurityContextInterface $securityContext,
        EntityFactory $entityFactory,
        RequestFactory $requestFactory,
        PermissionChecker $permissionChecker
    ) {
        $this->encoder = m::mock('Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface');

        $encoderFactory = m::mock('Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface');
        $encoderFactory->shouldReceive('getEncoder')
            ->andReturn($this->encoder);

        return new UserFormProcessor(
            $formFactory,
            $interactorFactory,
            $securityContext,
            $entityFactory,
            $requestFactory,
            $permissionChecker
        );
    }

    /**
     * @test
     */
    public function testConstructor()
    {
        $this->assertNull($this->processor->getUser());
        $this->assertNull($this->processor->getErrors());
        $this->assertFalse($this->processor->hasErrors());
    }

    /**
     * @test
     * @expectedException \Diside\SecurityBundle\Exception\UnauthorizedException
     */
    public function whenProcessingRequestAndUserIsAnonymous_thenThrow()
    {
        $this->givenNotAuthorized();

        $request = new Request();

        $this->processor->process($request);
    }

    /**
     * @test
     */
    public function whenProcessingWithNoData_thenIsNotValid()
    {
        $this->givenLoggedUser();
        $this->givenInvalidData();

        $request = $this->givenPostRequest();

        $this->processor->process($request);
        $this->assertFalse($this->processor->hasErrors());
    }

    /**
     * @test
     */
    public function whenProcessing_thenFormDataIsSet()
    {
        $this->givenLoggedUser();
        $this->givenInvalidData();

        $expect = $this->expectFormDataIsSet();

        $request = $this->givenPostRequest();

        $this->processor->process($request);

        $expect->verify();
    }

    /**
     * @test
     */
    public function whenProcessingValidForm_thenHasNoErrors()
    {
        $this->givenLoggedUser();
        $this->givenEncodedPassword();

        $user = $this->givenUser();
        $interactor = new InteractorMock($user, 'setUser');

        $this->expectInteractorFor($interactor, SecurityInteractorRegister::SAVE_USER);

        $request = $this->givenValidData($user);

        $this->setPermission('has_same_company', true);
        $this->setPermission('set_password', true);
        $this->setPermission('set_company', true);

        $this->processor->process($request);

        $user = $this->processor->getUser();

        $this->assertNotNull($user);
        $this->assertFalse($this->processor->hasErrors());
        $this->assertTrue($this->processor->isValid());
    }

    /**
     * @test
     */
    public function whenProcessingExistingUser_thenSaveUser()
    {
        $admin = $this->givenAdmin();
        $this->buildLoggedUser($admin);

        $this->givenEncodedPassword();

        $user = $this->givenUser();
        $user->setPassword('password');
        $interactor = new InteractorMock($user, 'setUser');

        $expect1 = $this->expectInteractorFor($interactor, SecurityInteractorRegister::GET_USER);
        $expect2 = $this->expectInteractorFor($interactor, SecurityInteractorRegister::SAVE_USER);

        $this->setPermission('can_edit', true);
        $this->setPermission('set_password', true);
        $this->setPermission('set_company', true);

        $request = $this->givenValidData($user);

        $this->processor->process($request, 1);

        $expect1->verify();
        $expect2->verify();
    }

    /**
     * @test
     */
    public function whenProcessingValidFormButInteractorFails_thenHasErrors()
    {
        $this->givenLoggedUser();
        $user = $this->givenUser();
        $this->givenEncodedPassword();

        $this->givenErrorInteractorFor(SecurityInteractorRegister::SAVE_USER);

        $request = $this->givenValidData($user);
        $this->setPermission('has_same_company', true);
        $this->setPermission('set_password', false);
        $this->setPermission('set_company', false);

        $this->processor->process($request);

        $this->assertTrue($this->processor->hasErrors());

        $errors = $this->processor->getErrors();
        $this->assertThat($errors[0], $this->equalTo('Error'));
    }

    private function givenEncodedPassword($password = '1234')
    {
        $this->encoder->shouldReceive('encodePassword')
            ->andReturn($password);
    }

    protected function mockEntities()
    {
        $this->mockEntity('user', new UserEntity);
        $this->mockEntity('company', new CompanyEntity);
    }

    protected function buildFormData()
    {
        return new UserEntity();
    }

}
