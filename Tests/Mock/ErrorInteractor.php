<?php

namespace Diside\SecurityBundle\Tests\Mock;

use SecurityComponent\Interactor\Interactor;
use SecurityComponent\Interactor\Presenter;
use SecurityComponent\Interactor\Request;

class ErrorInteractor implements Interactor
{
    /** @var string */
    private $error;

    public function __construct($error)
    {
        $this->error = $error;
    }

    public function process(Request $request, Presenter $presenter)
    {
        $presenter->setErrors(array($this->error));
    }
}
