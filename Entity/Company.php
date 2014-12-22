<?php

namespace Diside\SecurityBundle\Entity;

use Diside\SecurityComponent\Model\Company as Model;
use Symfony\Component\Validator\Constraints as Assert;

class Company
{
    /** @var  string */
    protected $id;

    /**
     * @Assert\NotBlank(message="error.empty_name")
     * @var string
     */
    protected $name;

    public function __toString()
    {
        return $this->getName();
    }

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

    public function fromModel($model)
    {
        /** @var Model $model */

        $this->setId($model->getId());
        $this->setName($model->getName());
    }

}