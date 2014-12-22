<?php

namespace Diside\SecurityBundle\Provider;

class LocaleProvider 
{
    private $defaultLocale;
    
    /** @var array */
    private $locales;

    public function __construct($defaultLocale, array $locales)
    {
        $this->defaultLocale = $defaultLocale;
        $this->locales = $locales;
    }

    /**
     * @return mixed
     */
    public function getDefaultLocale()
    {
        return $this->defaultLocale;
    }

    /**
     * @return array
     */
    public function getAvailableLocales()
    {
        return array_diff($this->locales, array($this->defaultLocale));
    }

}