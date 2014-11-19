<?php

namespace Diside\SecurityBundle\Presenter;

use Diside\SecurityComponent\Interactor\Presenter\UsersPresenter as UsersPresenterInterface;

class UsersPresenter extends BasePresenter implements PaginatorPresenter, UsersPresenterInterface
{
    private $users;
    private $total;

    public function getUsers()
    {
        return $this->users;
    }

    public function setUsers(array $users)
    {
        $this->users = $users;
    }

    public function setCount($count)
    {
        $this->total = $count;
    }

    public function count()
    {
        return $this->total;
    }

    public function getItems()
    {
        return $this->users;
    }

    public function getTotalUsers()
    {
        return $this->total;
    }

    public function setTotalUsers($total)
    {
        $this->total = $total;
    }
}
