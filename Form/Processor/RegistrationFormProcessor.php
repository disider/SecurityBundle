<?php

namespace Diside\SecurityBundle\Form\Processor;

use Diside\SecurityBundle\Factory\EntityFactory;
use Diside\SecurityBundle\Factory\RequestFactory;
use Diside\SecurityBundle\Form\Data\RegistrationFormData;
use Diside\SecurityBundle\Form\RegistrationForm;
use Diside\SecurityBundle\Presenter\BasePresenter;
use Diside\SecurityComponent\Helper\TokenGenerator;
use Diside\SecurityComponent\Interactor\InteractorFactory;
use Diside\SecurityComponent\Interactor\Presenter\UserPresenter;
use Diside\SecurityComponent\Interactor\Request\RegisterUserRequest;
use Diside\SecurityComponent\Interactor\SecurityInteractorRegister;
use Diside\SecurityComponent\Model\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\SecurityContextInterface;

class RegistrationFormProcessor extends BasePresenter implements UserPresenter
{
    /** @var bool */
    private $isValid = false;

    /** @var RegistrationForm */
    private $form;

    /** @var InteractorFactory */
    private $interactorFactory;

    /** @var User */
    private $user;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var EntityFactory */
    private $entityFactory;

    /** @var RequestFactory */
    private $requestFactory;

    /** @var AbstractType registrationForm */
    private $registrationForm;

    public function __construct(
        FormFactoryInterface $factory,
        InteractorFactory $interactorFactory,
        EntityFactory $entityFactory,
        RequestFactory $requestFactory,
        AbstractType $registrationForm
    ) {
        $this->interactorFactory = $interactorFactory;
        $this->formFactory = $factory;
        $this->entityFactory = $entityFactory;
        $this->requestFactory = $requestFactory;
        $this->registrationForm = $registrationForm;
    }

    public function getForm()
    {
        return $this->form;
    }

    public function process(Request $request)
    {
        $this->form = $this->formFactory->create($this->registrationForm);

        if ($request->isMethod('POST')) {
            $this->form->handleRequest($request);

            if ($this->form->isValid()) {
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

    public function getUser()
    {
        return $this->user;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    private function buildRequest($data)
    {
        $user = $this->entityFactory->create('user');
        $user->setSalt(TokenGenerator::generateToken());

        return $this->requestFactory->create('register_user', $data, array('user' => $user->toModel()));
    }
}
