<?php

namespace Diside\SecurityBundle\Tests\Mock;

use Diside\SecurityComponent\Interactor\AbstractInteractor;
use Diside\SecurityComponent\Interactor\Presenter;
use Diside\SecurityComponent\Interactor\Request;
use Diside\SecurityComponent\Model\User;

class InteractorMock extends AbstractInteractor
{
    private $request;
    private $object;
    private $method;

    public function __construct($object, $method)
    {
        $this->object = $object;
        $this->method = $method;
    }

    public function process(Request $request, Presenter $presenter)
    {
        $this->request = $request;

        $method = $this->method;

        $presenter->$method($this->object);
    }

    public function getRequest()
    {
        return $this->request;
    }
}