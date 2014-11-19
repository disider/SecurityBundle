<?php

namespace Diside\SecurityBundle\Tests\Gateway\ORM;

use Diside\SecurityBundle\Gateway\ORM\ORMLogGateway;
use Mockery as m;
use Diside\SecurityComponent\Gateway\LogGateway;
use Diside\SecurityComponent\Model\Log;
use Diside\SecurityComponent\Model\User;

class ORMLogGatewayTest extends BaseUserGatewayTest
{
    /** @var LogGateway */
    protected $logGateway;

    /**
     * @before
     */
    public function setUp()
    {
        parent::setUp();

        $this->logGateway = new ORMLogGateway($this->entityManager);
    }

    /**
     * @test
     */
    public function testFindAllPagination()
    {
        $user = $this->givenUser();
        $this->givenLogs(10, $user);

        $filters = array();

        $this->assertPage($filters, 0, 5, 5, 10);
        $this->assertPage($filters, 1, 5, 5, 10);
        $this->assertPage($filters, 2, 5, 0, 10);
    }

    /**
     * @test
     */
    public function testFindByActionPagination()
    {
        $user = $this->givenUser();
        $this->givenLogs(10, $user, '', 'action 1');
        $this->givenLogs(10, $user, '', 'action 2');

        $filters = array(LogGateway::FILTER_BY_ACTION => 'action 1');

        $this->assertPage($filters, 0, 10, 10, 10);
        $this->assertPage($filters, 1, 10, 0, 10);
    }

    /**
     * @test
     */
    public function testFindAllByAdminPagination()
    {
        $company = $this->givenCompany('Acme');
        $admin = $this->givenAdmin('admin@acme.com', $company);
        $user = $this->givenUser();

        $this->givenLogs(10, $admin);
        $this->givenLogs(10, $user);

        $filters = array(LogGateway::FILTER_BY_COMPANY_ID => $company->getId());

        $this->assertPage($filters, 0, 5, 5, 10);
        $this->assertPage($filters, 1, 5, 5, 10);
        $this->assertPage($filters, 2, 5, 0, 10);
    }

    private function assertPage($filters, $pageIndex, $pageSize, $count, $total)
    {
        $logs = $this->logGateway->findAll($filters, $pageIndex, $pageSize);
        $this->assertThat(count($logs), $this->equalTo($count));
        $this->assertThat($this->logGateway->countAll($filters), $this->equalTo($total));
    }

    protected function givenLogs($number, User $user, $details = 'details', $action = 'action')
    {
        for($i = 0; $i < $number; ++$i)
            $this->givenLog($action, $details . ' ' . $i, $user);
    }

    protected function givenLog($action, $details, User $user)
    {
        $log = new Log(null, $action, $details, $user, new \DateTime());

        return $this->logGateway->save($log);
    }


}