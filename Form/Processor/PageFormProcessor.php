<?php

namespace Diside\SecurityBundle\Form\Processor;

use Diside\SecurityBundle\Entity\Page as PageEntity;
use Diside\SecurityBundle\Factory\EntityFactory;
use Diside\SecurityBundle\Factory\RequestFactory;
use Diside\SecurityBundle\Form\Data\PageTranslationFormData;
use Diside\SecurityBundle\Form\PageForm;
use Diside\SecurityBundle\Provider\LocaleProvider;
use Diside\SecurityBundle\Security\PermissionChecker;
use Diside\SecurityComponent\Interactor\InteractorFactory;
use Diside\SecurityComponent\Interactor\Presenter\PagePresenter;
use Diside\SecurityComponent\Interactor\Request\GetPageByIdRequest;
use Diside\SecurityComponent\Interactor\Request\GetPageByLanguageAndUrlRequest;
use Diside\SecurityComponent\Interactor\Request\SavePageRequest;
use Diside\SecurityComponent\Interactor\SecurityInteractorRegister;
use Diside\SecurityComponent\Model\Page;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\SecurityContextInterface;

class PageFormProcessor extends BaseFormProcessor implements PagePresenter
{
    /** @var Page */
    private $page;
    /**
     * @var LocaleProvider
     */
    private $localeProvider;

    public function __construct(
        FormFactoryInterface $formFactory,
        InteractorFactory $interactorFactory,
        SecurityContextInterface $securityContext,
        EntityFactory $entityFactory,
        RequestFactory $requestFactory,
        PermissionChecker $permissionChecker,
        LocaleProvider $localeProvider
    ) {
        parent::__construct(
            $formFactory,
            $interactorFactory,
            $securityContext,
            $entityFactory,
            $requestFactory,
            $permissionChecker
        );

        $this->localeProvider = $localeProvider;
    }


    protected function buildFormData($id)
    {
        /** @var PageEntity $page */
        $page = $this->createEntity('page');

        if ($id != null) {
            $this->retrievePageById($id);

            $page->fromModel($this->getPage());
        }
        else {
            $page->setLocale($this->localeProvider->getDefaultLocale());
        }

        return $page;
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
        /** @var PageEntity $data */
        $data = $this->getFormData();

        return $this->createRequest('save_page', $data);
    }

    protected function buildForm()
    {
        return new PageForm($this->localeProvider->getAvailableLocales(), $this->getEntityFactory());
    }

    protected function getSaveInteractorName()
    {
        return SecurityInteractorRegister::SAVE_PAGE;
    }

    protected function evaluateRedirect()
    {
        $this->setRedirectTo($this->isButtonClicked('save_and_close') ? self::REDIRECT_TO_LIST : null);
    }

    /**
     * @param $id
     */
    protected function retrievePageById($id)
    {
        $interactor = $this->getInteractorFactory()->get(SecurityInteractorRegister::GET_PAGE);

        $user = $this->getAuthenticatedUser();

        $request = new GetPageByIdRequest($user->getId(), $id);
        $interactor->process($request, $this);

        if ($this->hasErrors()) {
            throw new NotFoundHttpException;
        }
    }
}
