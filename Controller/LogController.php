<?php

namespace Diside\SecurityBundle\Controller;

use CheckIt\FrontendBundle\Form\Filter\LogFilterForm;
use Diside\SecurityBundle\Presenter\LogsPresenter;
use Diside\SecurityComponent\Interactor\InteractorFactory;
use Diside\SecurityComponent\Interactor\Presenter\LogPresenter;
use Diside\SecurityComponent\Interactor\Request\DeleteLogRequest;
use Diside\SecurityComponent\Interactor\Request\FindLogsRequest;
use Diside\SecurityComponent\Interactor\SecurityInteractorRegister;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

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
