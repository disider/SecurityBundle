<?php

namespace Diside\SecurityBundle\Tests\Mock;

use SecurityComponent\Interactor\Interactor;
use SecurityComponent\Interactor\Presenter;
use SecurityComponent\Interactor\Request;

class DummyInteractor implements Interactor
{
    public function process(Request $request, Presenter $presenter)
    {
    }
}
