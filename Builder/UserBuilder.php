<?php

namespace Diside\SecurityBundle\Builder;

use Diside\SecurityComponent\Model\User;

class UserBuilder
{
    public function build($email, $password, $salt)
    {
        return new User(null, $email, $password, $salt);
    }
}