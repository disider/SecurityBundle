<?php


namespace Diside\SecurityBundle\Tests\Form\Processor;

use Mockery as m;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use SecurityComponent\Interactor\Interactor;
use SecurityComponent\Interactor\InteractorFactory;
use SecurityComponent\Interactor\Presenter;
use SecurityComponent\Interactor\Request as InteractorRequest;
use SecurityComponent\Model\User;
use Diside\SecurityBundle\Security\LoggedUser;
use Diside\SecurityBundle\Form\Data\UserFormData;
use Diside\SecurityBundle\Form\Processor\UserFormProcessor;
use Diside\SecurityBundle\Tests\Mock\DummyToken;
use Diside\SecurityBundle\Tests\Mock\ErrorInteractor;

class UserFormProcessorTest extends WebTestCase
{
    /** @var UserFormProcessor */
    private $processor;

    /** @var FormInterface */
    private $form;

    /** @var InteractorFactory */
    private $interactorFactory;

    /** @var SecurityContextInterface */
    private $securityContext;

    /** @var PasswordEncoderInterface */
    private $encoder;

    protected function setUp()
    {
        $this->securityContext = m::mock('Symfony\Component\Security\Core\SecurityContextInterface');

        $this->interactorFactory = m::mock('SecurityComponent\Interactor\InteractorFactory');

        $this->form = m::mock('Symfony\Component\Form\Form');
        $this->form->shouldReceive('handleRequest');
        $this->form->shouldReceive('setData');
        $button = m::mock('Symfony\Component\Form\SubmitButton');
        $button->shouldReceive('isClicked');
        $this->form->shouldReceive('get')->andReturn($button);
        $this->form->shouldReceive('has');

        $formFactory = m::mock('Symfony\Component\Form\FormFactoryInterface');
        $formFactory->shouldReceive('create')
            ->andReturn($this->form);

        $this->encoder = m::mock('Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface');

        $encoderFactory = m::mock('Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface');
        $encoderFactory->shouldReceive('getEncoder')
            ->andReturn($this->encoder);

        $this->processor = new UserFormProcessor($formFactory, $this->interactorFactory, $this->securityContext, $encoderFactory);
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
        $this->securityContext
            ->shouldReceive('isGranted')
            ->andReturn(false);

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

        $request = $this->buildRequest();

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

        $expect = $this->form->mockery_findExpectation('setData', array());
        $expect->once();

        $request = $this->buildRequest();

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
        $interactor = new UserInteractorMock($user);

        $this->interactorFactory->shouldReceive('get')
            ->with(InteractorFactory::SAVE_USER)
            ->andReturn($interactor);

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
        $this->givenLoggedUser();
        $user = $this->givenUser();
        $this->givenEncodedPassword();

        $interactor = new UserInteractorMock($user);

        $expect1 = $this->interactorFactory->shouldReceive('get')
            ->with(InteractorFactory::GET_USER)
            ->andReturn($interactor)
            ->once()
        ;

        $expect2 = $this->interactorFactory->shouldReceive('get')
            ->with(InteractorFactory::SAVE_USER)
            ->andReturn($interactor)
            ->once()
        ;

        $request = $this->givenValidData($user, 'password');

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
        $interactor = new UserInteractorMock($user);

        $this->interactorFactory->shouldReceive('get')
            ->with(InteractorFactory::GET_USER)
            ->andReturn($interactor)
        ;

        $this->interactorFactory->shouldReceive('get')
            ->with(InteractorFactory::SAVE_USER)
            ->andReturn($interactor)
            ->once()
        ;

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
        $expect = $this->encoder->shouldReceive('encodePassword')
            ->never()
        ;

        $interactor = new UserInteractorMock($user);

        $this->interactorFactory->shouldReceive('get')
            ->with(InteractorFactory::GET_USER)
            ->andReturn($interactor)
        ;

        $this->interactorFactory->shouldReceive('get')
            ->with(InteractorFactory::SAVE_USER)
            ->andReturn($interactor)
            ->once()
        ;

        $request = $this->givenValidData($user, null);

        $this->processor->process($request, 1);

        $interactorRequest = $interactor->getRequest();
        $this->assertNull($interactorRequest->password);
        $expect->verify();
    }

    /**
     * @test
     */
    public function whenPasswordIsNotEmpty_thenProcessPassword()
    {
        $encodedPassword = '12345678';
        $this->givenLoggedUser();
        $user = $this->givenUser();

        $this->givenEncodedPassword($encodedPassword);

        $interactor = new UserInteractorMock($user);

        $this->interactorFactory->shouldReceive('get')
            ->with(InteractorFactory::GET_USER)
            ->andReturn($interactor)
        ;

        $this->interactorFactory->shouldReceive('get')
            ->with(InteractorFactory::SAVE_USER)
            ->andReturn($interactor)
            ->once()
        ;

        $request = $this->givenValidData($user, 'newpassword');


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

        $interactor = new ErrorInteractor('Error');

        $this->interactorFactory->shouldReceive('get')
            ->with(InteractorFactory::SAVE_USER)
            ->andReturn($interactor);

        $request = $this->givenValidData($user);

        $this->processor->process($request);

        $this->assertTrue($this->processor->hasErrors());

        $errors = $this->processor->getErrors();
        $this->assertThat($errors[0], $this->equalTo('Error'));
    }

    private function givenUser()
    {
        return new User(2, 'test@example.com', 'password', '');
    }

    private function givenLoggedUser($user = null)
    {
        if(!$user) {
            $user = new User(1, 'adam@example.com', 'password', 'salt');
            $user->addRole(User::ROLE_ADMIN);
        }

        $loggedUser = new LoggedUser($user);

        $token = new DummyToken($loggedUser);

        $this->securityContext
            ->shouldReceive('isGranted')
            ->andReturn(true);

        $this->securityContext
            ->shouldReceive('getToken')
            ->once()
            ->andReturn($token);

        return $loggedUser;
    }

    private function givenValidData(User $user, $password = null)
    {
        $data = new UserFormData($user, array());
        $data->setPassword($password);

        $this->givenValidForm($data);

        return $this->givenPostRequest($data);
    }

    private function givenInvalidData()
    {
        $this->form
            ->shouldReceive('isValid')
            ->once()
            ->andReturn(false);

        return $this->givenPostRequest(array());
    }

    private function givenPostRequest($data)
    {
        $request = new Request(array(), array('user' => $data));
        $request->setMethod('POST');

        return $request;
    }

    private function givenValidForm(UserFormData $data)
    {
        $this->form
            ->shouldReceive('isValid')
            ->once()
            ->andReturn(true);

        $this->form
            ->shouldReceive('getData')
            ->once()
            ->andReturn($data);
    }

    private function givenEncodedPassword($password = '1234')
    {
        $this->encoder->shouldReceive('encodePassword')
            ->andReturn($password)
        ;
    }

    private function buildRequest()
    {
        $request = new Request(array(), array());
        $request->setMethod('POST');
        return $request;
    }

}

class UserInteractorMock implements Interactor
{
    /** @var User */
    private $user;

    /** @var InteractorRequest */
    private $request;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function process(InteractorRequest $request, Presenter $presenter)
    {
        $this->request = $request;

        /** @var UserPresenter $presenter */
        $presenter->setUser($this->user);
    }

    public function getRequest()
    {
        return $this->request;
    }
}
