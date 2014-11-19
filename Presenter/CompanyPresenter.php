<?php

namespace Diside\SecurityBundle\Presenter;

use Diside\SecurityComponent\Interactor\Presenter\CompanyPresenter as CompanyPresenterInterface;
use Diside\SecurityComponent\Model\Company;

class CompanyPresenter extends BasePresenter implements CompanyPresenterInterface
{
    /** @var Company */
    private $company;

    public function getCompany()
    {
        return $this->company;
    }

    public function setCompany(Company $company)
    {
        $this->company = $company;
    }
}