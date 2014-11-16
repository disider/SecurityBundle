<?php

namespace Diside\SecurityBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Diside\SecurityBundle\Form\Data\RequestResetPasswordFormData;

class RequestResetPasswordForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('email', 'text', array('label' => 'form.email'));
    }

    public function getName()
    {
        return 'request_reset_password';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('data_class' => 'Diside\SecurityBundle\Form\Data\RequestResetPasswordFormData'));
    }

}