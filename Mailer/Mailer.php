<?php

namespace Diside\SecurityBundle\Mailer;

use SecurityComponent\Model\ShareRequest;
use SecurityComponent\Model\User;

interface Mailer {

    public function sendConfirmRegistrationEmailTo(User $user);

    public function sendRegistrationCompletedEmailTo(User $user);

    public function sendResetPasswordRequestEmailTo(User $user);
}