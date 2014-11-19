<?php

namespace Diside\SecurityBundle\Presenter;

use Diside\SecurityComponent\Interactor\Presenter\CompaniesPresenter as FindCompaniesPresenterInterface;

class CompaniesPresenter extends BasePresenter implements PaginatorPresenter, FindCompaniesPresenterInterface
{
    private $companies;
    private $total;

    public function getCompanies()
    {
        return $this->companies;
    }

    public function setCompanies(array $companies)
    {
        $this->companies = $companies;
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
        return $this->companies;
    }

    public function getTotalCompanies()
    {
        return $this->total;
    }

    public function setTotalCompanies($total)
    {
        $this->total = $total;
    }
}