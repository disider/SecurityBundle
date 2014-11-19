<?php

namespace Diside\SecurityBundle\Security;

use Diside\SecurityComponent\Model\User;
use Symfony\Component\Security\Core\User\UserInterface;

class LoggedUser implements UserInterface
{
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function __toString()
    {
        return (string)$this->user;
    }

    public function getId()
    {
        return $this->user->getId();
    }

    public function getUsername()
    {
        return $this->user->getEmail();
    }

    public function getPassword()
    {
        return $this->user->getPassword();
    }

    public function getSalt()
    {
        return $this->user->getSalt();
    }

    public function getRoles()
    {
        return $this->user->getRoles();
    }

    public function eraseCredentials()
    {
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array(array($this->user, $name), $arguments);
    }

}