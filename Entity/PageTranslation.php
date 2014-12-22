<?php

namespace Diside\SecurityBundle\Entity;

use Diside\SecurityComponent\Model\PageTranslation as Model;
use Symfony\Component\Validator\Constraints as Assert;

class PageTranslation
{
    /** @var  string */
    protected $id;

    /** @var Page */
    protected $page;

    /** @var string */
    protected $locale;

    /**
     * @Assert\NotBlank(message="error.empty_number")
     * @var string
     */
    protected $title;

    /**
     * @Assert\NotBlank(message="error.empty_number")
     * @var string
     */
    protected $url;

    /**
     * @Assert\NotBlank(message="error.empty_number")
     * @var string
     */
    protected $content;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getLocale()
    {
        return $this->locale;
    }

    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function getPage()
    {
        return $this->page;
    }

    public function setPage($page)
    {
        $this->page = $page;
    }

    /**
     * @param Model $model
     */
    public function fromModel($model)
    {
        $this->id = $model->getId();
        $this->locale = $model->getLocale();
        $this->url = $model->getUrl();
        $this->title = $model->getTitle();
        $this->content = $model->getContent();
    }

    public function toModel()
    {
        return new Model($this->id, $this->locale, $this->url, $this->title, $this->content);
    }

}