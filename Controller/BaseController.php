<?php

namespace Diside\SecurityBundle\Controller;

use Diside\SecurityBundle\Exception\UnauthorizedException;
use Diside\SecurityBundle\Mailer\Mailer;
use SecurityComponent\Interactor\Interactor;
use SecurityComponent\Interactor\InteractorFactory;
use SecurityComponent\Interactor\Presenter;
use SecurityComponent\Interactor\Presenter\UserPresenter;
use SecurityComponent\Model\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\RememberMeToken;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class BaseController extends Controller
{
    /** @return User */
    protected function getAuthenticatedUser()
    {
        $token = $this->get('security.context')->getToken();

        if ($token instanceof UsernamePasswordToken || $token instanceof RememberMeToken)
            return $token->getUser();

        return null;
    }

    /** @return InteractorFactory */
    protected function getInteractorFactory()
    {
        return $this->get('diside.security.interactor.interactor_factory');
    }

    /** @return Interactor */
    protected function getInteractor($type)
    {
        return $this->getInteractorFactory()->get($type);
    }

    protected function translate($id, $params)
    {
        /** @var Translator $translator */
        $translator = $this->get('translator');

        /** @Ignore */
        return $translator->trans($id, $params);
    }

    protected function addFlash($type, $id, array $params = array())
    {
        /** @var FlashBag $flashBag */
        $flashBag = $this->get('session')->getFlashBag();

        $flashBag->add(
            $type,
            $this->translate($id, $params)
        );
    }

    protected function throwErrors(array $errors)
    {
        foreach ($errors as $error) {
            switch ($error) {
                case UserPresenter::UNDEFINED_USER:
                    throw new UnauthorizedException('Undefined user');
                case Presenter::NOT_FOUND;
                    throw $this->createNotFoundException();
                case Presenter::FORBIDDEN:
                    throw new UnauthorizedException();
                default:
                    throw new \Exception('Unmapped error: ' . $error);
            }
        }
    }

    protected function applyFiltering(Request $request, $filterForm)
    {
        $filterForm->handleRequest($request);

        return $filterForm->isValid() ? $filterForm->getData()->getFilters() : array();
    }

    /** @return Mailer $mailer */
    protected function getMailer()
    {
        return $this->get('security.mailer');
    }

}
