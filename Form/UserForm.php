<?php

namespace Diside\SecurityBundle\Form;

use Diside\SecurityBundle\Factory\EntityFactory;
use Diside\SecurityBundle\Presenter\BasePresenter;
use Diside\SecurityBundle\Security\LoggedUser;
use Diside\SecurityComponent\Interactor\InteractorFactory;
use Diside\SecurityComponent\Interactor\Presenter\CompaniesPresenter;
use Diside\SecurityComponent\Interactor\Presenter\CompanyPresenter;
use Diside\SecurityComponent\Interactor\Request\FindCompaniesRequest;
use Diside\SecurityComponent\Interactor\Request\GetCompanyRequest;
use Diside\SecurityComponent\Interactor\SecurityInteractorRegister;
use Diside\SecurityComponent\Model\Company;
use Diside\SecurityComponent\Model\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserForm extends AbstractType implements CompaniesPresenter
{
    /** @var array */
    private $errors;

    /** @var User */
    private $user;

    /** @var array */
    private $companies = array();

    /** @var int */
    private $totalCompanies;

    /** @var InteractorFactory */
    private $interactorFactory;

    /** @var EntityFactory */
    private $entityFactory;

    public function __construct(LoggedUser $user, InteractorFactory $interactorFactory, EntityFactory $entityFactory)
    {
        $this->user = $user;
        $this->interactorFactory = $interactorFactory;
        $this->entityFactory = $entityFactory;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($this->user->isSuperadmin()) {
            $this->findCompanies();
        }

        if ($this->user->isSuperadmin()) {
            $companyForm = $builder->create(
                'company',
                'choice',
                array(
                    'label' => 'form.company',
                    'choices' => $this->companies,
                )
            );

            $transformer = new ModelTransformer($this->interactorFactory, $this->entityFactory);
            $companyForm->addModelTransformer($transformer);
            $builder->add($companyForm);
        }

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
                /** @var User $formData */
                $formData = $event->getData();
                $form = $event->getForm();

                if ($formData) {
                    if ($this->user->isAdmin()) {
                        $form->add('email', 'text', array('label' => 'form.email'));
                    } else {
                        $form->add(
                            'email',
                            'genemu_plain',
                            array(
                                'label' => 'form.email',
                            )
                        );
                    }

                    if ($this->user->getId() !== $formData->getId()) {
                        $form->add('password', 'password', array('label' => 'form.password'));
                    }
                }
            }
        );

        if ($this->user->isAdmin()) {
            if ($this->user->isSuperadmin()) {
                $roles = User::getSuperadminRoles();
            } else {
                $roles = User::getUserRoles();
            }

            $builder->add(
                'roles',
                'choice',
                array(
                    'choices' => array_combine($roles, $roles),
                    'multiple' => true,
                    'expanded' => false
                )
            );

            $builder->add('is_active', 'checkbox', array('label' => 'form.is_active'));
        }

        $builder->add('save', 'submit', array('label' => 'form.save'));
        $builder->add(
            'save_and_close',
            'submit',
            array(
                'label' => 'form.save_and_close',
                'button_class' => 'btn btn-default'
            )
        );
    }

    public function getName()
    {
        return 'user';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('data_class' => $this->entityFactory->getClass('user')));
    }

    protected function findCompanies()
    {
        $interactor = $this->interactorFactory->get(SecurityInteractorRegister::FIND_COMPANIES);
        $request = new FindCompaniesRequest($this->user->getId());
        $interactor->process($request, $this);
    }

    /** @return array */
    public function getCompanies()
    {
        return $this->companies;
    }

    public function setCompanies(array $companies)
    {
        $this->companies[] = '';

        foreach ($companies as $company) {
            $this->companies[$company->getId()] = $company;
        }
    }

    /** @return int */
    public function getTotalCompanies()
    {
        return $this->totalCompanies;
    }

    public function setTotalCompanies($total)
    {
        $this->totalCompanies = $total;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function setErrors(array $errors)
    {
        $this->errors = $errors;
    }

    public function hasErrors()
    {
        return $this->errors != null;
    }

}

class ModelTransformer extends BasePresenter implements DataTransformerInterface, CompanyPresenter
{
    /** @var InteractorFactory */
    private $interactorFactory;

    /** @var EntityFactory */
    private $entityFactory;

    /** @var Company */
    private $company;

    public function __construct(InteractorFactory $interactorFactory, EntityFactory $entityFactory)
    {
        $this->interactorFactory = $interactorFactory;
        $this->entityFactory = $entityFactory;
    }

    public function transform($object)
    {
        if (null === $object) {
            return null;
        }

        return $object->getId();
    }

    public function reverseTransform($id)
    {
        if (!$id) {
            return null;
        }

        $interactor = $this->interactorFactory->get(SecurityInteractorRegister::GET_COMPANY);
        $request = new GetCompanyRequest($id);
        $interactor->process($request, $this);

        return $this->getCompany();
    }

    public function getCompany()
    {
        return $this->company;
    }

    public function setCompany(Company $company)
    {
        $this->company = $this->entityFactory->create('company', $company);
    }

}