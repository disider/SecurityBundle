<?php

namespace Diside\SecurityBundle\Form;

use Diside\SecurityBundle\Form\Data\PageFormData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PageForm extends AbstractType
{
    /**
     * @var
     */
    private $availableLocales;

    public function __construct(array $availableLocales)
    {
        $this->availableLocales = $availableLocales;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('url', 'text', array('label' => 'form.url'));
        $builder->add('title', 'text', array('label' => 'form.title'));
        $builder->add('content', 'textarea', array('label' => 'form.content'));

        $builder->add('translations', new PageTranslationsForm($this->availableLocales));

        $builder->add('save', 'submit', array('label' => 'form.save'));
        $builder->add('save_and_close', 'submit', array('label' => 'form.save_and_close'));
    }

    public function getName()
    {
        return 'page';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Diside\SecurityBundle\Form\Data\PageFormData',
            'locales' => array()
        ));
    }

}