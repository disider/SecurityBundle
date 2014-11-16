<?php

namespace Diside\SecurityBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ChangePasswordForm extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('current_password', 'password', array('label' => 'form.current_password'));
        $builder->add('new_password', 'password', array('label' => 'form.new_password'));

        $builder->add('save', 'submit', array('label' => 'form.save'));
    }

    public function getName()
    {
        return 'change_password';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('data_class' => 'Diside\SecurityBundle\Form\Data\ChangePasswordFormData'));
    }

}