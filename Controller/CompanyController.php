<?php

namespace Diside\SecurityBundle\Controller;

use Diside\SecurityBundle\Form\Processor\CompanyFormProcessor;
use Diside\SecurityBundle\Presenter\CompaniesPresenter;
use Diside\SecurityBundle\Presenter\CompanyPresenter;
use Diside\SecurityComponent\Interactor\Request\DeleteCompanyRequest;
use Diside\SecurityComponent\Interactor\Request\FindCompaniesRequest;
use Diside\SecurityComponent\Interactor\SecurityInteractorRegister;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

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

        $interactor = $this->getInteractor(SecurityInteractorRegister::FIND_COMPANIES);

        $request = new FindCompaniesRequest($user ? $user->getId() : null, $page - 1, $pageSize);
        $presenter = new CompaniesPresenter();

        $interactor->process($request, $presenter);

        $paginator = $this->get('knp_paginator');
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

        $interactor = $this->getInteractor(SecurityInteractorRegister::DELETE_COMPANY);

        $request = new DeleteCompanyRequest($user ? $user->getId() : null, $id);
        $presenter = new CompanyPresenter();

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

