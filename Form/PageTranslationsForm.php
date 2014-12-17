<?php

namespace Diside\SecurityBundle\Form;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PageTranslationsForm extends AbstractType
{
    /**
     * @var array
     */
    private $availableLocales;

    public function __construct(array $availableLocales)
    {
        $this->availableLocales = $availableLocales;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach ($this->availableLocales as $locale) {
            $builder->add($locale, new PageTranslationForm());
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'locales' => array()
            )
        );
    }

    public function getName()
    {
        return 'page_translations';
    }


}