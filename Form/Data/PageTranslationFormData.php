<?php

namespace Diside\SecurityBundle\Form\Data;

use Diside\SecurityComponent\Model\Page;
use Diside\SecurityComponent\Model\PageTranslation;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

class PageTranslationFormData
{
    /** @var string */
    private $id;

    /** @var string */
    private $title;

    /** @var string */
    private $url;

    /** @var string */
    private $content;

    public function __construct(PageTranslation $translation = null)
    {
        if($translation != null) {
            $this->id = $translation->getId();
            $this->url = $translation->getUrl();
            $this->title = $translation->getTitle();
            $this->content = $translation->getContent();
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