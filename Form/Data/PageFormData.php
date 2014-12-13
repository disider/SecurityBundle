<?php

namespace Diside\SecurityBundle\Form\Data;

use Diside\SecurityComponent\Model\Page;
use Symfony\Component\Validator\Constraints as Assert;

class PageFormData
{
    /** @var string */
    private $id;

    /** @var string */
    private $title;

    /** @var string */
    private $url;

    /** @var string */
    private $content;


    public function __construct(Page $page = null)
    {
        if ($page) {
            $this->id = $page->getId();
        }
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }
}