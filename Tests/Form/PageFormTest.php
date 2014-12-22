<?php

namespace Diside\SecurityBundle\Tests\Form;

use Diside\SecurityBundle\Entity\Page;
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

        $entityFactory = m::mock('Diside\SecurityBundle\Factory\EntityFactory');
        $entityFactory->shouldReceive('getClass')
            ->with('page')
            ->andReturn('Diside\SecurityBundle\Entity\Page');
        $entityFactory->shouldReceive('getClass')
            ->with('page_translation')
            ->andReturn('Diside\SecurityBundle\Entity\PageTranslation');

        $this->form = new PageForm(array('it'), $entityFactory);
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
                    'data_class' => 'Diside\SecurityBundle\Entity\Page',
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

        /** @var Page $page */
        $page = $form->getData();
        $this->assertThat($page->getUrl(), $this->equalTo('title'));
        $this->assertThat($page->getTitle(), $this->equalTo('Title'));
        $this->assertThat($page->getContent(), $this->equalTo('Content'));

        $translations = $page->getTranslations();
        $translation = $translations[0];

        $this->assertThat($translation->getUrl(), $this->equalTo('titolo'));
        $this->assertThat($translation->getTitle(), $this->equalTo('Titolo'));
        $this->assertThat($translation->getContent(), $this->equalTo('Contenuto'));
    }
}