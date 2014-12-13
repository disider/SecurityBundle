<?php

namespace Diside\SecurityBundle\Form\Data;

use Symfony\Component\Validator\Constraints as Assert;
use Diside\SecurityComponent\Model\Page;

class PageFormData
{
    /** @var string */
    private $id;

    public function __construct(Page $page = null)
    {
        if($page) {
            $this->id = $page->getId();
        }
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }
}