<?php


namespace Diside\SecurityBundle\Tests\Form\Processor;

use Diside\SecurityBundle\Form\Data\RequestResetPasswordFormData;
use Diside\SecurityBundle\Form\Processor\RequestResetPasswordFormProcessor;
use Diside\SecurityBundle\Tests\Mock\ErrorInteractor;
use Diside\SecurityBundle\Tests\Mock\UserInteractorMock;
use Mockery as m;
use Diside\SecurityComponent\Interactor\SecurityInteractorRegister;
use Diside\SecurityComponent\Model\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContextInterface;

class RequestResetPasswordFormProcessorTest extends WebTestCase
{
    /** @var RequestResetPasswordFormProcessor */
    private $processor;

    /** @var FormInterface */
    private $form;

    /** @var InteractorFactory */
    private $interactorFactory;

    protected function setUp()
    {
        $this->interactorFactory = m::mock('Diside\SecurityComponent\Interactor\InteractorFactory');

        $this->form = m::mock('Symfony\Component\Form\Form');
        $this->form->shouldReceive('handleRequest');
        $this->form->shouldReceive('setData');

        $formFactory = m::mock('Symfony\Component\Form\FormFactoryInterface');
        $formFactory->shouldReceive('create')
            ->andReturn($this->form);

        $this->processor = new RequestResetPasswordFormProcessor($formFactory, $this->interactorFactory);
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
        $this->givenInvalidData();

        $request = $this->buildRequest();

        $this->processor->process($request);
        $this->assertFalse($this->processor->hasErrors());
    }

    /**
     * @test
     */
    public function whenProcessingValidForm_thenHasNoErrors()
    {
        $user = $this->givenUser();
        $interactor = new UserInteractorMock($user);

        $this->interactorFactory->shouldReceive('get')
            ->with(SecurityInteractorRegister::REQUEST_RESET_PASSWORD)
            ->andReturn($interactor);

        $request = $this->givenValidData();

        $this->processor->process($request);

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
        $interactor = new ErrorInteractor('Undefined');

        $this->interactorFactory->shouldReceive('get')
            ->with(SecurityInteractorRegister::REQUEST_RESET_PASSWORD)
            ->andReturn($interactor);

        $request = $this->givenValidData();

        $this->processor->process($request);

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
        $data = new RequestResetPasswordFormData($user);

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
        $request = new Request(array(), array('request_reset_password' => $data));
        $request->setMethod('POST');

        return $request;
    }

    private function givenValidForm(RequestResetPasswordFormData $data)
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
