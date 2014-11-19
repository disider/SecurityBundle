<?php

namespace Diside\SecurityBundle\Tests\Mock;

use Diside\SecurityComponent\Interactor\AbstractInteractor;
use Diside\SecurityComponent\Interactor\Presenter;
use Diside\SecurityComponent\Interactor\Request;

class ErrorInteractor extends AbstractInteractor
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
