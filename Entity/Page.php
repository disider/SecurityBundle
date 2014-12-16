<?php

namespace Diside\SecurityBundle\Entity;

use Diside\SecurityComponent\Model\Page as Model;
use Diside\SecurityComponent\Model\PageTranslation as PageTranslationModel;
use Doctrine\Common\Collections\ArrayCollection;

class Page
{
    /** @var  string */
    protected $id;

    /** @var string */
    protected $language;

    /** @var string */
    protected $title;

    /** @var string */
    protected $url;

    /** @var string */
    protected $content;

    /** @var ArrayCollection */
    protected $translations;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
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

        /** @var PageTranslationModel $translationModel */
        foreach ($model->getTranslations() as $translationModel) {
            $translation = $this->hasTranslationId($translationModel->getId())
                ? $this->getTranslationById($translationModel->getId())
                : $this->buildTranslation();

            $translation->fromModel($translationModel);
        }
    }

    public function toModel()
    {
        $model = new Model($this->id, $this->language, $this->url, $this->title, $this->content);

        /** @var PageTranslation $translation */
        foreach ($this->getTranslations() as $translation) {
            $model->addTranslation($translation->toModel());
        }

        return $model;
    }

    public function getTranslations()
    {
        return $this->translations;
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
        $translation = new PageTranslation();
        $this->addTranslation($translation);

        return $translation;
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

}