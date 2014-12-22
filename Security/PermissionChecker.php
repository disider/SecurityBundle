<?php

namespace Diside\SecurityBundle\Security;

use Symfony\Component\Security\Core\SecurityContextInterface;

class PermissionChecker
{
    /** @var SecurityContextInterface */
    private $securityContext;

    public function __construct(SecurityContextInterface $securityContext)
    {
        $this->securityContext = $securityContext;
    }

    public function check($permission, $object = null)
    {
        $currentUser = $this->getAuthenticatedUser();

        if($permission == 'set_password')
            if (!$currentUser->isSameAs($object))
                return true;

        if($permission == 'has_same_company')
            if($currentUser->isAdmin())
                return true;

        if($permission == 'set_company')
            if($currentUser->isSuperAdmin() || $currentUser->isAdmin())
                return true;

        if($permission == 'can_edit') {
            if ($currentUser->isSuperadmin()
                || ($currentUser->isAdmin() && $currentUser->hasSameCompanyAs($object))
                || $currentUser->isSameAs($object))
            return true;
        }
        return false;
    }

    /** @return LoggedUser */
    protected function getAuthenticatedUser()
    {
        $token = $this->securityContext->getToken();
        $user = $token->getUser();

        return $user;
    }

}