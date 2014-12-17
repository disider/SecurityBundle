<?php

namespace Diside\SecurityBundle\Tests\Form;

use Diside\SecurityBundle\Form\Data\PageFormData;
use Diside\SecurityBundle\Form\PageForm;
use Diside\SecurityBundle\Tests\FormTestCase;
use Mockery as m;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PageFormTest extends FormTestCase
{
    /** @var PageForm */
    private $form;

    protected function setUp()
    {
        parent::setUp();

        $this->form = new PageForm(array('it'));
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
    public function whenBuildingForm_thenFormHasFields()
    {
        $builder = m::mock('\Symfony\Component\Form\FormBuilder');
        $expect = $builder->shouldReceive('add')
            ->times(6);

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
            ->with(
                array(
                    'data_class' => 'Diside\SecurityBundle\Form\Data\PageFormData',
                    'locales' => array()
                )
            )
            ->once();

        $this->form->setDefaultOptions($resolver);
        $expect->verify();
    }

    /**
     * @test
     */
    public function whenSubmittingValidData_thenReturnValidRequest()
    {
        $formData = array(
            'url' => 'title',
            'title' => 'Title',
            'content' => 'Content',
            'translations' => array(
                'it' => array(
                    'language' => 'it',
                    'url' => 'titolo',
                    'title' => 'Titolo',
                    'content' => 'Contenuto'
                )
            )
        );

        $form = $this->factory->create($this->form);

        $form->submit($formData);

        /** @var PageFormData $page */
        $page = $form->getData();
        $this->assertThat($page->getUrl(), $this->equalTo('title'));
        $this->assertThat($page->getTitle(), $this->equalTo('Title'));
        $this->assertThat($page->getContent(), $this->equalTo('Content'));

        $translations = $page->getTranslations();
        $this->assertThat($translations['it']->getUrl(), $this->equalTo('titolo'));
        $this->assertThat($translations['it']->getTitle(), $this->equalTo('Titolo'));
        $this->assertThat($translations['it']->getContent(), $this->equalTo('Contenuto'));
    }
}