<?php

namespace Diside\SecurityBundle\Presenter;

interface PaginatorPresenter
{
    public function count();

    public function getItems();
}
