<?php

namespace Diside\SecurityBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Diside\SecurityComponent\Model\User as Model;

class User
{
    /** @var int */
    protected $id;

    /** @var string */
    protected $email;

    /** @var string */
    protected $salt;

    /** @var string */
    protected $password;

    /** @var bool */
    protected $isActive = false;

    /** @var array */
    protected $roles = array();

    /** @var Company */
    protected $company;

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

    public function hasCompany()
    {
        return $this->company != null;
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
        return $this->hasCompany() ? $this->getCompany()->getId() : null;
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

    public function toModel()
    {
        $model = new Model($this->getId(), $this->getEmail(), $this->getPassword(), $this->getSalt());
        $model->setActive($this->isActive());
        $model->setRoles($this->getRoles());
        $model->setRegistrationToken($this->getRegistrationToken());
        $model->setResetPasswordToken($this->getResetPasswordToken());

        if($this->hasCompany())
            $model->setCompany($this->getCompany()->toModel());

        return $model;
    }

    public function fromModel($model, $company)
    {
        $this->setEmail($model->getEmail());
        $this->setPassword($model->getPassword());
        $this->setSalt($model->getSalt());
        $this->setIsActive($model->isActive());
        $this->setRoles($model->getRoles());
        $this->setRegistrationToken($model->getRegistrationToken());
        $this->setResetPasswordToken($model->getResetPasswordToken());
        $this->setCompany($company);
    }


}