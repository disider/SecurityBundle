<?php

namespace Diside\SecurityBundle\Form\Processor;

use Diside\SecurityBundle\Form\PageForm;
use Diside\SecurityBundle\Form\Data\PageFormData;
use Diside\SecurityComponent\Interactor\InteractorFactory;
use Diside\SecurityComponent\Interactor\Presenter\PagePresenter;
use Diside\SecurityComponent\Interactor\Request\GetPageByIdRequest;
use Diside\SecurityComponent\Interactor\Request\GetPageByLanguageAndUrlRequest;
use Diside\SecurityComponent\Interactor\Request\SavePageRequest;
use Diside\SecurityComponent\Interactor\SecurityInteractorRegister;
use Diside\SecurityComponent\Model\Page;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\SecurityContextInterface;

class PageFormProcessor extends BaseFormProcessor implements PagePresenter
{
    /** @var Page */
    private $page;

    protected function buildFormData($id)
    {
        if ($id != null) {
            $interactor = $this->getInteractorFactory()->get(SecurityInteractorRegister::GET_PAGE);

            $user = $this->getAuthenticatedUser();

            $request = new GetPageByIdRequest($user->getId(), $id);
            $interactor->process($request, $this);

            if ($this->hasErrors()) {
                throw new NotFoundHttpException;
            }

            $page = $this->getPage();

            return new PageFormData($page);
        } else {
            return new PageFormData();
        }
    }

    public function getPage()
    {
        return $this->page;
    }

    public function setPage(Page $user)
    {
        $this->page = $user;
    }

    protected function buildRequest()
    {
        /** @var PageFormData $data */
        $data = $this->getFormData();

        $user = $this->getAuthenticatedUser();

        return new SavePageRequest($user->getId(), $data->getId());
    }

    protected function buildForm()
    {
        return new PageForm();
    }

    protected function getSaveInteractorName()
    {
        return SecurityInteractorRegister::SAVE_PAGE;
    }

    protected function evaluateRedirect()
    {
        $this->setRedirectTo($this->isButtonClicked('save_and_close') ? self::REDIRECT_TO_LIST : null);
    }
}
