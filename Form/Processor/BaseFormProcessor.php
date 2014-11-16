<?php

namespace Diside\SecurityBundle\Form\Processor;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\SecurityContextInterface;
use SecurityComponent\Interactor\InteractorFactory;
use SecurityComponent\Interactor\Presenter;
use SecurityComponent\Model\User;
use Diside\SecurityBundle\Exception\UnauthorizedException;

abstract class BaseFormProcessor implements Presenter
{
    const REDIRECT_TO_LIST = 'redirect_to_list';

    /** @var array */
    private $errors;

    /** @var BaseForm */
    private $form;

    /** @var InteractorFactory */
    private $interactorFactory;

    /** @var SecurityContextInterface */
    private $securityContext;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var string */
    private $redirectTo;

    /** @var bool */
    private $isValid = false;

    public function __construct(FormFactoryInterface $formFactory, InteractorFactory $interactorFactory, SecurityContextInterface $securityContext)
    {
        $this->interactorFactory = $interactorFactory;
        $this->securityContext = $securityContext;
        $this->formFactory = $formFactory;
    }

    protected abstract function getSaveInteractorName();

    protected abstract function buildForm();

    protected abstract function buildFormData($id);

    public function getForm()
    {
        return $this->form;
    }

    protected function getSecurityContext()
    {
        return $this->securityContext;
    }

    protected function getInteractorFactory()
    {
        return $this->interactorFactory;
    }

    public function process(Request $request, $id = null)
    {
        if (!$this->isUserAuthenticated())
            throw new UnauthorizedException;

        $formData = $this->buildFormData($id);

        $this->form = $this->formFactory->create($this->buildForm());
        $this->form->setData($formData);

        $this->handleRequest($request);
    }

    protected function handleRequest(Request $request)
    {
        if ($request->isMethod('POST')) {
            $this->form->handleRequest($request);

            if ($this->form->isValid()) {

                $request = $this->buildRequest();

                $interactor = $this->interactorFactory->get($this->getSaveInteractorName());

                $interactor->process($request, $this);

                $this->isValid = !$this->hasErrors();

                $this->evaluateRedirect();
            }
        }
    }

    public function isValid()
    {
        return $this->isValid;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function setErrors(array $errors)
    {
        $this->errors = $errors;
    }

    public function hasErrors()
    {
        return $this->errors != null;
    }

    public function isRedirectingTo($to)
    {
        return $this->redirectTo == $to;
    }

    /** @return User */
    protected function getAuthenticatedUser()
    {
        $token = $this->securityContext->getToken();
        $user = $token->getUser();
        return $user;
    }

    protected function evaluateRedirect()
    {
    }

    protected function isUserAuthenticated()
    {
        return $this->securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED');
    }

    protected function setRedirectTo($to)
    {
        $this->redirectTo = $to;
    }

    protected function isButtonClicked($buttonName)
    {
        if(!$this->form->has($buttonName))
            return false;

        return $this->form->get($buttonName)->isClicked();
    }

    protected function getFormData()
    {
        return $this->form->getData();
    }

}
