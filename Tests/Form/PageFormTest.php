<?php

namespace Diside\SecurityBundle\Tests\Form;

use Mockery as m;
use Diside\SecurityBundle\Form\PageForm;
use Diside\SecurityBundle\Tests\FormTestCase;

class PageFormTest extends FormTestCase
{
    /** @var PageForm */
    private $form;

    protected function setUp()
    {
        parent::setUp();

        $this->form = new PageForm('en', array('it'));
    }

    /**
     * @test
     */
    public function testConstructor()
    {
        $this->assertInstanceOf('Symfony\Component\Form\AbstractType', $this->form);
        $this->assertThat($this->form->getName(), $this->equalTo('page'));
    }

    /**
     * @test
     */
    public function whenBuildingForm_thenFormHasTitle()
    {
        $builder = m::mock('\Symfony\Component\Form\FormBuilder');
        $expect = $builder->shouldReceive('add')
            ->times(5);

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
            ->with(array('data_class' => 'Diside\SecurityBundle\Form\Data\PageFormData'))
            ->once();

        $this->form->setDefaultOptions($resolver);
        $expect->verify();
    }

    /**
     * @test
     */
    public function whenSubmittingValidData_thenReturnValidRequest()
    {
        $url = 'url';
        $title = 'Title';
        $content = 'Content';
        $formData = array(
            'url' => $url,
            'title' => $title,
            'content' => $content
        );

        $form = $this->factory->create($this->form);

        $form->submit($formData);

        $page = $form->getData();
        $this->assertThat($page->getUrl(), $this->equalTo($url));
        $this->assertThat($page->getContent(), $this->equalTo($content));
        $this->assertThat($page->getTitle(), $this->equalTo($title));
    }
}