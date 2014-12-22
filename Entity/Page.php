<?php

namespace Diside\SecurityBundle\Entity;

use Diside\SecurityComponent\Model\Page as Model;
use Diside\SecurityComponent\Model\PageTranslation as PageTranslationModel;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

class Page
{
    /** @var  string */
    protected $id;

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

    /**
     * @Assert\Valid()
     * @var ArrayCollection
     */
    protected $translations;

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

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }

    public function getTranslations()
    {
        return $this->translations;
    }

    public function setTranslations($translations)
    {
        foreach ($translations as $translation) {
            $this->addTranslation($translation);
        }
    }

    public function addTranslation(PageTranslation $translation)
    {
        $this->translations->add($translation);
        $translation->setPage($this);
    }

    /**
     * @return PageTranslation
     */
    protected function buildTranslation()
    {
        return new PageTranslation();
    }

    private function hasTranslationId($id)
    {
        /** @var PageTranslation $translation */
        foreach ($this->getTranslations() as $translation) {
            if ($translation->getId() == $id) {
                return true;
            }
        }

        return false;
    }

    private function getTranslationById($id)
    {
        /** @var PageTranslation $translation */
        foreach ($this->getTranslations() as $translation) {
            if ($translation->getId() == $id) {
                return $translation;
            }
        }

        return null;
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

        $this->translations = new ArrayCollection();

        /** @var PageTranslationModel $translationModel */
        foreach ($model->getTranslations() as $translationModel) {
            $translation = $this->buildTranslation();
            $this->addTranslation($translation);

            $translation->fromModel($translationModel);
        }
    }

    public function toModel()
    {
        $model = new Model($this->id, $this->locale, $this->url, $this->title, $this->content);

        /** @var PageTranslation $translation */
        foreach ($this->getTranslations() as $translation) {
            $model->addTranslation($translation->toModel());
        }

        return $model;
    }

}