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
        $this->language = $model->getLanguage();
        $this->url = $model->getUrl();
        $this->title = $model->getTitle();
        $this->content = $model->getContent();

        /** @var PageTranslationModel $translationModel */
        foreach ($model->getTranslations() as $translationModel) {
            $translation = new PageTranslation();
            $translation->fromModel($translationModel);
            $this->addTranslation($translation);
        }
    }

    public function toModel()
    {
        $model = new Model($this->id, $this->language, $this->url, $this->title, $this->content);

        foreach ($this->getTranslations() as $translation) {
            $model->addTranslation($translation->toModel());
        }

        return $model;
    }

    private function getTranslations()
    {
        return $this->translations;
    }

    private function addTranslation(PageTranslation $translation)
    {
        $this->translations->add($translation);
        $translation->setPage($this);
    }

}