<?php

namespace Diside\SecurityBundle\Presenter;

use Diside\SecurityComponent\Interactor\Presenter\PagesPresenter as PagesPresenterInterface;

class PagesPresenter extends BasePresenter implements PaginatorPresenter, PagesPresenterInterface
{
    /** @var array */
    private $pages;

    /** @var int */
    private $total;

    public function getPages()
    {
        return $this->pages;
    }

    public function setPages(array $pages)
    {
        $this->pages = $pages;
    }

    public function count()
    {
        return $this->total;
    }

    public function getItems()
    {
        return $this->pages;
    }

    public function getTotalPages()
    {
        return $this->total;
    }

    public function setTotalPages($total)
    {
        $this->total = $total;
    }
}