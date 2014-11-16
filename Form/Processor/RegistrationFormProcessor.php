<?php

namespace Diside\SecurityBundle\Form\Processor;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\SecurityContextInterface;
use SecurityComponent\Helper\TokenGenerator;
use SecurityComponent\Interactor\InteractorFactory;
use SecurityComponent\Interactor\Presenter\UserPresenter;
use SecurityComponent\Interactor\Request\RegisterUserRequest;
use SecurityComponent\Model\User;
use Diside\SecurityBundle\Form\Data\RegistrationFormData;
use Diside\SecurityBundle\Form\RegistrationForm;

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

    public function process(Request $request)
    {
        $this->form = $this->factory->create(new RegistrationForm());

        if ($request->isMethod('POST')) {
            $this->form->handleRequest($request);

            if ($this->form->isValid()) {
                /** @var RegistrationFormData $data */
                $data = $this->form->getData();

                $request = $this->buildRequest($data);

                $interactor = $this->interactorFactory->get(InteractorFactory::REGISTER_USER);

                $interactor->process($request, $this);

//                if($this->hasErrors()) {
//                    throw new EmailNotUniqueException;
//                }

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
        $user = new User(null, '', '', $salt);

        $encoder = $this->encoderFactory->getEncoder($user);

        $request = new RegisterUserRequest(
            $data->getEmail(),
            $encoder->encodePassword($data->getPassword(), $salt),
            $salt);

        return $request;
    }
}
