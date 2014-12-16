<?php

namespace Diside\SecurityBundle\Tests\Form\Processor;

use Diside\SecurityBundle\Builder\UserBuilder;
use Diside\SecurityBundle\Form\Data\RegistrationFormData;
use Diside\SecurityBundle\Form\Processor\RegistrationFormProcessor;
use Diside\SecurityBundle\Tests\Mock\ErrorInteractor;
use Diside\SecurityBundle\Tests\Mock\InteractorMock;
use Diside\SecurityComponent\Interactor\InteractorFactory;
use Diside\SecurityComponent\Interactor\SecurityInteractorRegister;
use Diside\SecurityComponent\Model\User;
use Mockery as m;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContextInterface;

class RegistrationFormProcessorTest extends FormProcessorTestCase
{

    protected function buildProcessor(
        FormFactoryInterface $formFactory,
        InteractorFactory $interactorFactory,
        SecurityContextInterface $securityContext
    ) {
        $encoder = m::mock('Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface');
        $encoder->shouldReceive('encodePassword');

        $encoderFactory = m::mock('Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface');
        $encoderFactory->shouldReceive('getEncoder')
            ->andReturn($encoder);

        $userBuilder = new UserBuilder();

        return new RegistrationFormProcessor(
            $formFactory,
            $interactorFactory,
            $encoderFactory,
            $userBuilder
        );

    }

    protected function buildValidData($object)
    {
        return new RegistrationFormData($object);
    }

    protected function getFormName()
    {
        return 'registration';
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

        $request = $this->givenPostRequest();

        $this->processor->process($request);
        $this->assertFalse($this->processor->hasErrors());
    }

    /**
     * @test
     */
    public function whenProcessingValidForm_thenHasNoErrors()
    {
        $user = $this->givenUser();
        $interactor = new InteractorMock($user, 'setUser');

        $this->expectInteractorFor($interactor, SecurityInteractorRegister::REGISTER_USER);

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

        $this->expectInteractorFor($interactor, SecurityInteractorRegister::REGISTER_USER);

        $request = $this->givenValidData();

        $this->processor->process($request);

        $this->assertTrue($this->processor->hasErrors());

        $errors = $this->processor->getErrors();
        $this->assertThat($errors[0], $this->equalTo('Undefined'));
    }

}
