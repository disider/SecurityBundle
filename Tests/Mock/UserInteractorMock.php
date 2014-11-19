<?php

namespace Diside\SecurityBundle\Tests\Mock;

use Diside\SecurityComponent\Interactor\AbstractInteractor;
use Diside\SecurityComponent\Interactor\Presenter;
use Diside\SecurityComponent\Interactor\Request;
use Diside\SecurityComponent\Model\User;

class UserInteractorMock extends AbstractInteractor
{
    private $request;

    /** @var User */
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function process(Request $request, Presenter $presenter)
    {
        $this->request = $request;

        /** @var UserPresenter $presenter */
        $presenter->setUser($this->user);
    }

    public function getRequest()
    {
        return $this->request;
    }
}