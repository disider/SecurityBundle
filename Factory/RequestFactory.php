<?php

namespace Diside\SecurityBundle\Factory;

use Diside\SecurityBundle\Entity\Page;
use Diside\SecurityBundle\Entity\PageTranslation;
use Diside\SecurityBundle\Exception\UndefinedFactoryException;
use Diside\SecurityBundle\Security\LoggedUser;
use Diside\SecurityComponent\Interactor\Request\ChangePasswordRequest;
use Diside\SecurityComponent\Interactor\Request\RegisterUserRequest;
use Diside\SecurityComponent\Interactor\Request\SaveCompanyRequest;
use Diside\SecurityComponent\Interactor\Request\SavePageRequest;
use Diside\SecurityComponent\Interactor\Request\SaveUserRequest;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

class RequestFactory
{
    /** @var SecurityContextInterface */
    private $securityContext;

    /** @var EncoderFactoryInterface */
    private $encoderFactory;

    public function __construct(SecurityContextInterface $securityContext, EncoderFactoryInterface $encoderFactory)
    {
        $this->securityContext = $securityContext;
        $this->encoderFactory = $encoderFactory;
    }

    public function create($name, $data, array $params = array())
    {
        switch($name) {
            case 'save_user': return $this->createSaveUserRequest($data, $params);
            case 'register_user': return $this->createRegisterUserRequest($data, $params);
            case 'change_password': return $this->createChangePasswordRequest($data);
            case 'save_company': return $this->createSaveCompanyRequest($data);
            case 'save_page': return $this->createSavePageRequest($data);
        }

        throw new UndefinedFactoryException('Undefined request ' . $name);
    }

    /** @return LoggedUser */
    protected function getAuthenticatedUser()
    {
        $token = $this->securityContext->getToken();
        $user = $token->getUser();

        return $user;
    }

    protected function encodePassword($password, $user)
    {
        if ($password == null) {
            return null;
        }

        $encoder = $this->encoderFactory->getEncoder($user);

        return $encoder->encodePassword($password, $user->getSalt());
    }

    protected function createSaveUserRequest($user, array $params)
    {
        $currentUser = $this->getAuthenticatedUser();

        $request = new SaveUserRequest(
            $currentUser->getId(),
            $user->getId(),
            $user->getEmail(),
            $params['set_password'] ? $this->encodePassword($user->getPassword(), $user) : null,
            $user->getSalt(),
            $user->isActive(),
            $user->getRoles()
        );

        if ($params['set_company']) {
            $request->companyId = $user->getCompanyId();
        }

        return $request;
    }

    protected function createRegisterUserRequest($data, $params)
    {
        $user = $params['user'];

        return new RegisterUserRequest(
            $data->getEmail(),
            $this->encodePassword($data->getPassword(), $user),
            $user->getSalt()
        );
    }

    protected function createSaveCompanyRequest($company)
    {
        return new SaveCompanyRequest($company->getId(), $company->getName());
    }

    protected function createChangePasswordRequest($data)
    {
        $currentUser = $this->getAuthenticatedUser();

        return new ChangePasswordRequest(
            $currentUser->getId(),
            $data->getId(),
            $this->encodePassword($data->getCurrentPassword(), $currentUser),
            $this->encodePassword($data->getNewPassword(), $currentUser)
        );
    }

    /**
     * @param Page $page
     * @return SavePageRequest
     */
    protected function createSavePageRequest($page)
    {
        $currentUser = $this->getAuthenticatedUser();

        $request = new SavePageRequest(
            $currentUser->getId(),
            $page->getId(),
            $page->getLocale(),
            $page->getUrl(),
            $page->getTitle(),
            $page->getContent()
        );

        /** @var PageTranslation $translation */
        foreach ($page->getTranslations() as $translation) {
            $request->addTranslation(
                $translation->getId(),
                $translation->getLocale(),
                $translation->getUrl(),
                $translation->getTitle(),
                $translation->getContent()
            );
        }

        return $request;
    }

}