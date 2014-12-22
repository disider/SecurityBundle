<?php

namespace Diside\SecurityBundle\Form;

use Diside\SecurityBundle\Factory\EntityFactory;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PageTranslationsForm extends AbstractType
{
    /** @var array */
    private $availableLocales;

    /** @var EntityFactory */
    private $entityFactory;

    public function __construct(array $availableLocales, EntityFactory $entityFactory)
    {
        $this->availableLocales = $availableLocales;
        $this->entityFactory = $entityFactory;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber(new TranslationListener());

        foreach ($this->availableLocales as $locale) {
            $builder->add($locale, new PageTranslationForm($this->entityFactory));
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                //'by_reference' => false,
            )
        );
    }

    public function getName()
    {
        return 'page_translations';
    }

}

class TranslationListener implements EventSubscriberInterface
{
    public function submit(FormEvent $event)
    {
        $data = $event->getData();

        foreach ($data as $locale => $translation) {
            if (!$translation) {
                $data->removeElement($translation);
            } else {
                $translation->setLocale($locale);
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::SUBMIT => 'submit',
        );
    }
}