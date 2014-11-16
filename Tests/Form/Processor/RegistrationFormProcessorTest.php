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
use Diside\SecurityBundle\Form\Data\RegistrationFormData;
use Diside\SecurityBundle\Form\Processor\RegistrationFormProcessor;
use Diside\SecurityBundle\Tests\Mock\ErrorInteractor;

class RegistrationFormProcessorTest extends WebTestCase
{
    /** @var RegistrationFormProcessor */
    private $processor;

    /** @var FormInterface */
    private $form;

    /** @var InteractorFactory */
    private $interactorFactory;

    protected function setUp()
    {
        $this->interactorFactory = m::mock('SecurityComponent\Interactor\InteractorFactory');

        $encoder = m::mock('Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface');
        $encoder->shouldReceive('encodePassword');

        $encoderFactory = m::mock('Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface');
        $encoderFactory->shouldReceive('getEncoder')
            ->andReturn($encoder);

        $this->form = m::mock('Symfony\Component\Form\Form');
        $this->form->shouldReceive('handleRequest');
        $this->form->shouldReceive('setData');

        $formFactory = m::mock('Symfony\Component\Form\FormFactoryInterface');
        $formFactory->shouldReceive('create')
            ->andReturn($this->form);

        $this->processor = new RegistrationFormProcessor($formFactory, $this->interactorFactory, $encoderFactory);
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
        $interactor = new RegistrationInteractorMock($user);

        $this->interactorFactory->shouldReceive('get')
            ->with(InteractorFactory::REGISTER_USER)
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
            ->with(InteractorFactory::REGISTER_USER)
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
        $data = new RegistrationFormData($user);

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
        $request = new Request(array(), array('registration' => $data));
        $request->setMethod('POST');

        return $request;
    }

    private function givenValidForm(RegistrationFormData $data)
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

class RegistrationInteractorMock implements Interactor
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
