<?php

namespace Diside\SecurityBundle\Exception;

use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class InactiveUserException extends UsernameNotFoundException
{

}