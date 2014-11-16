<?php

namespace Diside\SecurityBundle\Security\Voter;

use Symfony\Component\Security\Core\User\UserInterface;
use SecurityComponent\Model\User;
use Diside\SecurityBundle\Security\LoggedUser;

class UserVoter extends AbstractVoter {

    const DELETE = 'DELETE';
    const EDIT = 'EDIT';
    const IMPERSONATE = 'IMPERSONATE';

    protected function getSupportedClasses()
    {
        return array('SecurityComponent\Model\User');
    }

    protected function getSupportedAttributes()
    {
        return array(self::IMPERSONATE, self::DELETE, self::EDIT);
    }

    protected function isGranted($attribute, $user, $loggedUser = null)
    {
        /** @var LoggedUser $loggedUser */
        /** @var User $user */

        if($attribute == self::DELETE) {
            if($loggedUser->isAdmin() && (!$loggedUser->isSameAs($user)))
                return true;
        }
        if($attribute == self::EDIT) {
            if($loggedUser->isSameAs($user) || $loggedUser->isAdmin())
                return true;
        }
        if($attribute == self::IMPERSONATE) {
            if($loggedUser->isSuperadmin() && !$loggedUser->isSameAs($user))
                return true;
        }


        return false;
    }
}