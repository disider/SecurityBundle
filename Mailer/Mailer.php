<?php

namespace Diside\SecurityBundle\Mailer;

use Diside\SecurityComponent\Model\ShareRequest;
use Diside\SecurityComponent\Model\User;

interface Mailer {

    public function sendConfirmRegistrationEmailTo(User $user);

    public function sendRegistrationCompletedEmailTo(User $user);

    public function sendResetPasswordRequestEmailTo(User $user);
}