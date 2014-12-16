<?php

namespace Diside\SecurityBundle\Presenter;

use Diside\SecurityComponent\Interactor\Presenter\PagePresenter as PagePresenterInterface;
use Diside\SecurityComponent\Model\Page;

class PagePresenter extends BasePresenter implements PagePresenterInterface
{
    private $page;

    public function getPage()
    {
        return $this->page;
    }

    public function setPage(Page $page)
    {
        $this->page = $page;
    }
}
