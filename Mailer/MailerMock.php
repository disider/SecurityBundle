<?php

namespace Diside\SecurityBundle\Mailer;

use Diside\SecurityComponent\Model\ShareRequest;
use Diside\SecurityComponent\Model\User;

class MailerMock implements Mailer
{
    private $template;
    private $to;

    public function sendConfirmRegistrationEmailTo(User $user) {
        $this->registerMail('registration_confirm', $user->getEmail());
    }

    public function sendRegistrationCompletedEmailTo(User $user)
    {
        $this->registerMail('registration_completed', $user->getEmail());
    }

    public function sendResetPasswordRequestEmailTo(User $user)
    {
        $this->registerMail('request_reset_password', $user->getEmail());
    }

    public function getTemplate()
    {
        return $this->template;
    }

    public function getTo()
    {
        return $this->to;
    }

    protected function registerMail($template, $email)
    {
        $this->template = $template;
        $this->to = $email;
    }
}