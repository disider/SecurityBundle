<?php

namespace Diside\SecurityBundle\Tests\Form\Processor;

use Diside\SecurityBundle\Form\Processor\BaseFormProcessor;
use Diside\SecurityBundle\Security\LoggedUser;
use Diside\SecurityBundle\Tests\Mock\DummyToken;
use Diside\SecurityBundle\Tests\Mock\ErrorInteractor;
use Diside\SecurityComponent\Interactor\InteractorFactory;
use Diside\SecurityComponent\Interactor\SecurityInteractorRegister;
use Diside\SecurityComponent\Model\Page;
use Diside\SecurityComponent\Model\User;
use Mockery as m;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContextInterface;

abstract class FormProcessorTestCase extends WebTestCase
{
    /** @var BaseFormProcessor */
    protected $processor;

    /** @var FormInterface */
    private $form;

    /** @var InteractorFactory */
    private $interactorFactory;

    /** @var SecurityContextInterface */
    private $securityContext;

    protected abstract function buildProcessor(FormFactoryInterface $formFactory, InteractorFactory $interactorFactory, SecurityContextInterface $securityContext);

    protected abstract function buildValidData($object);

    protected abstract function getFormName();

    protected function setUp()
    {
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

        $this->interactorFactory = m::mock('Diside\SecurityComponent\Interactor\InteractorFactory');

        $this->securityContext = m::mock('Symfony\Component\Security\Core\SecurityContextInterface');

        $this->processor = $this->buildProcessor($formFactory, $this->interactorFactory, $this->securityContext);
        $this->formName = $this->getFormName();
    }

    protected function givenValidData($object = null)
    {
        $data = $this->buildValidData($object);

        $this->givenValidForm($data);

        return $this->givenPostRequest($data);
    }

    protected function givenInvalidData()
    {
        $this->form
            ->shouldReceive('isValid')
            ->once()
            ->andReturn(false);

        return $this->givenPostRequest(array());
    }

    protected function givenPostRequest($data = array())
    {
        $request = new Request(array(), array($this->formName => $data));
        $request->setMethod('POST');

        return $request;
    }

    protected function givenValidForm($data)
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

    protected function givenNotAuthorized()
    {
        $this->securityContext
            ->shouldReceive('isGranted')
            ->andReturn(false);
    }

    protected function expectInteractorFor($interactor, $name)
    {
        $expect = $this->interactorFactory->shouldReceive('get')
            ->with($name)
            ->andReturn($interactor)
            ->once();

        return $expect;
    }

    protected function givenErrorInteractorFor($name)
    {
        $interactor = new ErrorInteractor('Error');

        $this->interactorFactory->shouldReceive('get')
            ->with($name)
            ->andReturn($interactor);
    }

    protected function givenAuthorized()
    {
        $this->securityContext
            ->shouldReceive('isGranted')
            ->andReturn(true);
    }

    protected function givenLoggedUser($role = User::ROLE_USER)
    {
        $user = $this->givenUser($role);

        return $this->buildLoggedUser($user);
    }

    protected function givenLoggedSuperadmin()
    {
        $this->givenLoggedUser(User::ROLE_SUPERADMIN);
    }

    protected function expectFormDataIsSet()
    {
        $expect = $this->form->mockery_findExpectation('setData', array());
        $expect->once();

        return $expect;
    }

    protected function givenUser($role = User::ROLE_USER)
    {
        $user = new User(1, 'adam@example.com', 'password', 'salt');
        $user->addRole($role);

        return $user;
    }

    protected function givenAdmin()
    {
        $user = new User(2, 'admin@example.com', 'password', 'salt');
        $user->addRole(User::ROLE_ADMIN);

        return $user;
    }

    /**
     * @param $user
     * @return LoggedUser
     */
    protected function buildLoggedUser($user)
    {
        $this->givenAuthorized();

        $loggedUser = new LoggedUser($user);

        $token = new DummyToken($loggedUser);

        $this->securityContext
            ->shouldReceive('getToken')
            ->once()
            ->andReturn($token);

        return $loggedUser;
    }

}
