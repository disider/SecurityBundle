<?php

namespace Diside\SecurityBundle\Tests\Form\Processor;

use Diside\SecurityBundle\Entity\Company as CompanyEntity;
use Diside\SecurityBundle\Factory\EntityFactory;
use Diside\SecurityBundle\Factory\RequestFactory;
use Diside\SecurityBundle\Form\Processor\CompanyFormProcessor;
use Diside\SecurityBundle\Security\PermissionChecker;
use Diside\SecurityBundle\Tests\FormProcessorTestCase;
use Diside\SecurityBundle\Tests\Mock\InteractorMock;
use Diside\SecurityComponent\Interactor\InteractorFactory;
use Diside\SecurityComponent\Interactor\SecurityInteractorRegister;
use Diside\SecurityComponent\Model\Company;
use Diside\SecurityComponent\Model\User;
use Mockery as m;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContextInterface;

class CompanyFormProcessorTest extends FormProcessorTestCase
{
    protected function buildProcessor(
        FormFactoryInterface $formFactory,
        InteractorFactory $interactorFactory,
        SecurityContextInterface $securityContext,
        EntityFactory $entityFactory,
        RequestFactory $requestFactory,
        PermissionChecker $permissionChecker
    ) {
        return new CompanyFormProcessor($formFactory, $interactorFactory, $securityContext, $entityFactory, $requestFactory, $permissionChecker);
    }

    protected function getFormName()
    {
        return 'company';
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
        $this->givenNotAuthorized();

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

        $request = $this->givenPostRequest();

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

        $expect = $this->expectFormDataIsSet();

        $request = $this->givenPostRequest();

        $this->processor->process($request);

        $expect->verify();
    }

    /**
     * @test
     */
    public function whenProcessingValidForm_thenHasNoErrors()
    {
        $company = $this->givenCompany();
        $interactor = new InteractorMock($company, 'setCompany');

        $this->expectInteractorFor($interactor, SecurityInteractorRegister::SAVE_COMPANY);

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
    public function whenProcessingExisting_thenSaveExisting()
    {
        $company = $this->givenCompany();
        $interactor = new InteractorMock($company, 'setCompany');

        $expect = $this->expectInteractorFor($interactor, SecurityInteractorRegister::GET_COMPANY);

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
        $this->givenErrorInteractorFor(SecurityInteractorRegister::SAVE_COMPANY);

        $this->givenLoggedSuperadmin();
        $request = $this->givenValidData();

        $this->processor->process($request);

        $this->assertTrue($this->processor->hasErrors());

        $errors = $this->processor->getErrors();
        $this->assertThat($errors[0], $this->equalTo('Error'));
    }

    protected function givenCompany()
    {
        return new Company(null, 'test@example.com', 'password', '');
    }

    protected function mockEntities()
    {
        $this->mockEntity('company', new CompanyEntity());
    }

    protected function buildFormData()
    {
        return new CompanyEntity();
    }
}
