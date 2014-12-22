<?php

namespace Diside\SecurityBundle\Controller;

use Diside\SecurityBundle\Form\Processor\RegistrationFormProcessor;
use Diside\SecurityBundle\Presenter\UserPresenter;
use Diside\SecurityComponent\Interactor\Request\ConfirmUserRegistrationRequest;
use Diside\SecurityComponent\Interactor\SecurityInteractorRegister;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class RegistrationController extends BaseController
{

    /**
     * @Route("/register", name="register")
     * @Template
     */
    public function registerAction(Request $request)
    {
        if ($this->getAuthenticatedUser() != null)
            return $this->redirect($this->generateUrl('homepage'));

        /** @var RegistrationFormProcessor $processor */
        $processor = $this->get('registration_form_processor');

        $processor->process($request);

        if ($processor->isValid()) {
            $user = $processor->getUser();

            $mailer = $this->getMailer();

            $mailer->sendConfirmRegistrationEmailTo($user);

            return new RedirectResponse($this->generateUrl('request_registration_confirmation'));
        }

        $form = $processor->getForm();

        return array(
            'errors' => $processor->getErrors(),
            'form' => $form->createView()
        );
    }

    /**
     * @Route("/register/request-confirmation", name="request_registration_confirmation")
     * @Template
     */
    public function requestRegistrationConfirmationAction()
    {
        if ($this->getAuthenticatedUser() != null)
            return $this->redirect($this->generateUrl('homepage'));

        return array();
    }

    /**
     * @Route("/register/confirm/{token}", name="confirm_registration")
     * @Template
     */
    public function confirmRegistrationAction($token)
    {
        if ($this->getAuthenticatedUser() != null)
            return $this->redirect($this->generateUrl('homepage'));

        $interactor = $this->getInteractor(SecurityInteractorRegister::CONFIRM_USER_REGISTRATION);

        $presenter = new UserPresenter();

        $request = new ConfirmUserRegistrationRequest($token);
        $interactor->process($request, $presenter);

        if ($presenter->hasErrors()) {
            $this->throwErrors($presenter->getErrors());
        }

        $user = $presenter->getUser();

        $mailer = $this->getMailer();

        $mailer->sendRegistrationCompletedEmailTo($user);

        return new RedirectResponse($this->generateUrl('registration_completed'));
    }

    /**
     * @Route("/register/thank-you", name="registration_completed")
     * @Template
     */
    public function registrationCompletedAction()
    {
        if ($this->getAuthenticatedUser() != null)
            return $this->redirect($this->generateUrl('homepage'));

        return array();
    }
}
