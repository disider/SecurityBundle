<?php

namespace Diside\SecurityBundle\Entity;

use Diside\SecurityComponent\Model\Log as Model;

class Log
{
    /** @var int */
    private $id;

    /** @var string */
    private $action;

    /** @var string */
    private $details;

    /** @var User */
    private $user;

    /** @var \DateTime */
    private $date;

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

    public static function toModel(Log $entity = null)
    {
        if ($entity != null) {
            $model = new Model($entity->getId(), $entity->getAction(), $entity->getDetails(), User::toModel($entity->getUser()), $entity->getDate());

            return $model;
        }

        return null;
    }

}