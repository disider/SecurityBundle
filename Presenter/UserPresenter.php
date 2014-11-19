<?php

namespace Diside\SecurityBundle\Presenter;

use Diside\SecurityComponent\Interactor\Presenter\UserPresenter as UserPresenterInterface;
use Diside\SecurityComponent\Model\User;

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
