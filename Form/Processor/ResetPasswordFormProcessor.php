<?php

namespace Diside\SecurityBundle\Form\Processor;

use Diside\SecurityBundle\Form\Data\ResetPasswordFormData;
use Diside\SecurityBundle\Form\ResetPasswordForm;
use Diside\SecurityComponent\Interactor\InteractorFactory;
use Diside\SecurityComponent\Interactor\Presenter\UserPresenter;
use Diside\SecurityComponent\Interactor\Request\GetUserByResetPasswordTokenRequest;
use Diside\SecurityComponent\Interactor\Request\ResetPasswordRequest;
use Diside\SecurityComponent\Interactor\SecurityInteractorRegister;
use Diside\SecurityComponent\Model\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\SecurityContextInterface;

class ResetPasswordFormProcessor implements UserPresenter
{
    /** @var bool */
    private $isValid = false;

    /** @var array */
    private $errors;

    /** @var ResetPasswordForm */
    private $form;

    /** @var InteractorFactory */
    private $interactorFactory;

    /** @var User */
    private $user;

    /** @var FormFactoryInterface */
    private $factory;

    /** @var EncoderFactoryInterface */
    private $encoderFactory;

    /** @var AbstractType registrationForm */
    private $resetPasswordForm;

    public function __construct(FormFactoryInterface $factory, InteractorFactory $interactorFactory, EncoderFactoryInterface $encoderFactory, AbstractType $resetPasswordForm)
    {
        $this->interactorFactory = $interactorFactory;
        $this->factory = $factory;
        $this->encoderFactory = $encoderFactory;
        $this->resetPasswordForm = $resetPasswordForm;
    }

    public function getForm()
    {
        return $this->form;
    }

    public function process(Request $request, $token)
    {
        $this->form = $this->factory->create($this->resetPasswordForm);

        $this->getUserByResetPasswordToken($token);

        if ($this->hasErrors())
            throw new NotFoundHttpException;

        if ($request->isMethod('POST')) {
            $this->form->handleRequest($request);

            if ($this->form->isValid()) {

                /** @var ResetPasswordFormData $data */
                $data = $this->form->getData();

                $request = $this->buildRequest($data);

                $interactor = $this->interactorFactory->get(SecurityInteractorRegister::RESET_PASSWORD);

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

    private function buildRequest(ResetPasswordFormData $data)
    {
        $user = $this->getUser();

        return new ResetPasswordRequest($user->getId(),
            $this->encodePassword($user, $data->getPassword()));
    }

    private function encodePassword(User $user, $password)
    {
        $encoder = $this->encoderFactory->getEncoder($user);

        return $encoder->encodePassword($password, $user->getSalt());
    }

    protected function getUserByResetPasswordToken($token)
    {
        $interactor = $this->interactorFactory->get(SecurityInteractorRegister::GET_USER);

        $request = new GetUserByResetPasswordTokenRequest($token);

        $interactor->process($request, $this);
        return array($interactor, $request);
    }
}
