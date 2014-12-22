<?php

namespace Diside\SecurityBundle\Form;

use Diside\SecurityBundle\Factory\EntityFactory;
use Diside\SecurityBundle\Form\Data\PageFormData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PageForm extends AbstractType
{
    /** @var array */
    private $availableLocales;

    /** @var EntityFactory */
    private $entityFactory;

    public function __construct(array $availableLocales, EntityFactory $entityFactory)
    {
        $this->availableLocales = $availableLocales;
        $this->entityFactory = $entityFactory;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('url', 'text', array('label' => 'form.url'));
        $builder->add('title', 'text', array('label' => 'form.title'));
        $builder->add('content', 'textarea', array('label' => 'form.content'));

        $builder->add('translations', new PageTranslationsForm($this->availableLocales, $this->entityFactory));

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
            'data_class' => $this->entityFactory->getClass('page'),
        ));
    }

}