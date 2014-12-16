<?php

namespace Diside\SecurityBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PageTranslationForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('url', 'text', array('label' => 'form.url'));
        $builder->add('title', 'text', array('label' => 'form.title'));
        $builder->add('content', 'textarea', array('label' => 'form.content'));
    }

    public function getName()
    {
        return 'page_translation';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('data_class' => 'Diside\SecurityBundle\Form\Data\PageTranslationFormData'));
    }

}