<?php

namespace Diside\SecurityBundle\Tests;

abstract class EntityTest extends \PHPUnit_Framework_TestCase
{

    protected function assertField($expected, $current, $field)
    {
        $method = $this->getMethod($expected, $field);

        $this->assertThat($expected->$method(), $this->equalTo($current->$method()));
    }

    private function getMethod($expected, $field)
    {
        $class = new \ReflectionClass(get_class($expected));
        if ($class->hasMethod('get' . ucfirst($field))) {
            return 'get' . ucfirst($field);
        }

        if ($class->hasMethod('is' . ucfirst($field))) {
            return 'is' . ucfirst($field);
        }

        throw new \Exception('Undefined getter for field: ' . $field);
    }

}