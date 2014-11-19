<?php


namespace Diside\SecurityBundle\Tests\Form\Processor;

use Diside\SecurityBundle\Tests\Mock\CompanyInteractorMock;
use Mockery as m;
use Diside\SecurityComponent\Interactor\SecurityInteractorRegister;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Diside\SecurityComponent\Interactor\InteractorFactory;
use Diside\SecurityComponent\Model\Company;
use Diside\SecurityComponent\Model\User;
use Diside\SecurityBundle\Form\Data\CompanyFormData;
use Diside\SecurityBundle\Form\Processor\CompanyFormProcessor;
use Diside\SecurityBundle\Tests\Mock\DummyToken;
use Diside\SecurityBundle\Tests\Mock\ErrorInteractor;

class CompanyFormProcessorTest extends WebTestCase
{
    /** @var CompanyFormProcessor */
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

        $button = m::mock('Symfony\Component\Form\SubmitButton');
        $button->shouldReceive('isClicked');
        $this->form->shouldReceive('get')->andReturn($button);
        $this->form->shouldReceive('has');

        $formFactory = m::mock('Symfony\Component\Form\FormFactoryInterface');
        $formFactory->shouldReceive('create')
            ->andReturn($this->form);

        $this->interactorFactory = m::mock('Diside\SecurityComponent\Interactor\InteractorFactory');

        $this->securityContext = m::mock('Symfony\Component\Security\Core\SecurityContextInterface');

        $this->processor = new CompanyFormProcessor($formFactory, $this->interactorFactory, $this->securityContext);
    }

    /**
     * @test
     */
    public function testConstructor()
    {
        $this->assertNull($this->processor->getCompany());
        $this->assertNull($this->processor->getErrors());
        $this->assertFalse($this->processor->hasErrors());
    }

    /**
     * @test
     * @expectedException \Diside\SecurityBundle\Exception\UnauthorizedException
     */
    public function whenProcessingRequestAndCompanyIsAnonymous_thenThrow()
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
        $this->givenLoggedSuperadmin();
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
        $this->givenLoggedSuperadmin();
        $this->givenInvalidData();

        $expect = $this->form->mockery_findExpectation('setData', array());
        $expect->once();

        $request = $this->buildRequest();

        $this->processor->process($request);

        $expect->verify();
    }

    private function buildRequest()
    {
        $request = new Request(array(), array());
        $request->setMethod('POST');
        return $request;
    }

    /**
     * @test
     */
    public function whenProcessingValidForm_thenHasNoErrors()
    {
        $company = $this->givenCompany();
        $interactor = new CompanyInteractorMock($company);

        $this->interactorFactory->shouldReceive('get')
            ->with(SecurityInteractorRegister::SAVE_COMPANY)
            ->andReturn($interactor);

        $this->givenLoggedSuperadmin();
        $request = $this->givenValidData();

        $this->processor->process($request);

        $company = $this->processor->getCompany();

        $this->assertNotNull($company);
        $this->assertFalse($this->processor->hasErrors());
        $this->assertTrue($this->processor->isValid());
    }

    /**
     * @test
     */
    public function whenProcessingExistingChecklist_thenSaveExistingChecklist()
    {
        $company = $this->givenCompany();
        $interactor = new CompanyInteractorMock($company);

        $expect = $this->interactorFactory->shouldReceive('get')
            ->once()
            ->with(SecurityInteractorRegister::GET_COMPANY)
            ->andReturn($interactor);

        $this->givenLoggedSuperadmin();
        $request = $this->givenInvalidData();

        $this->processor->process($request, 1);

        $expect->verify();
    }

    /**
     * @test
     */
    public function whenProcessingValidFormButInteractorFails_thenHasErrors()
    {
        $interactor = new ErrorInteractor('Error');

        $this->interactorFactory->shouldReceive('get')
            ->with(SecurityInteractorRegister::SAVE_COMPANY)
            ->andReturn($interactor);

        $this->givenLoggedSuperadmin();
        $request = $this->givenValidData();

        $this->processor->process($request);

        $this->assertTrue($this->processor->hasErrors());

        $errors = $this->processor->getErrors();
        $this->assertThat($errors[0], $this->equalTo('Error'));
    }

    protected function givenCompany()
    {
        $company = new Company(null, 'test@example.com', 'password', '');
        return $company;
    }

    private function givenLoggedSuperadmin()
    {
        $user = new User(1, 'adam@example.com', 'password', 'salt');
        $user->addRole(User::ROLE_SUPERADMIN);

        $token = new DummyToken($user);

        $this->securityContext
            ->shouldReceive('isGranted')
            ->andReturn(true);

        $this->securityContext
            ->shouldReceive('getToken')
            ->once()
            ->andReturn($token);
    }

    private function givenValidData()
    {
        $data = new CompanyFormData();
        $data->setName('Acme');

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
        $request = new Request(array(), array('company' => $data));
        $request->setMethod('POST');

        return $request;
    }

    private function givenValidForm(CompanyFormData $data)
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
