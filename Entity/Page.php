<?php

namespace Diside\SecurityBundle\Entity;

use Diside\SecurityComponent\Model\Page as Model;
use Doctrine\Common\Collections\ArrayCollection;

class Page
{
    /** @var  string */
    protected $id;

    /** @var ArrayCollection */
    protected $translations;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @param Model $model
     */
    public function fromModel($model)
    {
        foreach ($model->getTranslations() as $translationModel) {
            $translation = new PageTranslation();
            $translation->fromModel($translationModel);
            $this->addTranslation($translation);
        }

    }

    public function toModel()
    {
        $model = new Model($this->id);
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