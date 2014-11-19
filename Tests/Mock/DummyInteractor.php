<?php

namespace Diside\SecurityBundle\Tests\Mock;

use Diside\SecurityComponent\Interactor\Interactor\Interactor;
use Diside\SecurityComponent\Interactor\Interactor\Presenter;
use Diside\SecurityComponent\Interactor\Interactor\Request;

class DummyInteractor implements Interactor
{
    public function process(Request $request, Presenter $presenter)
    {
    }
}
