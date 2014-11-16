<?php

namespace Diside\SecurityBundle\Presenter;

use SecurityComponent\Interactor\Presenter\UserPresenter as UserPresenterInterface;
use SecurityComponent\Model\User;

class UserPresenter extends BasePresenter implements UserPresenterInterface
{
    private $user;

    public function getUser()
    {
        return $this->user;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }
}
