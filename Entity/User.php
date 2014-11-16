<?php

namespace Diside\SecurityBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Event\LifecycleEventArgs;
use SecurityComponent\Model\User as Model;

class User
{
    /** @var int */
    private $id;

    /** @var string */
    private $email;

    /** @var string */
    private $salt;

    /** @var string */
    private $password;

    /** @var bool */
    private $isActive = false;

    /** @var array */
    private $roles = array();

    /** @var Company */
    private $company;

    /** @var string */
    private $registrationToken;

    /** @var string */
    private $resetPasswordToken;

    public function __construct()
    {
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setUsername($username)
    {
        $this->email = $username;
    }

    public function getUsername()
    {
        return $this->email;
    }

    function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getSalt()
    {
        return $this->salt;
    }

    public function setSalt($salt)
    {
        $this->salt = $salt;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
    }

    function isActive()
    {
        return $this->isActive;
    }

    public function getRoles()
    {
        return $this->roles;
    }

    public function setRoles($roles)
    {
        $this->roles = $roles;
    }

    public function getCompany()
    {
        return $this->company;
    }

    public function setCompany($company)
    {
        $this->company = $company;
    }

    public function getCompanyId()
    {
        return $this->getCompany() ? $this->getCompany()->getId() : null;
    }

    public function setRegistrationToken($token)
    {
        $this->registrationToken = $token;
    }

    public function getRegistrationToken()
    {
        return $this->registrationToken;
    }

    public function setResetPasswordToken($token)
    {
        $this->resetPasswordToken = $token;
    }

    public function getResetPasswordToken()
    {
        return $this->resetPasswordToken;
    }

    public function onRemove(LifecycleEventArgs $eventArgs)
    {
        $em = $eventArgs->getEntityManager();

    }

    public function eraseCredentials()
    {
    }

    public static function toModels($entities)
    {
        $models = array();
        foreach ($entities as $entity)
            $models[] = self::toModel($entity);

        return $models;
    }

    public static function toModel(User $entity = null)
    {
        if ($entity != null) {
            $model = new Model($entity->getId(), $entity->getEmail(), $entity->getPassword(), $entity->getSalt());
            $model->setActive($entity->isActive());
            $model->setRoles($entity->getRoles());
            $model->setRegistrationToken($entity->getRegistrationToken());
            $model->setResetPasswordToken($entity->getResetPasswordToken());
            $model->setCompany(Company::toModel($entity->getCompany()));

            return $model;
        }

        return null;
    }


}