<?php

namespace Diside\SecurityBundle\Form\Data;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Diside\SecurityComponent\Model\User;

class UserFormData
{
    /** @var string */
    private $id;

    /**
     * @Assert\NotBlank(message="error.empty_email")
     * @Assert\Email(message="error.wrong_email")
     * @var string
     */
    private $email;

    /**
     * Assert\NotBlank(message="error.empty_password")
     * @var string
     */
    private $password;

    /** @var array */
    private $companies = array();

    /** @var int */
    private $companyId;

    /** @var boolean */
    private $isActive;

    /** @var array */
    private $roles = array();

    /** @var User */
    private $user;

    public function __construct(User $user, array $companies)
    {
        /** @var User $user */
        $this->companies[] = '';
        foreach ($companies as $company) {
            $this->companies[$company->getId()] = $company;
        }

        $this->id = $user->getId();
        $this->email = $user->getEmail();
        $this->isActive = $user->isActive();

        $this->company = (string)$user->getCompany();
        $this->companyId = $user->getCompanyId();
        $this->roles = $user->getRoles();
        $this->user = $user;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
    }

    public function isActive()
    {
        return $this->isActive;
    }

    public function setCompany($company)
    {
        $this->company = $company;
    }

    public function getCompany()
    {
        return $this->company;
    }

    public function setCompanyId($companyId)
    {
        $this->companyId = $companyId;
    }

    public function getCompanyId()
    {
        return $this->companyId;
    }

    public function setRoles(array $roles)
    {
        $this->roles = $roles;
    }

    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context)
    {
        if ($this->getId() == null && $this->getPassword() == null) {
            $context->buildViolation('error.empty_password')
                ->atPath('password')
                ->addViolation();
        }
    }

    public function getCompanies()
    {
        return $this->companies;
    }

    public function getUser()
    {
        return $this->user;
    }
}