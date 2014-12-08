<?php

namespace Diside\SecurityBundle\Entity;

use Diside\SecurityComponent\Model\Log as Model;

class Log
{
    /** @var int */
    protected $id;

    /** @var string */
    protected $action;

    /** @var string */
    protected $details;

    /** @var User */
    protected $user;

    /** @var \DateTime */
    protected $date;

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    function getAction()
    {
        return $this->action;
    }

    public function setAction($action)
    {
        $this->action = $action;
    }

    function getDetails()
    {
        return $this->details;
    }

    public function setDetails($details)
    {
        $this->details = $details;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser($user)
    {
        $this->user = $user;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function setDate($date)
    {
        $this->date = $date;
    }

    public function toModel()
    {
        return new Model(
            $this->getId(),
            $this->getAction(),
            $this->getDetails(),
            $this->getUser()->toModel(),
            $this->getDate());
    }

}