<?php

namespace Diside\SecurityBundle\Tests\Mock;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class DummyToken extends AbstractToken
{
    public function __construct($user = null)
    {
        if($user != null)
            $this->setUser($user);
    }

    public function getCredentials()
    {
    }
}