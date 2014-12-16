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
    public function testFindOneByLanguageAndUrl()
    {
        $page = $this->givenPage('en', 'page');
        $this->givenPageTranslation($page, 'it', 'pagina');

        $page = $this->pageGateway->findOneByLanguageAndUrl('en', 'page');
        $this->assertPage($page, 'en', 'page');
        $this->assertPage($page, 'it', 'pagina');

        $page = $this->pageGateway->findOneByLanguageAndUrl('it', 'pagina');
        $this->assertPage($page, 'en', 'page');
        $this->assertPage($page, 'it', 'pagina');
    }

    /**
     * @test
     */
    public function testFindAllPagination()
    {
        $this->givenPages(10);

        $filters = array();

        $this->assertPages($filters, 0, 5, 5, 10);
        $this->assertPages($filters, 1, 5, 5, 10);
        $this->assertPages($filters, 2, 5, 0, 10);
    }

    private function assertPages($filters, $pageIndex, $pageSize, $count, $total)
    {
        $pages = $this->pageGateway->findAll($filters, $pageIndex, $pageSize);
        $this->assertThat(count($pages), $this->equalTo($count));
        $this->assertThat($this->pageGateway->countAll($filters), $this->equalTo($total));
    }

    private function givenPages($number)
    {
        for($i = 0; $i != $number; ++$i)
            $this->givenPage('en', 'page' . $i, 'Page ' . $i);
    }

    private function givenPage($language, $url, $title = '')
    {
        $page = new Page(null, $language, $url, $title, '');

        return $this->pageGateway->save($page);
    }

    private function givenPageTranslation(Page $page, $language, $url, $title = '')
    {
        $translation = new PageTranslation(null, $language, $url, $title, '');

        $page->addTranslation($translation);

        $this->pageGateway->save($page);
    }

    private function assertPage($page, $language, $url)
    {
        $this->assertInstanceOf('Diside\SecurityComponent\Model\Page', $page);
        $this->assertTrue($page->hasTranslation($language));
        $this->assertThat($page->getTranslation($language)->getUrl(), $this->equalTo($url));
    }

}