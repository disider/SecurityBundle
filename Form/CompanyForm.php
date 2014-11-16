<?php

namespace Diside\SecurityBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CompanyForm extends AbstractType
{
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
        $resolver->setDefaults(array('data_class' => 'Diside\SecurityBundle\Form\Data\CompanyFormData'));
    }

}