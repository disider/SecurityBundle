<?php

namespace Diside\SecurityBundle\Tests\Gateway\ORM;

use Mockery as m;
use SecurityComponent\Gateway\CompanyGateway;
use SecurityComponent\Model\Company;
use Diside\SecurityBundle\Gateway\ORM\ORMCompanyGateway;
use Diside\SecurityBundle\Tests\RepositoryTestCase;

class ORMCompanyGatewayTest extends BaseUserGatewayTest
{
    /**
     * @test
     */
    public function testFindAllPagination()
    {
        $this->givenCompanies(10, 'Acme');

        $filters = array();
        $companies = $this->companyGateway->findAll($filters, 0, 5);
        $this->assertThat(count($companies), $this->equalTo(5));

        $companies = $this->companyGateway->findAll($filters, 1, 5);
        $this->assertThat(count($companies), $this->equalTo(5));

        $companies = $this->companyGateway->findAll($filters, 2, 5);
        $this->assertThat(count($companies), $this->equalTo(0));
    }

    /**
     * @test
     */
    public function testCountAll()
    {
        $this->givenCompanies(10, 'Acme');

        $filters = array();
        $count = $this->companyGateway->countAll($filters);
        $this->assertThat($count, $this->equalTo(10));
    }

    /**
     * @test
     */
    public function testDelete()
    {
        $company = $this->givenCompany('Acme');

        $this->companyGateway->delete($company->getId());

        $this->entityManager->clear();

        $company = $this->companyGateway->findOneById($company->getId());
        $this->assertNull($company);
    }

}