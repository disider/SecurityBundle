<?php

namespace Diside\SecurityBundle\Form\Data;

use Diside\SecurityComponent\Model\Page;
use Doctrine\Common\Collections\ArrayCollection;
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

    /** @var array */
    private $translations = array();

    /** @var Page */
    private $page;

    public function setPage(Page $page)
    {
        $this->id = $page->getId();
        $this->url = $page->getUrl();
        $this->title = $page->getTitle();
        $this->content = $page->getContent();
        $this->page = $page;
    }

    public function setAvailableLocales(array $availableLocales)
    {
        foreach ($availableLocales as $locale) {
            $this->translations[$locale] = new PageTranslationFormData(
                $this->getTranslation($locale)
            );
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

    public function getTranslations()
    {
        return $this->translations;
    }

    public function setTranslations($translations)
    {
        $this->translations = $translations;
    }

    /**
     * @param $locale
     * @return $this|\Diside\SecurityComponent\Model\PageTranslation|null
     */
    protected function getTranslation($locale)
    {
        return ($this->page && $this->page->hasTranslation($locale)) ? $this->page->getTranslation($locale) : null;
    }

}