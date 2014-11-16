<?php

namespace Diside\SecurityBundle\Twig;

use Twig_Extension;
use Twig_SimpleFunction;

class IconExtension extends Twig_Extension
{
    public function getFunctions()
    {
        return array(
            new Twig_SimpleFunction('fa_icon', array($this, 'showIcon'), array('is_safe' => array('html'))),
        );
    }

    public function showIcon($icon, $class = '')
    {
        return sprintf('<span class="fa fa-%s %s"></span>', $icon, $class ? 'fa-' . $class : '');
    }

    public function getName()
    {
        return 'whalist_frontend_icon';
    }
}
