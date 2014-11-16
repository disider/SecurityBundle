<?php

namespace Diside\SecurityBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use SecurityComponent\Interactor\InteractorFactory;
use SecurityComponent\Interactor\Presenter\FindCompaniesPresenter;
use SecurityComponent\Interactor\Presenter\CompanyPresenter;
use SecurityComponent\Interactor\Request\DeleteCompanyRequest;
use SecurityComponent\Interactor\Request\FindCompaniesRequest;
use SecurityComponent\Model\Company;
use Diside\SecurityBundle\Form\Processor\CompanyFormProcessor;
use Diside\SecurityBundle\Presenter\BasePresenter;
use Diside\SecurityBundle\Presenter\PaginatorPresenter;

/**
 * @Route("/companies")
 * @Security("has_role('ROLE_SUPERADMIN')")
 */
class CompanyController extends BaseController
{

    /**
     * @Route("", name="companies")
     * @Template
     */
    public function indexAction(Request $request)
    {
        $page = $request->get('page', 1);
        $pageSize = $this->container->getParameter('page_size');

        $user = $this->getAuthenticatedUser();

        $interactor = $this->getInteractor(InteractorFactory::FIND_COMPANIES);

        $request = new FindCompaniesRequest($user ? $user->getId() : null, $page - 1, $pageSize);
        $presenter = new GuiFindCompaniesPresenter();

        $interactor->process($request, $presenter);

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $presenter,
            $page,
            $pageSize
        );

        return array(
            'pagination' => $pagination
        );
    }

    /**
     * @Route("/new", name="company_new")
     * @Template
     */
    public function newAction(Request $request)
    {
        return $this->processForm($request);
    }

    /**
     * @Route("/{id}/edit", name="company_edit")
     * @Template
     */
    public function editAction(Request $request, $id)
    {
        return $this->processForm($request, $id);
    }

    /**
     * @Route("/{id}/delete", name="company_delete")
     * @Template
     */
    public function deleteAction($id)
    {
        $user = $this->getAuthenticatedUser();

        $interactor = $this->getInteractor(InteractorFactory::DELETE_COMPANY);

        $request = new DeleteCompanyRequest($user ? $user->getId() : null, $id);
        $presenter = new GuiDeleteCompanyPresenter();

        $interactor->process($request, $presenter);

        $this->addFlash('success', 'flash.company.deleted', array('%company%' => $presenter->getCompany()));

        return $this->redirect($this->generateUrl('companies'));
    }

    private function processForm(Request $request, $id = null)
    {
        /** @var CompanyFormProcessor $processor */
        $processor = $this->get('company_form_processor');

        $processor->process($request, $id);

        if ($processor->isValid()) {
            $this->addFlash('success', $id ? 'flash.company.updated' : 'flash.company.created', array('%company%' => $processor->getCompany()));

            if ($processor->isRedirectingTo(CompanyFormProcessor::REDIRECT_TO_LIST))
                return $this->redirect($this->generateUrl('companies'));

            return $this->redirect($this->generateUrl('company_edit', array(
                    'id' => $processor->getCompany()->getId())
            ));
        }

        $form = $processor->getForm();

        return array(
            'errors' => $processor->getErrors(),
            'form' => $form->createView()
        );
    }

}

class GuiFindCompaniesPresenter extends BasePresenter implements PaginatorPresenter, FindCompaniesPresenter
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

class GuiDeleteCompanyPresenter extends BasePresenter implements CompanyPresenter
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