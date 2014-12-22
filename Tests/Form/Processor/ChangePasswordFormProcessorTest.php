<?php
//
//namespace Diside\SecurityBundle\Tests\Form\Processor;
//
//use Diside\SecurityBundle\Form\Data\ChangePasswordFormData;
//use Diside\SecurityBundle\Form\Processor\ChangePasswordFormProcessor;
//use Diside\SecurityBundle\Tests\Mock\InteractorMock;
//use Diside\SecurityComponent\Interactor\AbstractInteractor;
//use Diside\SecurityComponent\Interactor\InteractorFactory;
//use Diside\SecurityComponent\Interactor\InteractorRegister;
//use Diside\SecurityComponent\Interactor\Presenter;
//use Diside\SecurityComponent\Interactor\Presenter\UserPresenter;
//use Diside\SecurityComponent\Interactor\Request as InteractorRequest;
//use Diside\SecurityComponent\Interactor\SecurityInteractorRegister;
//use Diside\SecurityComponent\Model\User;
//use Mockery as m;
//use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
//use Symfony\Component\Form\FormFactoryInterface;
//use Symfony\Component\Form\FormInterface;
//use Symfony\Component\HttpFoundation\Request;
//use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
//use Symfony\Component\Security\Core\SecurityContextInterface;
//
//class ChangePasswordFormProcessorTest extends FormProcessorTestCase
//{
//
//    protected function buildProcessor(
//        FormFactoryInterface $formFactory,
//        InteractorFactory $interactorFactory,
//        SecurityContextInterface $securityContext
//    ) {
//        $encoder = m::mock('Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface');
//        $encoder->shouldReceive('encodePassword');
//
//        $encoderFactory = m::mock('Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface');
//        $encoderFactory->shouldReceive('getEncoder')
//            ->andReturn($encoder);
//
//        return new ChangePasswordFormProcessor(
//            $formFactory,
//            $interactorFactory,
//            $securityContext,
//            $encoderFactory
//        );
//    }
//
//    protected function buildValidData($object)
//    {
//        $user = $this->givenLoggedUser();
//        $data = new ChangePasswordFormData($user);
//
//        return $data;
//    }
//
//    protected function getFormName()
//    {
//        return 'change_password';
//    }
//
//    /**
//     * @test
//     */
//    public function testConstructor()
//    {
//        $this->assertNull($this->processor->getUser());
//        $this->assertNull($this->processor->getErrors());
//        $this->assertFalse($this->processor->hasErrors());
//    }
//
//    /**
//     * @test
//     */
//    public function whenProcessingWithNoData_thenIsNotValid()
//    {
//        $user = $this->givenLoggedUser();
//        $this->givenInvalidData();
//
//        $request = $this->givenPostRequest();
//
//        $this->processor->process($request, $user->getId());
//        $this->assertFalse($this->processor->hasErrors());
//        $this->assertFalse($this->processor->isValid());
//    }
//
//    /**
//     * @test
//     */
//    public function whenProcessingValidForm_thenHasNoErrors()
//    {
//        $user = $this->givenUser();
//        $this->givenLoggedUser($user);
//
//        $interactor = new InteractorMock($user, 'setUser');
//
//        $this->expectInteractorFor($interactor, SecurityInteractorRegister::GET_USER);
//        $this->expectInteractorFor($interactor, SecurityInteractorRegister::SAVE_USER);
//
//        $request = $this->givenValidData();
//
//        $this->processor->process($request, $user->getId());
//
//        $user = $this->processor->getUser();
//
//        $this->assertNotNull($user);
//        $this->assertFalse($this->processor->hasErrors());
//        $this->assertTrue($this->processor->isValid());
//    }
//
//    /**
//     * @test
//     */
//    public function whenProcessingValidFormButInteractorFails_thenHasErrors()
//    {
//        $this->givenErrorInteractorFor(SecurityInteractorRegister::SAVE_USER);
//        $request = $this->givenValidData();
//        $user = $this->givenLoggedUser();
//
//        $this->processor->process($request, $user->getId());
//
//        $this->assertTrue($this->processor->hasErrors());
//
//        $errors = $this->processor->getErrors();
//        $this->assertThat($errors[0], $this->equalTo('Error'));
//    }
//}
