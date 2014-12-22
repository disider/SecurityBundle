<?php

namespace Diside\SecurityBundle\Factory;

use Diside\SecurityBundle\Entity\Company;
use Diside\SecurityBundle\Entity\User;
use Diside\SecurityBundle\Exception\UndefinedFactoryException;

class EntityFactory
{
    private $classes = array();

    public function register($name, $class)
    {
        $this->classes[$name] = $class;
    }

    public function create($name, $model = null)
    {
        $class = $this->getClass($name);

        if($class == null)
            throw new UndefinedFactoryException('Undefined entity ' . $name);

        return $this->buildEntity(new $class(), $model);
    }

    protected function buildEntity($entity, $model)
    {
        if($model)
            $entity->fromModel($model);

        return $entity;
    }

    public function getClass($name)
    {
        if(!isset($this->classes[$name]))
            throw new UndefinedFactoryException('Undefined entity ' . $name);

        return $this->classes[$name];
    }

}