<?php


namespace Diside\SecurityBundle\Tests\Form\Processor;

use Diside\SecurityBundle\Builder\UserBuilder;
use Diside\SecurityBundle\Tests\Mock\InteractorMock;
use Diside\SecurityBundle\Tests\Mock\UserInteractorMock;
use Diside\SecurityComponent\Interactor\InteractorFactory;
use Mockery as m;
use Diside\SecurityComponent\Interactor\SecurityInteractorRegister;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Diside\SecurityComponent\Model\User;
use Diside\SecurityBundle\Security\LoggedUser;
use Diside\SecurityBundle\Form\Data\UserFormData;
use Diside\SecurityBundle\Form\Processor\UserFormProcessor;
use Diside\SecurityBundle\Tests\Mock\DummyToken;
use Diside\SecurityBundle\Tests\Mock\ErrorInteractor;

class UserFormProcessorTest extends FormProcessorTestCase
{
    /** @var PasswordEncoderInterface */
    private $encoder;


    protected function buildProcessor(FormFactoryInterface $formFactory, InteractorFactory $interactorFactory, SecurityContextInterface $securityContext)
    {
        $this->encoder = m::mock('Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface');

        $encoderFactory = m::mock('Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface');
        $encoderFactory->shouldReceive('getEncoder')
            ->andReturn($this->encoder);

        $userBuilder = new UserBuilder();

        return new UserFormProcessor($formFactory, $interactorFactory, $securityContext, $encoderFactory, $userBuilder);
    }

    protected function buildValidData($object)
    {
        $data = new UserFormData($object, array());
        $data->setPassword($object->getPassword());

        $this->givenValidForm($data);

        return $this->givenPostRequest($data);
    }

    protected function getFormName()
    {
        return 'user';
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

        $user = $this->givenUser();
        $user->setPassword('password');
        $this->givenEncodedPassword();

        $interactor = new InteractorMock($user, 'setUser');

        $expect1 = $this->expectInteractorFor($interactor, SecurityInteractorRegister::GET_USER);
        $expect2 = $this->expectInteractorFor($interactor, SecurityInteractorRegister::SAVE_USER);

        $request = $this->givenValidData($user);

        $this->processor->process($request, 1);

        $expect1->verify();
        $expect2->verify();

        $interactorRequest = $interactor->getRequest();
        $this->assertNotNull($interactorRequest->password);
    }

    /**
     * @test
     */
    public function whenCurrentUserIsSavingHimself_thenProcessNoPassword()
    {
        $user = $this->givenUser();
        $this->givenLoggedUser($user);
        $interactor = new InteractorMock($user, 'setUser');

        $this->expectInteractorFor($interactor, SecurityInteractorRegister::GET_USER);
        $this->expectInteractorFor($interactor, SecurityInteractorRegister::SAVE_USER);

        $request = $this->givenValidData($user);

        $this->processor->process($request, 1);

        $interactorRequest = $interactor->getRequest();
        $this->assertNull($interactorRequest->password);
    }

    /**
     * @test
     */
    public function whenPasswordIsEmpty_thenProcessNoPassword()
    {
        $this->givenLoggedUser();
        $user = $this->givenUser();
        $user->setPassword(null);

        $expect = $this->encoder->shouldReceive('encodePassword')
            ->never()
        ;

        $interactor = new InteractorMock($user, 'setUser');

        $this->expectInteractorFor($interactor, SecurityInteractorRegister::GET_USER);
        $this->expectInteractorFor($interactor, SecurityInteractorRegister::SAVE_USER);

        $request = $this->givenValidData($user);

        $this->processor->process($request, 1);

        $interactorRequest = $interactor->getRequest();
        $this->assertNull($interactorRequest->password);
        $expect->verify();
    }

    /**
     * @test
     */
    public function whenPasswordIsNotEmptyAndUserIsDifferent_thenProcessPassword()
    {
        $admin = $this->givenAdmin();
        $this->buildLoggedUser($admin);

        $user = $this->givenUser();
        $user->setPassword('password');

        $encodedPassword = '12345678';
        $this->givenEncodedPassword($encodedPassword);

        $interactor = new InteractorMock($user, 'setUser');

        $this->expectInteractorFor($interactor, SecurityInteractorRegister::GET_USER);
        $this->expectInteractorFor($interactor, SecurityInteractorRegister::SAVE_USER);

        $request = $this->givenValidData($user);

        $this->processor->process($request, 1);

        $interactorRequest = $interactor->getRequest();
        $this->assertThat($interactorRequest->password, $this->equalTo($encodedPassword));
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

        $this->processor->process($request);

        $this->assertTrue($this->processor->hasErrors());

        $errors = $this->processor->getErrors();
        $this->assertThat($errors[0], $this->equalTo('Error'));
    }

    private function givenEncodedPassword($password = '1234')
    {
        $this->encoder->shouldReceive('encodePassword')
            ->andReturn($password)
        ;
    }

}
