<?php

namespace Diside\SecurityBundle\Form\Processor;

use Diside\SecurityBundle\Entity\User as UserEntity;
use Diside\SecurityBundle\Exception\UnauthorizedException;
use Diside\SecurityBundle\Form\UserForm;
use Diside\SecurityComponent\Helper\TokenGenerator;
use Diside\SecurityComponent\Interactor\InteractorFactory;
use Diside\SecurityComponent\Interactor\Presenter\UserPresenter;
use Diside\SecurityComponent\Interactor\Request\GetUserByIdRequest;
use Diside\SecurityComponent\Interactor\Request\SaveUserRequest;
use Diside\SecurityComponent\Interactor\SecurityInteractorRegister;
use Diside\SecurityComponent\Model\User;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\SecurityContextInterface;

class UserFormProcessor extends BaseFormProcessor implements UserPresenter
{
    /** @var User */
    private $user;

    protected function buildFormData($id)
    {
        /** @var UserEntity $entity */
        $entity = $this->createEntity('user');

        if ($id != null) {
            $this->retrieveUserById($id);

            $model = $this->getUser();
            $entity->fromModel($model);
        } else {
            $entity->setCompany($this->createEntity('company', $this->getAuthenticatedUser()->getCompany()));
        }

        return $entity;
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
        /** @var UserEntity $data */
        $data = $this->getFormData();

        $model = $data->toModel();

        $request = $this->createRequest(
            'save_user',
            $model,
            array(
                'set_password' => $this->checkPermission('set_password', $model),
                'set_company' => $this->checkPermission('set_company')
            )
        );

        return $request;
    }

    protected function retrieveUserById($id)
    {
        $interactor = $this->getInteractorFactory()->get(SecurityInteractorRegister::GET_USER);

        $request = new GetUserByIdRequest($id);
        $interactor->process($request, $this);

        if ($this->hasErrors()) {
            throw new NotFoundHttpException;
        }

        $user = $this->getUser();

        if (!$this->checkPermission('can_edit', $user)) {
            throw new UnauthorizedException;
        }
    }

    protected function buildForm()
    {
        $user = $this->getAuthenticatedUser();

        return new UserForm($user, $this->getInteractorFactory(), $this->getEntityFactory());
    }

    protected function getSaveInteractorName()
    {
        return SecurityInteractorRegister::SAVE_USER;
    }

    protected function evaluateRedirect()
    {
        $this->setRedirectTo($this->isButtonClicked('save_and_close') ? self::REDIRECT_TO_LIST : null);
    }


}
