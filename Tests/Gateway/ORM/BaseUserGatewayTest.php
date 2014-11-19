<?php

namespace Diside\SecurityBundle\Tests\Gateway\ORM;

use Diside\SecurityBundle\Gateway\ORM\ORMCompanyGateway;
use Diside\SecurityBundle\Gateway\ORM\ORMUserGateway;
use Diside\SecurityBundle\Tests\RepositoryTestCase;
use Diside\SecurityComponent\Gateway\CompanyGateway;
use Diside\SecurityComponent\Gateway\UserGateway;
use Diside\SecurityComponent\Model\Company;
use Diside\SecurityComponent\Model\User;
use Mockery as m;

abstract class BaseUserGatewayTest extends RepositoryTestCase
{
    /** @var CompanyGateway */
    protected $companyGateway;

    /** @var UserGateway */
    protected $userGateway;

    /**
     * @before
     */
    public function setUp()
    {
        parent::setUp();

        $this->companyGateway = new ORMCompanyGateway($this->entityManager);
        $this->userGateway = new ORMUserGateway($this->entityManager);
    }

    protected function givenCompanies($number, $companyName)
    {
        for ($i = 0; $i < $number; ++$i)
            $this->givenCompany($companyName . ' ' . $i);
    }

    protected function givenCompany($companyName)
    {
        $company = new Company(null, $companyName);

        return $this->companyGateway->save($company);
    }

    protected function givenUsers($number, $companyName = null, $offset = 0)
    {
        for ($i = 0; $i < $number; ++$i)
            $this->givenUser(($i + $offset) . '@example.com', $companyName);
    }

    protected function givenUser($email = 'adam@example.com', $companyName = null)
    {
        $user = $this->buildUser($email, true, $companyName);

        return $this->userGateway->save($user);
    }

    protected function givenInactiveUser($email = 'inactive@example.com', $companyName = null)
    {
        $user = $this->buildUser($email, false, $companyName);

        return $this->userGateway->save($user);
    }

    protected function givenSuperadmin($email)
    {
        $user = $this->buildUser($email, true);
        $user->addRole(User::ROLE_SUPERADMIN);

        return $this->userGateway->save($user);
    }

    protected function givenAdmin($email, $companyName = null)
    {
        $user = $this->buildUser($email, true, $companyName);

        $user->addRole(User::ROLE_ADMIN);
        return $this->userGateway->save($user);
    }

    protected function givenManager($email = 'manager@example.com', $companyName = null)
    {
        $user = $this->buildUser($email, true, $companyName);

        $user->addRole(User::ROLE_MANAGER);
        return $this->userGateway->save($user);
    }

    protected function buildUser($email, $isActive = true, $companyName = null)
    {
        $user = new User(null, $email, '', '');
        $user->setActive($isActive);

        if ($companyName != null) {
            $company = $this->companyGateway->findOneByName($companyName);
            $user->setCompany($company);
            return $user;
        }

        return $user;
    }

}
