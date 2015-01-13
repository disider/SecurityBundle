<?php


namespace Diside\SecurityBundle\Tests\Form\Processor;

use Diside\SecurityBundle\Factory\EntityFactory;
use Diside\SecurityBundle\Factory\RequestFactory;
use Diside\SecurityBundle\Form\Data\ResetPasswordFormData;
use Diside\SecurityBundle\Form\Processor\ResetPasswordFormProcessor;
use Diside\SecurityBundle\Form\ResetPasswordForm;
use Diside\SecurityBundle\Security\PermissionChecker;
use Diside\SecurityBundle\Tests\FormProcessorTestCase;
use Diside\SecurityBundle\Tests\Mock\ErrorInteractor;
use Diside\SecurityBundle\Tests\Mock\InteractorMock;
use Diside\SecurityBundle\Tests\Mock\UserInteractorMock;
use Diside\SecurityComponent\Interactor\InteractorFactory;
use Diside\SecurityComponent\Interactor\SecurityInteractorRegister;
use Diside\SecurityComponent\Model\User;
use Mockery as m;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContextInterface;

class ResetPasswordFormProcessorTest extends FormProcessorTestCase
{
    protected function buildProcessor(
        FormFactoryInterface $formFactory,
        InteractorFactory $interactorFactory,
        SecurityContextInterface $securityContext,
        EntityFactory $entityFactory,
        RequestFactory $requestFactory,
        PermissionChecker $permissionChecker
    ) {
        $encoder = m::mock('Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface');
        $encoder->shouldReceive('encodePassword');

        $encoderFactory = m::mock('Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface');
        $encoderFactory->shouldReceive('getEncoder')
            ->andReturn($encoder);

        return new ResetPasswordFormProcessor($formFactory, $interactorFactory, $encoderFactory, new ResetPasswordForm('Diside\SecurityBundle\Form\Data\ResetPasswordForm'));
    }

    protected function getFormName()
    {
        return 'reset_password';
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

        $this->givenErrorInteractorFor(SecurityInteractorRegister::GET_USER);

        $request = $this->givenPostRequest();

        $this->processor->process($request, '123');
    }

    /**
     * @test
     */
    public function whenProcessingValidForm_thenHasNoErrors()
    {
        $user = $this->givenUser();

        $interactor = new InteractorMock($user, 'setUser');

        $this->expectInteractorFor($interactor, SecurityInteractorRegister::GET_USER);

        $this->expectInteractorFor($interactor, SecurityInteractorRegister::RESET_PASSWORD);

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
        $interactor = new InteractorMock($user, 'setUser');

        $this->expectInteractorFor($interactor, SecurityInteractorRegister::GET_USER);

        $this->givenErrorInteractorFor(SecurityInteractorRegister::RESET_PASSWORD);

        $request = $this->givenValidData();

        $this->processor->process($request, '123');

        $this->assertTrue($this->processor->hasErrors());
    }

    protected function buildFormData()
    {
        return new ResetPasswordFormData();
    }

}
