<?php

namespace Diside\SecurityBundle\Tests\Gateway\ORM;

use Diside\SecurityBundle\Gateway\ORM\ORMPageGateway;
use Diside\SecurityBundle\Tests\RepositoryTestCase;
use Diside\SecurityComponent\Gateway\PageGateway;
use Diside\SecurityComponent\Model\Page;
use Diside\SecurityComponent\Model\PageTranslation;
use Mockery as m;

class ORMPageGatewayTest extends RepositoryTestCase
{

    /** @var PageGateway */
    private $pageGateway;

    public function setUp()
    {
        parent::setUp();

        $this->pageGateway = new ORMPageGateway($this->entityManager);
    }

        /**
     * @test
     */
    public function testFindOneById()
    {
        $page = $this->givenPage('en', 'Title');
        $page = $this->pageGateway->findOneById($page->getId());

        $this->assertInstanceOf('Diside\SecurityComponent\Model\Page', $page);
    }

    /**
     * @test
     */
    public function testFindAllPagination()
    {
        $this->givenPages(10);

        $filters = array();

        $this->assertPage($filters, 0, 5, 5, 10);
        $this->assertPage($filters, 1, 5, 5, 10);
        $this->assertPage($filters, 2, 5, 0, 10);
    }

    private function assertPage($filters, $pageIndex, $pageSize, $count, $total)
    {
        $pages = $this->pageGateway->findAll($filters, $pageIndex, $pageSize);
        $this->assertThat(count($pages), $this->equalTo($count));
        $this->assertThat($this->pageGateway->countAll($filters), $this->equalTo($total));
    }

    private function givenPages($number)
    {
        for($i = 0; $i != $number; ++$i)
            $this->givenPage('en', 'Page ' . $i);
    }

    private function givenPage($language, $title)
    {
        $page = new Page(null);

        $translation = new PageTranslation(null, $language, '', $title, '');

        $page->addTranslation($translation);

        return $this->pageGateway->save($page);
    }

}