<?php

namespace Diside\SecurityBundle\Form\Processor;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\SecurityContextInterface;
use SecurityComponent\Interactor\InteractorFactory;
use SecurityComponent\Interactor\Presenter\UserPresenter;
use SecurityComponent\Interactor\Request\GetUserByResetPasswordTokenRequest;
use SecurityComponent\Interactor\Request\ResetPasswordRequest;
use SecurityComponent\Model\User;
use Diside\SecurityBundle\Form\Data\ResetPasswordFormData;
use Diside\SecurityBundle\Form\ResetPasswordForm;

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

    public function __construct(FormFactoryInterface $factory, InteractorFactory $interactorFactory, EncoderFactoryInterface $encoderFactory)
    {
        $this->interactorFactory = $interactorFactory;
        $this->factory = $factory;
        $this->encoderFactory = $encoderFactory;
    }

    public function getForm()
    {
        return $this->form;
    }

    public function process(Request $request, $token)
    {
        $this->form = $this->factory->create(new ResetPasswordForm());

        $this->getUserByResetPasswordToken($token);

        if($this->hasErrors())
            throw new NotFoundHttpException;

        if ($request->isMethod('POST')) {
            $this->form->handleRequest($request);

            if ($this->form->isValid()) {

                /** @var ResetPasswordFormData $data */
                $data = $this->form->getData();

                $request = $this->buildRequest($data);

                $interactor = $this->interactorFactory->get(InteractorFactory::RESET_PASSWORD);

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
        $interactor = $this->interactorFactory->get(InteractorFactory::GET_USER);

        $request = new GetUserByResetPasswordTokenRequest($token);

        $interactor->process($request, $this);
        return array($interactor, $request);
    }

}
