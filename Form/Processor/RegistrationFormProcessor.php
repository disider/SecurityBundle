<?php

namespace Diside\SecurityBundle\Form\Processor;

use Diside\SecurityBundle\Builder\UserBuilder;
use Diside\SecurityBundle\Form\Data\RegistrationFormData;
use Diside\SecurityBundle\Form\RegistrationForm;
use Diside\SecurityComponent\Helper\TokenGenerator;
use Diside\SecurityComponent\Interactor\InteractorFactory;
use Diside\SecurityComponent\Interactor\Presenter\UserPresenter;
use Diside\SecurityComponent\Interactor\Request\RegisterUserRequest;
use Diside\SecurityComponent\Interactor\SecurityInteractorRegister;
use Diside\SecurityComponent\Model\User;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\SecurityContextInterface;

class RegistrationFormProcessor implements UserPresenter
{
    /** @var bool */
    private $isValid = false;

    /** @var array */
    private $errors;

    /** @var RegistrationForm */
    private $form;

    /** @var InteractorFactory */
    private $interactorFactory;

    /** @var User */
    private $user;

    /** @var FormFactoryInterface */
    private $factory;

    /** @var EncoderFactoryInterface */
    private $encoderFactory;
    /** @var UserBuilder */
    private $userBuilder;

    public function __construct(FormFactoryInterface $factory, InteractorFactory $interactorFactory, EncoderFactoryInterface $encoderFactory, UserBuilder $userBuilder)
    {
        $this->interactorFactory = $interactorFactory;
        $this->factory = $factory;
        $this->encoderFactory = $encoderFactory;
        $this->userBuilder = $userBuilder;
    }

    public function getForm()
    {
        return $this->form;
    }

    public function process(Request $request)
    {
        $this->form = $this->factory->create($this->buildRegistrationForm());

        if ($request->isMethod('POST')) {
            $this->form->handleRequest($request);

            if ($this->form->isValid()) {
                /** @var RegistrationFormData $data */
                $data = $this->form->getData();

                $request = $this->buildRequest($data);

                $interactor = $this->interactorFactory->get(SecurityInteractorRegister::REGISTER_USER);

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

    private function buildRequest(RegistrationFormData $data)
    {
        $salt = TokenGenerator::generateToken();
        $user = $this->userBuilder->build('', '', $salt);

        $encoder = $this->encoderFactory->getEncoder($user);

        $request = $this->buildInteractorRequest($data, $encoder, $salt);

        return $request;
    }

    protected function buildInteractorRequest(RegistrationFormData $data, $encoder, $salt)
    {
        $request = new RegisterUserRequest(
            $data->getEmail(),
            $encoder->encodePassword($data->getPassword(), $salt),
            $salt);
        return $request;
    }

    protected function buildRegistrationForm()
    {
        return new RegistrationForm();
    }
}
