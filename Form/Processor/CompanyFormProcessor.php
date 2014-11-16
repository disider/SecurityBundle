<?php

namespace Diside\SecurityBundle\Form\Processor;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\SecurityContextInterface;
use SecurityComponent\Interactor\InteractorFactory;
use SecurityComponent\Interactor\Presenter\CompanyPresenter;
use SecurityComponent\Interactor\Request\GetCompanyRequest;
use SecurityComponent\Interactor\Request\SaveCompanyRequest;
use SecurityComponent\Model\Company;
use SecurityComponent\Model\User;
use Diside\SecurityBundle\Exception\UnauthorizedException;
use Diside\SecurityBundle\Form\CompanyForm;
use Diside\SecurityBundle\Form\Data\CompanyFormData;

class CompanyFormProcessor extends BaseFormProcessor implements CompanyPresenter
{
    /** @var Company */
    private $company;

    protected function buildFormData($id)
    {
        if ($id != null) {
            $interactor = $this->getInteractorFactory()->get(InteractorFactory::GET_COMPANY);

            $request = new GetCompanyRequest($id);
            $interactor->process($request, $this);

            if ($this->hasErrors()) {
                throw new NotFoundHttpException;
            }

            $company = $this->getCompany();

            return new CompanyFormData($company);
        } else {
            return new CompanyFormData();
        }
    }

    public function getCompany()
    {
        return $this->company;
    }

    public function setCompany(Company $user)
    {
        $this->company = $user;
    }

    protected function buildRequest()
    {
        /** @var CompanyFormData $data */
        $data = $this->getFormData();

        return new SaveCompanyRequest($data->getId(), $data->getName());
    }

    protected function buildForm()
    {
        return new CompanyForm();
    }

    protected function getSaveInteractorName()
    {
        return InteractorFactory::SAVE_COMPANY;
    }

    protected function evaluateRedirect()
    {
        $this->setRedirectTo($this->isButtonClicked('save_and_close') ? self::REDIRECT_TO_LIST : null);
    }
}
