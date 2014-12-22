<?php

namespace Diside\SecurityBundle\Factory;

interface AbstractRequestFactory
{
    public function build($data, array $params = array());

    public function getName();
}