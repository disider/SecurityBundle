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
use SecurityComponent\Interactor\Presenter\UserPresenter;
use SecurityComponent\Interactor\Request as InteractorRequest;
use SecurityComponent\Model\User;
use Diside\SecurityBundle\Form\Data\ChangePasswordFormData;
use Diside\SecurityBundle\Form\Processor\ChangePasswordFormProcessor;
use Diside\SecurityBundle\Tests\Mock\DummyToken;
use Diside\SecurityBundle\Tests\Mock\ErrorInteractor;

class ChangePasswordFormProcessorTest extends WebTestCase
{
    /** @var ChangePasswordFormProcessor */
    private $processor;

    /** @var FormInterface */
    private $form;

    /** @var InteractorFactory */
    private $interactorFactory;

    /** @var SecurityContextInterface */
    private $securityContext;

    protected function setUp()
    {
        $this->form = m::mock('Symfony\Component\Form\Form');
        $this->form->shouldReceive('handleRequest');
        $this->form->shouldReceive('setData');

        $formFactory = m::mock('Symfony\Component\Form\FormFactoryInterface');
        $formFactory->shouldReceive('create')
            ->andReturn($this->form);

        $this->interactorFactory = m::mock('SecurityComponent\Interactor\InteractorFactory');

        $this->securityContext = m::mock('Symfony\Component\Security\Core\SecurityContextInterface');

        $encoder = m::mock('Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface');
        $encoder->shouldReceive('encodePassword');

        $encoderFactory = m::mock('Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface');
        $encoderFactory->shouldReceive('getEncoder')
            ->andReturn($encoder);

        $this->processor = new ChangePasswordFormProcessor($formFactory, $this->interactorFactory, $this->securityContext, $encoderFactory);
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
     */
    public function whenProcessingWithNoData_thenIsNotValid()
    {
        $user = $this->givenUser();
        $this->givenInvalidData();

        $request = $this->build();

        $this->processor->process($request, $user->getId());
        $this->assertFalse($this->processor->hasErrors());
        $this->assertFalse($this->processor->isValid());
    }

    /**
     * @test
     */
    public function whenProcessingValidForm_thenHasNoErrors()
    {
        $user = $this->givenUser();

        $interactor = new ChangePasswordUserInteractorMock($user);

        $this->interactorFactory->shouldReceive('get')
            ->with(InteractorFactory::GET_USER)
            ->andReturn($interactor);

        $this->interactorFactory->shouldReceive('get')
            ->with(InteractorFactory::SAVE_USER)
            ->andReturn($interactor);

        $request = $this->givenValidData();

        $this->processor->process($request, $user->getId());

        $user = $this->processor->getUser();

        $this->assertNotNull($user);
        $this->assertFalse($this->processor->hasErrors());
        $this->assertTrue($this->processor->isValid());
    }

    /**
     * @test
     */
    public function whenProcessingValidFormButInteractorFails_thenHasErrors()
    {
        $user = $this->givenUser();

        $interactor = new ErrorInteractor('Undefined');

        $this->interactorFactory->shouldReceive('get')
            ->with(InteractorFactory::SAVE_USER)
            ->andReturn($interactor);

        $request = $this->givenValidData();

        $this->processor->process($request, $user->getId());

        $this->assertTrue($this->processor->hasErrors());

        $errors = $this->processor->getErrors();
        $this->assertThat($errors[0], $this->equalTo('Undefined'));
    }

    private function build()
    {
        $request = new Request(array(), array());
        $request->setMethod('POST');
        return $request;
    }

    private function givenUser()
    {
        $user = new User(null, 'test@example.com', 'password', '');

        $token = new DummyToken($user);

        $this->securityContext
            ->shouldReceive('isGranted')
            ->andReturn(true);

        $this->securityContext
            ->shouldReceive('getToken')
            ->once()
            ->andReturn($token);

        return $user;
    }

    private function givenValidData()
    {
        $user = $this->givenUser();
        $data = new ChangePasswordFormData($user);

        $this->givenValidForm($data);

        return $this->givenPost($data);
    }

    private function givenInvalidData()
    {
        $this->form
            ->shouldReceive('isValid')
            ->once()
            ->andReturn(false);

        return $this->givenPost(array());
    }

    private function givenPost($data)
    {
        $request = new Request(array(), array('change_password' => $data));
        $request->setMethod('POST');

        return $request;
    }

    private function givenValidForm(ChangePasswordFormData $data)
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

}

class ChangePasswordUserInteractorMock implements Interactor
{
    /** @var User */
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function process(InteractorRequest $request, Presenter $presenter)
    {
        /** @var UserPresenter $presenter */
        $presenter->setUser($this->user);
    }
}
