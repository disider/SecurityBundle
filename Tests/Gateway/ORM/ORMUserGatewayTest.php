<?php

namespace Diside\SecurityBundle\Tests\Gateway\ORM;

use Mockery as m;
use SecurityComponent\Gateway\ShareRequestGateway;
use SecurityComponent\Gateway\UserGateway;
use SecurityComponent\Model\ChecklistTemplate;
use SecurityComponent\Model\ShareRequest;
use SecurityComponent\Model\User;
use Diside\SecurityBundle\Gateway\ORM\ORMShareRequestGateway;

class ORMUserGatewayTest extends BaseUserGatewayTest
{
    /**
     * @test
     */
    public function testFindOneById()
    {
        $user = $this->givenUser('adam@example.com');
        $user = $this->userGateway->findOneById($user->getId());

        $this->assertInstanceOf('SecurityComponent\Model\User', $user);
    }

    /**
     * @test
     */
    public function testFindOneByEmail()
    {
        $this->givenUser('adam@example.com');
        $user = $this->userGateway->findOneByEmail('adam@example.com');

        $this->assertInstanceOf('SecurityComponent\Model\User', $user);
        $this->assertTrue($user->isActive());
    }

    /**
     * @test
     */
    public function testFindOneByRegistrationToken()
    {
        $user = $this->givenUser('adam@example.com');
        $user->setRegistrationToken('12345678');

        $this->userGateway->save($user);

        $user = $this->userGateway->findOneByRegistrationToken('12345678');

        $this->assertInstanceOf('SecurityComponent\Model\User', $user);
        $this->assertThat($user->getRegistrationToken(), $this->equalTo('12345678'));
    }

    /**
     * @test
     */
    public function testFindOneByResetPasswordToken()
    {
        $user = $this->givenUser('adam@example.com');
        $user->setResetPasswordToken('12345678');

        $this->userGateway->save($user);

        $user = $this->userGateway->findOneByResetPasswordToken('12345678');

        $this->assertInstanceOf('SecurityComponent\Model\User', $user);
        $this->assertThat($user->getResetPasswordToken(), $this->equalTo('12345678'));
    }

    /**
     * @test
     */
    public function whenFindingUnknownUser_thenReturnNull()
    {
        $user = $this->userGateway->findOneByEmail('unknown@example.com');

        $this->assertNull($user);
    }

    /**
     * @test
     */
    public function whenFindingByIds_thenReturnMatchingUsers()
    {
        $user = $this->givenUser('adam@example.com');

        $ids = array(-1, $user->getId());

        $users = $this->userGateway->findByIds($ids);

        $this->assertThat(count($users), $this->equalTo(1));
    }

    /**
     * @test
     */
    public function testFindAllPagination()
    {
        $this->givenUsers(10);
        $this->givenInactiveUser();

        $filters = array();

        $this->assertPage($filters, 0, 5, 5, 11);
        $this->assertPage($filters, 1, 5, 5, 11);
        $this->assertPage($filters, 2, 5, 1, 11);
    }

    /**
     * @test
     */
    public function testFindByCompanyPagination()
    {
        $this->givenCompany('Acme');
        $this->givenCompany('Bros');
        $admin = $this->givenAdmin('admin@acme.com', 'Acme');
        $this->givenAdmin('admin@bros.com', 'Bros');
        $this->givenUsers(5, 'Acme');
        $this->givenUsers(5, 'Bros', 5);
        $this->givenInactiveUser('inactive@acme.com', 'Acme');
        $this->givenInactiveUser('inactive@bros.com', 'Bros');

        $filters = array(UserGateway::FILTER_BY_COMPANY_ID => $admin->getCompanyId());

        $this->assertPage($filters, 0, 5, 5, 7);
        $this->assertPage($filters, 1, 5, 2, 7);
        $this->assertPage($filters, 2, 5, 0, 7);
    }

    /**
     * @test
     */
    public function testFindNoSuperadmins()
    {
        $this->givenSuperadmin('superadmin@example.com');

        $filters = array();

        $users = $this->userGateway->findAll($filters);
        $this->assertThat(count($users), $this->equalTo(0));
    }

    /**
     * @test
     */
    public function testFindAllActive()
    {
        $this->givenUsers(2);
        $this->givenInactiveUser();

        $filters = array(UserGateway::FILTER_ACTIVE => true);

        $users = $this->userGateway->findAll($filters);
        $this->assertThat(count($users), $this->equalTo(2));
    }

    /**
     * @test
     */
    public function whenSavingResetPasswordToken_thenHasResetPasswordToken()
    {
        $user = $this->givenUser();
        $user->setResetPasswordToken('1234');

        $this->userGateway->save($user);

        $user = $this->userGateway->findOneById($user->getId());

        $this->assertThat($user->getResetPasswordToken(), $this->equalTo('1234'));
    }

    private function assertPage($filters, $pageIndex, $pageSize, $count, $total)
    {
        $users = $this->userGateway->findAll($filters, $pageIndex, $pageSize);
        $this->assertThat(count($users), $this->equalTo($count));
        $this->assertThat($this->userGateway->countAll($filters), $this->equalTo($total));
    }

}