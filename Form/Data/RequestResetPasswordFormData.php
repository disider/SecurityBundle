<?php

namespace Diside\SecurityBundle\Form\Data;

use Symfony\Component\Validator\Constraints as Assert;

class RequestResetPasswordFormData
{
    /**
     * @Assert\NotBlank(message="error.empty_email")
     * @Assert\Email(message="error.wrong_email")
     * @var string
     */
    private $email;

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getEmail()
    {
        return $this->email;
    }
}