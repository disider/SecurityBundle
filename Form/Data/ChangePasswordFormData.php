<?php

namespace Diside\SecurityBundle\Form\Data;

use Symfony\Component\Validator\Constraints as Assert;
use Diside\SecurityComponent\Model\ChangePassword;

class ChangePasswordFormData
{
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