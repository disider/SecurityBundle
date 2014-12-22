<?php

namespace Diside\SecurityBundle\Form;

use Diside\SecurityBundle\Factory\EntityFactory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PageTranslationForm extends AbstractType
{
    /** @var EntityFactory */
    private $entityFactory;

    public function __construct(EntityFactory $entityFactory)
    {
        $this->entityFactory = $entityFactory;
    }


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
        $resolver->setDefaults(array(
            'data_class' => $this->entityFactory->getClass('page_translation'),
        ));
    }

}