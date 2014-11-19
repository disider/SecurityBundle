<?php

namespace Diside\SecurityBundle\Controller;

use Diside\SecurityBundle\Presenter\LogsPresenter;
use Diside\SecurityComponent\Interactor\InteractorFactory;
use Diside\SecurityComponent\Interactor\Presenter\LogPresenter;
use Diside\SecurityComponent\Interactor\Request\DeleteLogRequest;
use Diside\SecurityComponent\Interactor\Request\FindLogsRequest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Whalist\ChecklistComponent\Interactor\SecurityInteractorRegister;
use Whalist\FrontendBundle\Form\Filter\LogFilterForm;
use Whalist\FrontendBundle\Form\Processor\LogFormProcessor;
use Whalist\FrontendBundle\Presenter\BasePresenter;
use Whalist\FrontendBundle\Presenter\PaginatorPresenter;

/**
 * @Route("/logs")
 * @Security("has_role('ROLE_ADMIN')")
 */
class LogController extends BaseController
{

    /**
     * @Route("", name="logs")
     * @Security("has_role('ROLE_MANAGER')")
     * @Template
     */
    public function indexAction(Request $request)
    {
        $page = $request->get('page', 1);
        $pageSize = $this->container->getParameter('page_size');

        $user = $this->getAuthenticatedUser();

        $interactor = $this->getInteractor(SecurityInteractorRegister::FIND_LOGS);

        $filterForm = $this->createForm(new LogFilterForm());
        $filters = $this->applyFiltering($request, $filterForm);

        $request = new FindLogsRequest($user ? $user->getId() : null, $page - 1, $pageSize, $filters);
        $presenter = new LogsPresenter();

        $interactor->process($request, $presenter);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $presenter,
            $page,
            $pageSize
        );

        return array(
            'pagination' => $pagination,
            'filterForm' => $filterForm->createView()
        );
    }

}
