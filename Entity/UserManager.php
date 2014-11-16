<?php

namespace Diside\SecurityBundle\Entity;

use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use SecurityComponent\Model\User as UserModel;

class UserManager
{
    /** @var EncoderFactoryInterface */
    private $encoderFactory;

    public function __construct(EncoderFactoryInterface $encoderFactory)
    {
        $this->encoderFactory = $encoderFactory;
    }

    public function updateUser(User $user)
    {
        $model = new UserModel($user->getId(), $user->getEmail(), $user->getPassword(), $user->getSalt());

        $encoder = $this->encoderFactory->getEncoder($model);
        $user->setPassword($encoder->encodePassword($user->getPassword(), $user->getSalt()));

        return $user;
    }

} 