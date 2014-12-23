<?php

namespace Diside\SecurityBundle\Form\Processor;

use Diside\SecurityBundle\Exception\UnauthorizedException;
use Diside\SecurityBundle\Form\ChangePasswordForm;
use Diside\SecurityBundle\Form\Data\ChangePasswordFormData;
use Diside\SecurityBundle\Security\LoggedUser;
use Diside\SecurityComponent\Interactor\InteractorFactory;
use Diside\SecurityComponent\Interactor\Presenter\UserPresenter;
use Diside\SecurityComponent\Interactor\Request\ChangePasswordRequest;
use Diside\SecurityComponent\Interactor\SecurityInteractorRegister;
use Diside\SecurityComponent\Model\User;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\SecurityContextInterface;

class ChangePasswordFormProcessor extends BaseFormProcessor implements UserPresenter
{
    /** @var User */
    private $user;

    /** @var User */
    public function process(Request $request, $id = null)
    {
        $user = $this->getAuthenticatedUser();

        if ($user->getId() != $id)
            throw new UnauthorizedException;

        parent::process($request, $id);
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    protected function buildRequest()
    {
        /** @var ChangePasswordFormData $data */
        $data = $this->getFormData();

        return $this->createRequest('change_password', $data);
    }

    protected function getSaveInteractorName()
    {
        return SecurityInteractorRegister::SAVE_USER;
    }

    protected function buildForm()
    {
        return new ChangePasswordForm();
    }

    protected function buildFormData($id)
    {
        return new ChangePasswordFormData();
    }
}
