<?php

namespace Diside\SecurityBundle\Tests;

use Diside\SecurityBundle\Factory\EntityFactory;
use Diside\SecurityBundle\Factory\RequestFactory;
use Diside\SecurityBundle\Form\Processor\BaseFormProcessor;
use Diside\SecurityBundle\Security\LoggedUser;
use Diside\SecurityBundle\Security\PermissionChecker;
use Diside\SecurityBundle\Tests\Mock\DummyToken;
use Diside\SecurityBundle\Tests\Mock\ErrorInteractor;
use Diside\SecurityComponent\Interactor\InteractorFactory;
use Diside\SecurityComponent\Interactor\Request as InteractorRequest;
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

    /** @var PermissionChecker */
    private $permissionChecker;

    /** @var EntityFactory */
    private $entityFactory;

    protected abstract function buildProcessor(
        FormFactoryInterface $formFactory,
        InteractorFactory $interactorFactory,
        SecurityContextInterface $securityContext,
        EntityFactory $entityFactory,
        RequestFactory $requestFactory,
        PermissionChecker $permissionChecker
    );

    protected abstract function getFormName();

    protected abstract function buildFormData();

    protected function mockEntities()
    {

    }

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

        $this->entityFactory = $this->buildEntityFactory();

        $this->mockEntities();

        $requestFactory = $this->buildRequestFactory();
        $this->permissionChecker = m::mock('Diside\SecurityBundle\Security\PermissionChecker');

        $this->securityContext = m::mock('Symfony\Component\Security\Core\SecurityContextInterface');

        $this->processor = $this->buildProcessor(
            $formFactory,
            $this->interactorFactory,
            $this->securityContext,
            $this->entityFactory,
            $requestFactory,
            $this->permissionChecker
        );

        $this->formName = $this->getFormName();
    }

    protected function givenValidData($model = null)
    {
        $entity = $this->buildFormData();
        if($model)
            $entity->fromModel($model);

        $this->givenValidForm($entity);

        return $this->givenPostRequest($entity);
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

    protected function buildEntityFactory()
    {
        $this->entityFactory = m::mock('Diside\SecurityBundle\Factory\EntityFactory');

        return $this->entityFactory;
    }

    /**
     * @return m\MockInterface
     */
    protected function buildRequestFactory()
    {
        $dummyRequest = new DummyRequest();

        $requestFactory = m::mock('Diside\SecurityBundle\Factory\RequestFactory');
        $requestFactory->shouldReceive('create')
            ->andReturn($dummyRequest);

        return $requestFactory;
    }

    protected function setPermission($permission, $value)
    {
        $this->permissionChecker->shouldReceive('check')
            ->with($permission, m::any())
            ->andReturn($value);
    }

    protected function mockEntity($name, $entity)
    {
        $this->entityFactory->shouldReceive('create')
            ->with($name, null)
            ->andReturn($entity);
        $this->entityFactory->shouldReceive('create')
            ->with($name)
            ->andReturn($entity);
        $this->entityFactory->shouldReceive('getClass')
            ->with($name)
            ->andReturn(get_class($entity));
    }

}

class DummyRequest implements InteractorRequest
{

}
