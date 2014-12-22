<?php

namespace Diside\SecurityBundle\Tests\Form;

use Mockery as m;
use Diside\SecurityBundle\Form\CompanyForm;
use Diside\SecurityBundle\Tests\FormTestCase;

class CompanyFormTest extends FormTestCase
{
    /** @var CompanyForm */
    private $form;

    protected function setUp()
    {
        parent::setUp();

        $entityFactory = m::mock('Diside\SecurityBundle\Factory\EntityFactory');
        $entityFactory->shouldReceive('getClass')
            ->andReturn('Diside\SecurityBundle\Entity\Company');

        $this->form = new CompanyForm($entityFactory);
    }

    /**
     * @test
     */
    public function testConstructor()
    {
        $this->assertInstanceOf('Symfony\Component\Form\AbstractType', $this->form);
        $this->assertThat($this->form->getName(), $this->equalTo('company'));
    }

    /**
     * @test
     */
    public function whenBuildingForm_thenFormHasTitle()
    {
        $builder = m::mock('\Symfony\Component\Form\FormBuilder');
        $expect = $builder->shouldReceive('add')
            ->times(3);

        $this->form->buildForm($builder, array());

        $expect->verify();
    }

    /**
     * @test
     */
    public function whenSettingDefaultOptions_thenHasDataClass()
    {
        /** @var \Symfony\Component\OptionsResolver\OptionsResolverInterface $resolver */
        $resolver = m::mock('Symfony\Component\OptionsResolver\OptionsResolverInterface');
        $expect = $resolver->shouldReceive('setDefaults')
            ->with(array('data_class' => 'Diside\SecurityBundle\Entity\Company'))
            ->once();

        $this->form->setDefaultOptions($resolver);
        $expect->verify();
    }

    /**
     * @test
     */
    public function whenSubmittingValidData_thenReturnValidRequest()
    {
        $name = 'Acme';
        $formData = array(
            'name' => $name
        );

        $form = $this->factory->create($this->form);

        $form->submit($formData);

        $company = $form->getData();
        $this->assertThat($company->getName(), $this->equalTo($name));
    }
}