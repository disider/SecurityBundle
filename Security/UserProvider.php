<?php

namespace Diside\SecurityBundle\Security;

use Diside\SecurityBundle\Exception\InactiveUserException;
use Diside\SecurityBundle\Exception\UndefinedUsernameException;
use Diside\SecurityBundle\Presenter\UserPresenter;
use SecurityComponent\Interactor\InteractorFactory;
use SecurityComponent\Interactor\Request\GetUserByEmailRequest;
use SecurityComponent\Model\User;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider implements UserProviderInterface
{
    /** @var InteractorFactory */
    private $interactorFactory;

    public function __construct(InteractorFactory $interactorFactory)
    {
        $this->interactorFactory = $interactorFactory;
    }

    public function loadUserByUsername($username)
    {
        if (empty($username) || $username == 'NONE_PROVIDED')
            throw new UndefinedUsernameException();

        $user = $this->loadUser($username);

        if ($user == null || !($user instanceof User))
            throw new UsernameNotFoundException();

        if (!($user->isActive()))
            throw new InactiveUserException();

        return new LoggedUser($user);
    }

    public function refreshUser(UserInterface $user)
    {
        $user = $this->loadUser($user->getUsername());

        if ($user == null)
            return null;

        return new LoggedUser($user);
    }

    public function supportsClass($class)
    {
        return 'Diside\SecurityBundle\Security\LoggedUser' === $class;
    }

    protected function loadUser($username)
    {
        $interactor = $this->interactorFactory->get(InteractorFactory::GET_USER);

        $request = new GetUserByEmailRequest($username);

        $presenter = new UserPresenter();

        $interactor->process($request, $presenter);

        return $presenter->getUser();
    }
}
