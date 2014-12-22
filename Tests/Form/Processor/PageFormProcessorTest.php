<?php
//
//
//namespace Diside\SecurityBundle\Tests\Form\Processor;
//
//use Diside\SecurityBundle\Tests\Mock\PageInteractorMock;
//use Diside\SecurityBundle\Tests\Mock\InteractorMock;
//use Mockery as m;
//use Diside\SecurityComponent\Interactor\SecurityInteractorRegister;
//use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
//use Symfony\Component\Form\FormFactoryInterface;
//use Symfony\Component\Form\FormInterface;
//use Symfony\Component\HttpFoundation\Request;
//use Symfony\Component\Security\Core\SecurityContextInterface;
//use Diside\SecurityComponent\Interactor\InteractorFactory;
//use Diside\SecurityComponent\Model\Page;
//use Diside\SecurityComponent\Model\User;
//use Diside\SecurityBundle\Form\Data\PageFormData;
//use Diside\SecurityBundle\Form\Processor\PageFormProcessor;
//use Diside\SecurityBundle\Tests\Mock\DummyToken;
//use Diside\SecurityBundle\Tests\Mock\ErrorInteractor;
//
//class PageFormProcessorTest extends FormProcessorTestCase
//{
//    private $locales = array('it');
//
//    protected function buildProcessor(FormFactoryInterface $formFactory, InteractorFactory $interactorFactory, SecurityContextInterface $securityContext)
//    {
//        return new PageFormProcessor($formFactory, $interactorFactory, $securityContext, 'en', $this->locales);
//    }
//
//    protected function buildValidData($object)
//    {
//        $data = new PageFormData($this->locales, $object);
//
//        return $data;
//    }
//
//    protected function getFormName()
//    {
//        return 'page';
//    }
//
//
//    /**
//     * @test
//     */
//    public function testConstructor()
//    {
//        $this->assertNull($this->processor->getPage());
//        $this->assertNull($this->processor->getErrors());
//        $this->assertFalse($this->processor->hasErrors());
//    }
//
//    /**
//     * @test
//     * @expectedException \Diside\SecurityBundle\Exception\UnauthorizedException
//     */
//    public function whenProcessingRequestAndPageIsAnonymous_thenThrow()
//    {
//        $this->givenNotAuthorized();
//
//        $request = new Request();
//
//        $this->processor->process($request);
//    }
//
//    /**
//     * @test
//     */
//    public function whenProcessingWithNoData_thenIsNotValid()
//    {
//        $this->givenLoggedSuperadmin();
//        $this->givenInvalidData();
//
//        $request = $this->givenPostRequest();
//
//        $this->processor->process($request);
//        $this->assertFalse($this->processor->hasErrors());
//    }
//
//    /**
//     * @test
//     */
//    public function whenProcessing_thenFormDataIsSet()
//    {
//        $this->givenLoggedSuperadmin();
//        $this->givenInvalidData();
//
//        $expect = $this->expectFormDataIsSet();
//
//        $request = $this->givenPostRequest();
//
//        $this->processor->process($request);
//
//        $expect->verify();
//    }
//
//    /**
//     * @test
//     */
//    public function whenProcessingValidForm_thenHasNoErrors()
//    {
//        $page = $this->givenPage();
//        $interactor = new InteractorMock($page, 'setPage');
//
//        $this->expectInteractorFor($interactor, SecurityInteractorRegister::SAVE_PAGE);
//
//        $this->givenLoggedSuperadmin();
//        $request = $this->givenValidData();
//
//        $this->processor->process($request);
//
//        $page = $this->processor->getPage();
//
//        $this->assertNotNull($page);
//        $this->assertFalse($this->processor->hasErrors());
//        $this->assertTrue($this->processor->isValid());
//    }
//
//    /**
//     * @test
//     */
//    public function whenProcessingExisting_thenSaveExisting()
//    {
//        $page = $this->givenPage();
//        $interactor = new InteractorMock($page, 'setPage');
//
//        $expect = $this->expectInteractorFor($interactor, SecurityInteractorRegister::GET_PAGE);
//
//        $this->givenLoggedSuperadmin();
//        $request = $this->givenInvalidData();
//
//        $this->processor->process($request, 1);
//
//        $expect->verify();
//    }
//
//    /**
//     * @test
//     */
//    public function whenProcessingValidFormButInteractorFails_thenHasErrors()
//    {
//        $this->givenErrorInteractorFor(SecurityInteractorRegister::SAVE_PAGE);
//
//        $this->givenLoggedSuperadmin();
//        $request = $this->givenValidData();
//
//        $this->processor->process($request);
//
//        $this->assertTrue($this->processor->hasErrors());
//
//        $errors = $this->processor->getErrors();
//        $this->assertThat($errors[0], $this->equalTo('Error'));
//    }
//
//    protected function givenPage()
//    {
//        return new Page(null, 'en', 'url', 'title', 'content');
//    }
//}
