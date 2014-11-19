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

    public static function toModel(Company $entity = null)
    {
        if($entity != null) {
            $model = new Model($entity->getId(), $entity->getName());

            return $model;
        }

        return null;
    }

}