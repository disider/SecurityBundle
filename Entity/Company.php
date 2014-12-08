<?php

namespace Diside\SecurityBundle\Entity;

use Diside\SecurityComponent\Model\Company as Model;

class Company
{
    /** @var  string */
    protected $id;

    /** @var string */
    protected $name;

    /** @return int */
    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    /** @return string */
    public function getName()
    {
        return $this->name;
    }

    /** @param string $name */
    public function setName($name)
    {
        $this->name = $name;
    }

    public function toModel()
    {
        return new Model($this->getId(), $this->getName());
    }

}