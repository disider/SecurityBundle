<?php

namespace Diside\SecurityBundle\Form\Data;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use SecurityComponent\Model\Registration;

class RegistrationFormData
{
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
}