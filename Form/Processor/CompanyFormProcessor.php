<?php

namespace Diside\SecurityBundle\Form\Processor;

use Diside\SecurityBundle\Entity\Company as CompanyEntity;
use Diside\SecurityBundle\Form\CompanyForm;
use Diside\SecurityComponent\Interactor\InteractorFactory;
use Diside\SecurityComponent\Interactor\Presenter\CompanyPresenter;
use Diside\SecurityComponent\Interactor\Request\GetCompanyRequest;
use Diside\SecurityComponent\Interactor\Request\SaveCompanyRequest;
use Diside\SecurityComponent\Interactor\SecurityInteractorRegister;
use Diside\SecurityComponent\Model\Company;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\SecurityContextInterface;

class CompanyFormProcessor extends BaseFormProcessor implements CompanyPresenter
{
    /** @var Company */
    private $company;

    protected function buildFormData($id)
    {
        /** @var CompanyEntity $entity */
        $entity = $this->createEntity('company');

        if ($id != null) {
            $company = $this->retrieveCompanyById($id);

            $entity->fromModel($company);
        }

        return $entity;
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
        /** @var CompanyEntity $data */
        $data = $this->getFormData();

        return $this->createRequest('save_company', $data);
    }

    protected function buildForm()
    {
        return new CompanyForm($this->getEntityFactory());
    }

    protected function getSaveInteractorName()
    {
        return SecurityInteractorRegister::SAVE_COMPANY;
    }

    protected function evaluateRedirect()
    {
        $this->setRedirectTo($this->isButtonClicked('save_and_close') ? self::REDIRECT_TO_LIST : null);
    }

    /**
     * @param $id
     * @return Company
     */
    protected function retrieveCompanyById($id)
    {
        $interactor = $this->getInteractorFactory()->get(SecurityInteractorRegister::GET_COMPANY);

        $request = new GetCompanyRequest($id);
        $interactor->process($request, $this);

        if ($this->hasErrors()) {
            throw new NotFoundHttpException;
        }

        return $this->getCompany();
    }
}
