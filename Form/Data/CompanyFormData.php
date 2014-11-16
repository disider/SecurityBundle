<?php

namespace Diside\SecurityBundle\Form\Data;

use Symfony\Component\Validator\Constraints as Assert;
use SecurityComponent\Model\Company;

class CompanyFormData
{
    /** @var string */
    private $id;

    /**
     * @Assert\NotBlank(message="error.empty_name")
     * @var string
     */
    private $name;

    public function __construct(Company $company = null)
    {
        if($company) {
            $this->id = $company->getId();
            $this->name = $company->getName();
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

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }
}