<?php

namespace Diside\SecurityBundle\Mailer;

use SecurityComponent\Model\ShareRequest;
use SecurityComponent\Model\User;

class MailerMock implements Mailer
{
    private $template;
    private $to;

    public function sendConfirmRegistrationEmailTo(User $user) {
        $this->template = 'registration_confirm';
        $this->to = $user->getEmail();
    }

    public function sendRegistrationCompletedEmailTo(User $user)
    {
        $this->template = 'registration_completed';
        $this->to = $user->getEmail();
    }

    public function sendResetPasswordRequestEmailTo(User $user)
    {
        $this->template = 'request_reset_password';
        $this->to = $user->getEmail();
    }

    public function getTemplate()
    {
        return $this->template;
    }

    public function getTo()
    {
        return $this->to;
    }
}