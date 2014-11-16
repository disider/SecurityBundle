<?php

namespace Diside\SecurityBundle\Form\Data;

use Symfony\Component\Validator\Constraints as Assert;
use SecurityComponent\Model\ChangePassword;

class ChangePasswordFormData
{
    /** @var string */
    private $id;

    /**
     * Assert\NotBlank(message="error.empty_current_password")
     * @var string
     */
    private $currentPassword;

    /**
     * Assert\NotBlank(message="error.empty_new_password")
     * @var string
     */
    private $newPassword;

    public function __construct($id) {
        $this->id = $id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setCurrentPassword($currentPassword)
    {
        $this->currentPassword = $currentPassword;
    }

    public function getCurrentPassword()
    {
        return $this->currentPassword;
    }

    public function setNewPassword($newPassword)
    {
        $this->newPassword = $newPassword;
    }

    public function getNewPassword()
    {
        return $this->newPassword;
    }
}