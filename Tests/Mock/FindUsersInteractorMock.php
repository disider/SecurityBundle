<?php

namespace Diside\SecurityBundle\Tests\Mock;

use SecurityComponent\Interactor\Interactor;
use SecurityComponent\Interactor\Presenter;
use SecurityComponent\Interactor\Presenter\FindUsersPresenter;
use SecurityComponent\Interactor\Request;

class FindUsersInteractorMock implements Interactor
{
    public function process(Request $request, Presenter $presenter)
    {
        /** @var FindUsersPresenter $presenter */
        $presenter->setUsers(array());
    }
}
