<?php

namespace Diside\SecurityBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use SecurityComponent\Model\User;
use Diside\SecurityBundle\Security\LoggedUser;
use Diside\SecurityBundle\Form\Data\UserFormData;

class UserForm extends AbstractType
{
    /** @var User */
    private $user;

    public function __construct(LoggedUser $user)
    {
        $this->user = $user;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var UserFormData $formData */
            $formData = $event->getData();
            $form = $event->getForm();

            if ($formData) {
                if ($this->user->isSuperadmin()) {
                    $form->add('company_id', 'choice', array(
                        'label' => 'form.company',
                        'choices' => $formData->getCompanies(),
                    ));

                    $form->add('max_checklist_templates', 'number', array(
                        'label' => 'form.max_checklist_templates'
                    ));
                } else {
                    $form->add('company', 'genemu_plain', array(
                        'label' => 'form.company',
                    ));
                }

                if($this->user->isAdmin()) {
                    $form->add('email', 'text', array('label' => 'form.email'));
                } else {
                    $form->add('email', 'genemu_plain', array(
                        'label' => 'form.email',
                    ));
                }

                if ($this->user->getId() !== $formData->getId()) {
                    $form->add('password', 'password', array('label' => 'form.password'));
                }
            }
        });

        if ($this->user->isAdmin()) {
            if ($this->user->isSuperadmin())
                $roles = User::getSuperadminRoles();
            else
                $roles = User::getUserRoles();

            $builder->add('roles', 'choice', array(
                'choices' => array_combine($roles, $roles),
                'multiple' => true,
                'expanded' => false
            ));

            $builder->add('is_active', 'checkbox', array('label' => 'form.is_active'));
        }

        $builder->add('save', 'submit', array('label' => 'form.save'));
        $builder->add('save_and_close', 'submit', array('label' => 'form.save_and_close', 'button_class' => 'btn btn-default'));
    }

    public function getName()
    {
        return 'user';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('data_class' => 'Diside\SecurityBundle\Form\Data\UserFormData'));
    }

}