<?php

namespace Diside\SecurityBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Diside\SecurityBundle\Form\Data\RequestResetPasswordFormData;

class ResetPasswordForm extends AbstractType
{
    /** @var string */
    private $dataClass;

    public function __construct($dataClass)
    {
        $this->dataClass = $dataClass;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('password', 'password', array('label' => 'form.password'));
    }

    public function getName()
    {
        return 'reset_password';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('data_class' => $this->dataClass));
    }

}