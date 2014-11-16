<?php

namespace Diside\SecurityBundle\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Diside\SecurityBundle\Security\LoggedUser;

class RoleVoter implements VoterInterface
{

    const ADD_CHECKLIST_TEMPLATE = 'ADD_CHECKLIST_TEMPLATE';
    const HAS_SHARE_REQUESTS = 'HAS_SHARE_REQUESTS';

    public function supportsAttribute($attribute)
    {
        return in_array($attribute, array(self::ADD_CHECKLIST_TEMPLATE, self::HAS_SHARE_REQUESTS));
    }

    public function supportsClass($class)
    {
        return true;
    }

    protected function isGranted($attribute, $user = null)
    {
        /** @var LoggedUser $user */
        if (!$user)
            return false;

        switch ($attribute) {
            case self::ADD_CHECKLIST_TEMPLATE: {
                if ($user->countChecklistTemplates() < $user->getMaxChecklistTemplates())
                    return true;
            }
            case self::HAS_SHARE_REQUESTS: {
                if ($user->hasShareRequests())
                    return true;
            }
        }

        return false;
    }

    public function vote(TokenInterface $token, $object, array $attributes)
    {
        // abstain vote by default in case none of the attributes are supported
        $vote = self::ACCESS_ABSTAIN;
        foreach ($attributes as $attribute) {
            if (!$this->supportsAttribute($attribute)) {
                continue;
            }
            // as soon as at least one attribute is supported, default is to deny access
            $vote = self::ACCESS_DENIED;
            if ($this->isGranted($attribute, $token->getUser())) {
                // grant access as soon as at least one voter returns a positive response
                return self::ACCESS_GRANTED;
            }
        }
        return $vote;
    }

}