<?php

namespace Diside\SecurityBundle\DataFixtures\ORM;

use Diside\SecurityBundle\Entity\User;
use Nelmio\Alice\ProcessorInterface;
use Diside\SecurityComponent\Model\User as UserModel;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

class UserProcessor implements ProcessorInterface
{
    /** @var EncoderFactoryInterface */
    private $encoderFactory;

    public function __construct(EncoderFactoryInterface $encoderFactory)
    {
        $this->encoderFactory = $encoderFactory;
    }

    public function preProcess($object)
    {
        if (!($object instanceof User)) {
            return;
        }

        $model = $object->toModel();

        $encoder = $this->encoderFactory->getEncoder($model);
        $object->setPassword($encoder->encodePassword($object->getPassword(), $object->getSalt()));
    }

    public function postProcess($object)
    {
    }
}