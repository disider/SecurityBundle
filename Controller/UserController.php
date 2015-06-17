<?php

namespace Diside\SecurityBundle\Controller;

use Diside\SecurityBundle\Form\Processor\UserFormProcessor;
use Diside\SecurityBundle\Presenter\UserPresenter;
use Diside\SecurityBundle\Presenter\UsersPresenter;
use Diside\SecurityComponent\Interactor\Presenter\FindUsersPresenter;
use Diside\SecurityComponent\Interactor\Request\DeleteUserRequest;
use Diside\SecurityComponent\Interactor\Request\FindUsersRequest;
use Diside\SecurityComponent\Interactor\SecurityInteractorRegister;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/users")
 * @Security("has_role('ROLE_ADMIN')")
 */
class UserController extends BaseController
{

    /**
     * @Route("", name="users")
     * @Security("has_role('ROLE_MANAGER')")
     * @Template
     */
    public function indexAction(Request $request)
    {
        $page = $request->get('page', 1);
        $pageSize = $this->container->getParameter('page_size');

        $user = $this->getAuthenticatedUser();

        $interactor = $this->getInteractor(SecurityInteractorRegister::FIND_USERS);

        $request = new FindUsersRequest($user ? $user->getId() : null, $page - 1, $pageSize);
        $presenter = new UsersPresenter();

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
     * @Route("/new", name="user_new")
     * @Security("has_role('ROLE_ADMIN')")
     * @Template
     */
    public function newAction(Request $request)
    {
        return $this->processForm($request);
    }

    /**
     * @Route("/{id}/edit", name="user_edit")
     * @Security("has_role('ROLE_USER')")
     * @Template
     */
    public function editAction(Request $request, $id)
    {
        return $this->processForm($request, $id);
    }

    /**
     * @Route("/{id}/change-password", name="user_change_password")
     * @Security("has_role('ROLE_USER')")
     * @Template
     */
    public function changePasswordAction(Request $request, $id)
    {
        /** @var ChangePasswordFormProcessor $processor */
        $processor = $this->get('change_password_form_processor');

        $processor->process($request, $id);

        if ($processor->isValid()) {

            $this->addFlash('success', 'flash.password.updated');

            return $this->redirect($this->generateUrl('user_edit', array(
                    'id' => $processor->getUser()->getId())
            ));

        }

        $form = $processor->getForm();

        return array(
            'errors' => $processor->getErrors(),
            'form' => $form->createView()
        );
    }

    /**
     * @Route("/{id}/delete", name="user_delete")
     * @Security("has_role('ROLE_ADMIN')")
     * @Template
     */
    public function deleteAction($id)
    {
        $user = $this->getAuthenticatedUser();

        $interactor = $this->getInteractor(SecurityInteractorRegister::DELETE_USER);

        $request = new DeleteUserRequest($user ? $user->getId() : null, $id);
        $presenter = new UserPresenter();

        $interactor->process($request, $presenter);

        if ($presenter->hasErrors())
            $this->throwErrors($presenter->getErrors());

        $this->addFlash('success', 'flash.user.deleted', array('%user%' => $presenter->getUser()));

        return $this->redirect($this->generateUrl('users'));
    }

    private function processForm(Request $request, $id = null)
    {
        /** @var UserFormProcessor $processor */
        $processor = $this->get('user_form_processor');

        $user = $this->getAuthenticatedUser();

        $processor->process($request, $id);

        if ($processor->isValid()) {

            $this->addFlash('success', $id ? 'flash.user.updated' : 'flash.user.created', array('%user%' => $processor->getUser()));

            if ($processor->isRedirectingTo(UserFormProcessor::REDIRECT_TO_LIST)) {
                if ($user->isAdmin())
                    return $this->redirect($this->generateUrl('users'));

                return $this->redirect($this->generateUrl('homepage'));
            }

            return $this->redirect($this->generateUrl('user_edit', array(
                    'id' => $processor->getUser()->getId())
            ));

        }

        $form = $processor->getForm();

        return array(
            'errors' => $processor->getErrors(),
            'form' => $form->createView()
        );
    }

}
