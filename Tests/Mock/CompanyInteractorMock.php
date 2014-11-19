<?php

namespace Diside\SecurityBundle\Tests\Mock;


use Diside\SecurityComponent\Interactor\AbstractInteractor;
use Diside\SecurityComponent\Interactor\Presenter;
use Diside\SecurityComponent\Interactor\Request;
use Diside\SecurityComponent\Model\Company;

class CompanyInteractorMock extends AbstractInteractor
{
    /** @var Company */
    private $company;

    public function __construct(Company $company)
    {
        $this->company = $company;
    }

    public function process(Request $request, Presenter $presenter)
    {
        /** @var CompanyPresenter $presenter */
        $presenter->setCompany($this->company);
    }
}
