<?php


namespace Diside\SecurityBundle\Tests\Form\Processor;

use Diside\SecurityBundle\Form\Data\ResetPasswordFormData;
use Diside\SecurityBundle\Form\Processor\ResetPasswordFormProcessor;
use Diside\SecurityBundle\Tests\Mock\ErrorInteractor;
use Diside\SecurityBundle\Tests\Mock\UserInteractorMock;
use Mockery as m;
use Diside\SecurityComponent\Interactor\SecurityInteractorRegister;
use Diside\SecurityComponent\Model\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContextInterface;

class ResetPasswordFormProcessorTest extends WebTestCase
{
    /** @var ResetPasswordFormProcessor */
    private $processor;

    /** @var FormInterface */
    private $form;

    /** @var InteractorFactory */
    private $interactorFactory;

    protected function setUp()
    {
        $this->form = m::mock('Symfony\Component\Form\Form');
        $this->form->shouldReceive('handleRequest');
        $this->form->shouldReceive('setData');

        $formFactory = m::mock('Symfony\Component\Form\FormFactoryInterface');
        $formFactory->shouldReceive('create')
            ->andReturn($this->form);

        $this->interactorFactory = m::mock('Diside\SecurityComponent\Interactor\InteractorFactory');

        $encoder = m::mock('Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface');
        $encoder->shouldReceive('encodePassword');

        $encoderFactory = m::mock('Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface');
        $encoderFactory->shouldReceive('getEncoder')
            ->andReturn($encoder);


        $this->processor = new ResetPasswordFormProcessor($formFactory, $this->interactorFactory, $encoderFactory);
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
     * @expectedException \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function whenProcessingUnknownToken_thenThrow()
    {
        $this->givenInvalidData();

        $interactor = new ErrorInteractor('Undefined');

        $this->interactorFactory->shouldReceive('get')
            ->with(SecurityInteractorRegister::GET_USER)
            ->andReturn($interactor);

        $request = $this->buildRequest();

        $this->processor->process($request, '123');
    }

    /**
     * @test
     */
    public function whenProcessingValidForm_thenHasNoErrors()
    {
        $user = $this->givenUser();

        $interactor = new UserInteractorMock($user);

        $this->interactorFactory->shouldReceive('get')
            ->with(SecurityInteractorRegister::GET_USER)
            ->andReturn($interactor);

        $this->interactorFactory->shouldReceive('get')
            ->with(SecurityInteractorRegister::RESET_PASSWORD)
            ->andReturn($interactor);

        $request = $this->givenValidData();

        $this->processor->process($request, '123');

        $user = $this->processor->getUser();

        $this->assertNotNull($user);
        $this->assertFalse($this->processor->hasErrors());
        $this->assertTrue($this->processor->isValid());
    }

    /**
     * @test
     */
    public function whenProcessingValidFormButResetInteractorFails_thenHasErrors()
    {
        $user = $this->givenUser();
        $interactor = new UserInteractorMock($user);

        $this->interactorFactory->shouldReceive('get')
            ->with(SecurityInteractorRegister::GET_USER)
            ->andReturn($interactor);

        $interactor = new ErrorInteractor('Undefined');

        $this->interactorFactory->shouldReceive('get')
            ->with(SecurityInteractorRegister::RESET_PASSWORD)
            ->andReturn($interactor);

        $request = $this->givenValidData();

        $this->processor->process($request, '123');

        $this->assertTrue($this->processor->hasErrors());

        $errors = $this->processor->getErrors();
        $this->assertThat($errors[0], $this->equalTo('Undefined'));
    }

    private function buildRequest()
    {
        $request = new Request(array(), array());
        $request->setMethod('POST');
        return $request;
    }

    private function givenUser()
    {
        return new User(null, 'test@example.com', 'password', '');
    }

    private function givenValidData()
    {
        $user = $this->givenUser();
        $data = new ResetPasswordFormData($user);

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
        $request = new Request(array(), array('reset_password' => $data));
        $request->setMethod('POST');

        return $request;
    }

    private function givenValidForm(ResetPasswordFormData $data)
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
