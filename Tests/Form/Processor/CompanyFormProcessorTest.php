<?php


namespace Diside\SecurityBundle\Tests\Form\Processor;

use Diside\SecurityBundle\Tests\Mock\CompanyInteractorMock;
use Diside\SecurityBundle\Tests\Mock\InteractorMock;
use Mockery as m;
use Diside\SecurityComponent\Interactor\SecurityInteractorRegister;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Form\FormFactoryInterface;
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

class CompanyFormProcessorTest extends FormProcessorTestCase
{
    protected function buildProcessor(FormFactoryInterface $formFactory, InteractorFactory $interactorFactory, SecurityContextInterface $securityContext)
    {
        return new CompanyFormProcessor($formFactory, $interactorFactory, $securityContext);
    }

    protected function buildValidData($object)
    {
        $data = new CompanyFormData();
        $data->setName('Acme');

        return $data;
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
    public function whenProcessingExistingChecklist_thenSaveExistingChecklist()
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
}
