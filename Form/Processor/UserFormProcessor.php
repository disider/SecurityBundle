<?php

namespace Diside\SecurityBundle\Form\Processor;

use Diside\SecurityBundle\Builder\UserBuilder;
use Diside\SecurityBundle\Exception\UnauthorizedException;
use Diside\SecurityBundle\Form\Data\UserFormData;
use Diside\SecurityBundle\Form\UserForm;
use Diside\SecurityComponent\Helper\TokenGenerator;
use Diside\SecurityComponent\Interactor\InteractorFactory;
use Diside\SecurityComponent\Interactor\Presenter\CompaniesPresenter;
use Diside\SecurityComponent\Interactor\Presenter\UserPresenter;
use Diside\SecurityComponent\Interactor\Request\FindCompaniesRequest;
use Diside\SecurityComponent\Interactor\Request\GetUserByIdRequest;
use Diside\SecurityComponent\Interactor\Request\SaveUserRequest;
use Diside\SecurityComponent\Interactor\SecurityInteractorRegister;
use Diside\SecurityComponent\Model\User;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\SecurityContextInterface;

class UserFormProcessor extends BaseFormProcessor implements UserPresenter, CompaniesPresenter
{
    /** @var User */
    private $user;

    /** @var array */
    private $companies = array();

    /** @var int */
    private $totalCompanies;

    /** @var EncoderFactoryInterface */
    private $encoderFactory;

    /** @var UserBuilder */
    private $userBuilder;

    public function __construct(FormFactoryInterface $formFactory, InteractorFactory $interactorFactory, SecurityContextInterface $securityContext, EncoderFactoryInterface $encoderFactory, UserBuilder $userBuilder)
    {
        parent::__construct($formFactory, $interactorFactory, $securityContext);

        $this->encoderFactory = $encoderFactory;
        $this->userBuilder = $userBuilder;
    }

    protected function buildFormData($id)
    {
        $currentUser = $this->getAuthenticatedUser();

        if ($currentUser->isSuperadmin())
            $this->findCompanies();

        if ($id != null) {
            $this->retrieveUserById($id);

            $data = $this->getUser();

            return new UserFormData($data, $this->companies);
        } else {
            $salt = TokenGenerator::generateToken();

            $user = $this->userBuilder->build('', '', $salt);
            $data = new UserFormData($user, $this->companies);

            if ($currentUser->isAdmin())
                $data->setCompany((string)$currentUser->getCompany());

            return $data;
        }
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    protected function buildRequest()
    {
        /** @var UserFormData $data */
        $data = $this->getFormData();

        $currentUser = $this->getAuthenticatedUser();

        $user = $data->getUser();

        $request = new SaveUserRequest(
            $currentUser->getId(),
            $data->getId(),
            $data->getEmail(),
            $currentUser->isSameAs($user) ? null : $this->encodePassword($data->getPassword(), $user),
            $user->getSalt(),
            $data->isActive(),
            $data->getRoles());

        if ($currentUser->isSuperAdmin()) {
            $request->companyId = $data->getCompanyId();
        }

        return $request;
    }

    protected function findCompanies()
    {
        $user = $this->getAuthenticatedUser();

        $interactor = $this->getInteractorFactory()->get(SecurityInteractorRegister::FIND_COMPANIES);
        $request = new FindCompaniesRequest($user->getId());
        $interactor->process($request, $this);
    }

    protected function retrieveUserById($id)
    {
        $interactor = $this->getInteractorFactory()->get(SecurityInteractorRegister::GET_USER);

        $request = new GetUserByIdRequest($id);
        $interactor->process($request, $this);

        if ($this->hasErrors()) {
            throw new NotFoundHttpException;
        }

        $currentUser = $this->getAuthenticatedUser();
        $user = $this->getUser();

        if (!($currentUser->isSuperadmin() || ($currentUser->isAdmin() && $currentUser->hasSameCompanyAs($user)) || $currentUser->isSameAs($user)))
            throw new UnauthorizedException;
    }

    /** @return array */
    public function getCompanies()
    {
        return $this->companies;
    }

    public function setCompanies(array $companies)
    {
        $this->companies = $companies;
    }

    /** @return int */
    public function getTotalCompanies()
    {
        return $this->totalCompanies;
    }

    public function setTotalCompanies($total)
    {
        $this->totalCompanies = $total;
    }

    protected function buildForm()
    {
        $user = $this->getAuthenticatedUser();

        return new UserForm($user);
    }

    protected function getSaveInteractorName()
    {
        return SecurityInteractorRegister::SAVE_USER;
    }

    protected function evaluateRedirect()
    {
        $this->setRedirectTo($this->isButtonClicked('save_and_close') ? self::REDIRECT_TO_LIST : null);
    }

    private function encodePassword($password, User $user)
    {
        if ($password == null)
            return null;

        $encoder = $this->encoderFactory->getEncoder($user);

        return $encoder->encodePassword($password, $user->getSalt());
    }


}
