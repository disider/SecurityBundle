<?php

namespace Diside\SecurityBundle\Form\Processor;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\SecurityContextInterface;
use SecurityComponent\Helper\TokenGenerator;
use SecurityComponent\Interactor\InteractorFactory;
use SecurityComponent\Interactor\Presenter\FindCompaniesPresenter;
use SecurityComponent\Interactor\Presenter\UserPresenter;
use SecurityComponent\Interactor\Request\FindCompaniesRequest;
use SecurityComponent\Interactor\Request\GetUserByIdRequest;
use SecurityComponent\Interactor\Request\SaveUserRequest;
use SecurityComponent\Model\User;
use Diside\SecurityBundle\Exception\UnauthorizedException;
use Diside\SecurityBundle\Form\Data\UserFormData;
use Diside\SecurityBundle\Form\UserForm;

class UserFormProcessor extends BaseFormProcessor implements UserPresenter, FindCompaniesPresenter
{
    /** @var User */
    private $user;

    /** @var array */
    private $companies = array();

    /** @var int */
    private $totalCompanies;

    /** @var EncoderFactoryInterface */
    private $encoderFactory;

    public function __construct(FormFactoryInterface $formFactory, InteractorFactory $interactorFactory, SecurityContextInterface $securityContext, EncoderFactoryInterface $encoderFactory)
    {
        parent::__construct($formFactory, $interactorFactory, $securityContext);

        $this->encoderFactory = $encoderFactory;
    }

    protected function buildFormData($id)
    {
        $currentUser = $this->getAuthenticatedUser();

        if($currentUser->isSuperadmin())
            $this->findCompanies();

        if ($id != null) {
            $this->retrieveUserById($id);

            $user = $this->getUser();

            return new UserFormData($user, $this->companies);
        } else {
            $salt = TokenGenerator::generateToken();
            $user = new User(null, '', '', $salt);
            $user = new UserFormData($user, $this->companies);

            if($currentUser->isAdmin())
                $user->setCompany((string)$currentUser->getCompany());

            return $user;
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

        if($currentUser->isSuperAdmin()) {
            $request->companyId = $data->getCompanyId();
            $request->maximumChecklistTemplates = $data->getMaxChecklistTemplates();
        }

        return $request;
    }

    protected function findCompanies()
    {
        $user = $this->getAuthenticatedUser();

        $interactor = $this->getInteractorFactory()->get(InteractorFactory::FIND_COMPANIES);
        $request = new FindCompaniesRequest($user->getId());
        $interactor->process($request, $this);

        return array($interactor, $request);
    }

    protected function retrieveUserById($id)
    {
        $interactor = $this->getInteractorFactory()->get(InteractorFactory::GET_USER);

        $request = new GetUserByIdRequest($id);
        $interactor->process($request, $this);

        if ($this->hasErrors()) {
            throw new NotFoundHttpException;
        }

        $currentUser = $this->getAuthenticatedUser();
        $user = $this->getUser();

        if(!($currentUser->isSuperadmin() || ($currentUser->isAdmin() && $currentUser->hasSameCompanyAs($user)) || $currentUser->isSameAs($user)))
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
        return InteractorFactory::SAVE_USER;
    }

    protected function evaluateRedirect()
    {
        $this->setRedirectTo($this->isButtonClicked('save_and_close') ? self::REDIRECT_TO_LIST : null);
    }

    private function encodePassword($password, User $user)
    {
        if($password == null)
            return null;

        $encoder = $this->encoderFactory->getEncoder($user);

        return $encoder->encodePassword($password, $user->getSalt());
    }


}
