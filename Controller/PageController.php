<?php

namespace Diside\SecurityBundle\Controller;

use Diside\SecurityBundle\Presenter\PagePresenter;
use Diside\SecurityComponent\Interactor\Request\GetPageRequest;
use Diside\SecurityComponent\Interactor\SecurityInteractorRegister;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class PageController extends BaseController
{

    /**
     * @Route("/content/{url}", name="show_page")
     * @Template
     */
    public function showAction($url)
    {
        $interactor = $this->getInteractor(SecurityInteractorRegister::GET_PAGE);

        $user = $this->getAuthenticatedUser();

        $request = new GetPageRequest($user ? $user->getId() : null, $url);
        $presenter = new PagePresenter();

        $interactor->process($request, $presenter);

        $page = $presenter->getPage();

        return array(
            'page' => $page,
        );
    }

}
