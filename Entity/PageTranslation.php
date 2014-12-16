<?php

namespace Diside\SecurityBundle\Entity;

use Diside\SecurityComponent\Model\PageTranslation as Model;

class PageTranslation
{
    /** @var  string */
    protected $id;

    /** @var Page */
    protected $page;

    /** @var string */
    protected $language;

    /** @var string */
    protected $title;

    /** @var string */
    protected $url;

    /** @var string */
    protected $content;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function setTitle($title)
    {
        $this->title = $title;
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
        $this->language = $model->getLanguage();
        $this->url = $model->getUrl();
        $this->title = $model->getTitle();
        $this->content = $model->getContent();
    }

    public function toModel()
    {
        return new Model($this->id, $this->language, $this->url, $this->title, $this->content);
    }

}