<?php

namespace Diside\SecurityBundle\Form\Processor;

use Diside\SecurityBundle\Form\Data\RequestResetPasswordFormData;
use Diside\SecurityBundle\Form\RequestResetPasswordForm;
use Diside\SecurityComponent\Interactor\InteractorFactory;
use Diside\SecurityComponent\Interactor\Presenter\UserPresenter;
use Diside\SecurityComponent\Interactor\Request\RequestResetPasswordRequest;
use Diside\SecurityComponent\Interactor\SecurityInteractorRegister;
use Diside\SecurityComponent\Model\User;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\SecurityContextInterface;

class RequestResetPasswordFormProcessor implements UserPresenter
{
    /** @var bool */
    private $isValid = false;

    /** @var array */
    private $errors;

    /** @var RequestResetPasswordForm */
    private $form;

    /** @var InteractorFactory */
    private $interactorFactory;

    /** @var User */
    private $user;

    /** @var FormFactoryInterface */
    private $factory;

    public function __construct(FormFactoryInterface $factory, InteractorFactory $interactorFactory)
    {
        $this->interactorFactory = $interactorFactory;
        $this->factory = $factory;
    }

    public function getForm()
    {
        return $this->form;
    }

    public function process(Request $request)
    {
        $this->form = $this->factory->create(new RequestResetPasswordForm());

        if ($request->isMethod('POST')) {
            $this->form->handleRequest($request);

            if ($this->form->isValid()) {
                /** @var RequestResetPasswordFormData $data */
                $data = $this->form->getData();

                $request = $this->buildRequest($data);

                $interactor = $this->interactorFactory->get(SecurityInteractorRegister::REQUEST_RESET_PASSWORD);

                $interactor->process($request, $this);

                $this->isValid = !$this->hasErrors();
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

    public function getUser()
    {
        return $this->user;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    private function buildRequest(RequestResetPasswordFormData $data)
    {
        return new RequestResetPasswordRequest($data->getEmail());
    }
}
