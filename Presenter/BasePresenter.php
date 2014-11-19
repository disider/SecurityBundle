<?php

namespace Diside\SecurityBundle\Presenter;

use Diside\SecurityComponent\Interactor\Presenter;

class BasePresenter implements Presenter
{
    private $errors;

    public function hasErrors()
    {
        return $this->errors != null;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function setErrors(array $errors)
    {
        $this->errors = $errors;
    }
}