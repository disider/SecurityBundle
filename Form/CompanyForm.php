<?php

namespace Diside\SecurityBundle\Form;

use Diside\SecurityBundle\Factory\EntityFactory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CompanyForm extends AbstractType
{
    /** @var EntityFactory */
    private $entityFactory;

    public function __construct(EntityFactory $entityFactory)
    {
        $this->entityFactory = $entityFactory;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text', array('label' => 'form.name'));
        $builder->add('save', 'submit', array('label' => 'form.save'));
        $builder->add('save_and_close', 'submit', array('label' => 'form.save_and_close'));
    }

    public function getName()
    {
        return 'company';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => $this->entityFactory->getClass('company')
        ));
    }

}