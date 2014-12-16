<?php

namespace Diside\SecurityBundle\Controller;

use AppBundle\Presenter\PagesPresenter;
use Diside\SecurityBundle\Form\Processor\PageFormProcessor;
use Diside\SecurityBundle\Presenter\PagePresenter;
use Diside\SecurityComponent\Interactor\Request\DeletePageRequest;
use Diside\SecurityComponent\Interactor\Request\FindPagesRequest;
use Diside\SecurityComponent\Interactor\Request\GetPageByLanguageAndUrlRequest;
use Diside\SecurityComponent\Interactor\SecurityInteractorRegister;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class PageController extends BaseController
{
    /**
     * @Route("/pages", name="pages")
     * @Template
     */
    public function indexAction(Request $request)
    {
        $page = $request->get('page', 1);
        $pageSize = $this->container->getParameter('page_size');

        $user = $this->getAuthenticatedUser();

        $interactor = $this->getInteractor(SecurityInteractorRegister::FIND_PAGES);

        $filters = array();

        $request = new FindPagesRequest($user ? $user->getId() : null, $page - 1, $pageSize, $filters);
        $presenter = new PagesPresenter();

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
     * @Route("/content/{url}", name="page_show")
     * @Template
     */
    public function showAction(Request $request, $url)
    {
        $locale = $request->getLocale();
        $interactor = $this->getInteractor(SecurityInteractorRegister::GET_PAGE);

        $user = $this->getAuthenticatedUser();

        $request = new GetPageByLanguageAndUrlRequest($user ? $user->getId() : null, $locale, $url);
        $presenter = new PagePresenter();

        $interactor->process($request, $presenter);

        $page = $presenter->getPage();

        return array(
            'page' => $page,
        );
    }


    /**
     * @Route("/pages/new", name="page_new")
     * @Template
     */
    public function newAction(Request $request)
    {
        return $this->processForm($request);
    }

    /**
     * @Route("/pages/{id}/edit", name="page_edit")
     * @Template
     */
    public function editAction(Request $request, $id)
    {
        return $this->processForm($request, $id);
    }

    /**
     * @Route("/pages/{id}/delete", name="page_delete")
     * @Template
     */
    public function deleteAction($id)
    {
        $user = $this->getAuthenticatedUser();

        $interactor = $this->getInteractor(SecurityInteractorRegister::DELETE_PAGE);

        $request = new DeletePageRequest($user ? $user->getId() : null, $id);
        $presenter = new PagePresenter();

        $interactor->process($request, $presenter);

        $this->addFlash('success', 'flash.page.deleted', array('%page%' => $presenter->getPage()));

        return $this->redirect($this->generateUrl('pages'));
    }

    private function processForm(Request $request, $id = null)
    {
        /** @var PageFormProcessor $processor */
        $processor = $this->get('page_form_processor');

        $processor->process($request, $id);

        if ($processor->isValid()) {
            $this->addFlash('success', $id ? 'flash.page.updated' : 'flash.page.created', array('%page%' => $processor->getPage()));

            if ($processor->isRedirectingTo(PageFormProcessor::REDIRECT_TO_LIST))
                return $this->redirect($this->generateUrl('pages'));

            return $this->redirect($this->generateUrl('page_edit', array(
                    'id' => $processor->getPage()->getId())
            ));
        }

        $form = $processor->getForm();

        return array(
            'errors' => $processor->getErrors(),
            'form' => $form->createView()
        );
    }
    
}
