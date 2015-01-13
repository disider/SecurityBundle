<?php

namespace Diside\SecurityBundle\Form\Data;

use Symfony\Component\Validator\Constraints as Assert;

class ResetPasswordFormData
{
    /**
     * @Assert\NotBlank(message="error.empty_password")
     * @var string
     */
    protected $password;

    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function getPassword()
    {
        return $this->password;
    }
}