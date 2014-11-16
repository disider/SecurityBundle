<?php

namespace Diside\SecurityBundle\DataFixtures\ORM;

use Hautelook\AliceBundle\Alice\DataFixtureLoader;
use Nelmio\Alice\Fixtures;

class FixtureLoader extends DataFixtureLoader
{
    protected function getFixtures()
    {
        return array(
            __DIR__ . '/00-companies.yml',
            __DIR__ . '/10-users.yml',
        );
    }

    protected function getProcessors()
    {
        $processor = new UserProcessor($this->container->get('security.encoder_factory'));

        return array($processor);
    }

}