<?php

namespace Diside\SecurityBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Diside\SecurityComponent\Interactor\Interactor\InteractorFactory;
use Diside\SecurityBundle\Form\Processor\RequestResetPasswordFormProcessor;
use Diside\SecurityBundle\Form\Processor\ResetPasswordFormProcessor;
use Diside\SecurityBundle\Mailer\Mailer;
use Diside\SecurityBundle\Presenter\UserPresenter;

class ResetPasswordController extends BaseController
{

    /**
     * @Route("/reset-password", name="reset_password_request")
     * @Template
     */
    public function requestResetPasswordAction(Request $request)
    {
        if ($this->getAuthenticatedUser() != null)
            return $this->redirect($this->generateUrl('homepage'));

        /** @var RequestResetPasswordFormProcessor $processor */
        $processor = $this->get('request_reset_password_form_processor');

        $processor->process($request);

        if ($processor->isValid()) {
            $user = $processor->getUser();

            $mailer = $this->getMailer();

            $mailer->sendResetPasswordRequestEmailTo($user);

            return new RedirectResponse($this->generateUrl('reset_password_request_sent'));
        }

        $form = $processor->getForm();

        return array(
            'errors' => $processor->getErrors(),
            'form' => $form->createView()
        );
    }

    /**
     * @Route("/reset-password/request-sent", name="reset_password_request_sent")
     * @Template
     */
    public function resetPasswordRequestSentAction()
    {
        if ($this->getAuthenticatedUser() != null)
            return $this->redirect($this->generateUrl('homepage'));

        return array();
    }

    /**
     * @Route("/reset-password/reset/{token}", name="reset_password")
     * @Template
     */
    public function resetPasswordAction(Request $request, $token)
    {
        if ($this->getAuthenticatedUser() != null)
            return $this->redirect($this->generateUrl('homepage'));

        /** @var ResetPasswordFormProcessor $processor */
        $processor = $this->get('reset_password_form_processor');

        $processor->process($request, $token);

        if ($processor->isValid()) {
            return new RedirectResponse($this->generateUrl('reset_password_completed'));
        }

        $form = $processor->getForm();

        return array(
            'errors' => $processor->getErrors(),
            'form' => $form->createView()
        );
    }

    /**
     * @Route("/reset-password/thank-you", name="reset_password_completed")
     * @Template
     */
    public function resetPasswordCompletedAction()
    {
        if ($this->getAuthenticatedUser() != null)
            return $this->redirect($this->generateUrl('homepage'));

        return array();
    }

}
