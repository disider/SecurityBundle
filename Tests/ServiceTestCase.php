<?php

namespace Diside\SecurityBundle\Tests;

use Diside\SecurityBundle\DisideSecurityBundle;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Kernel;

class ServiceTestCase extends \PHPUnit_Framework_TestCase
{
    /** @var ContainerInterface */
    protected static $container;

    protected static $application;

    public static function setUpBeforeClass()
    {
        $kernel = static::createKernel();
        $kernel->boot();

        self::$container = $kernel->getContainer();
        self::$application = new Application($kernel);

        self::$application->setAutoExit(false);

        self::initDatabase();
    }

    protected static function getService($service)
    {
        return static::$container->get($service);
    }

    protected function assertService($id, $class)
    {
        $service = $this->getService($id);
        $this->assertInstanceOf($class, $service);
    }

    private static function createKernel()
    {
        return new TestingKernel('test', true);
    }

    protected static function initDatabase()
    {
        self::runConsole("doctrine:schema:drop", array("--force" => true));
        self::runConsole("doctrine:schema:create");
        self::runConsole("cache:warmup");
    }

    protected static function runConsole($command, Array $options = array())
    {
        $options["--env"] = "test";
        $options["--quiet"] = null;
        $options["--no-interaction"] = null;
        $options = array_merge($options, array('command' => $command));
        return self::$application->run(new ArrayInput($options));
    }
}

class TestingKernel extends Kernel
{
    /**
     * Returns an array of bundles to registers.
     *
     * @return BundleInterface[] An array of bundle instances.
     *
     * @api
     */
    public function registerBundles()
    {
        $bundles = array(
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new \Symfony\Bundle\TwigBundle\TwigBundle(),
            new \Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new \Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new \Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),

            new DisideSecurityBundle(),
        );

        return $bundles;
    }

    /**
     * Loads the container configuration
     *
     * @param LoaderInterface $loader A LoaderInterface instance
     *
     * @api
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__ . '/config/config.yml');
    }
}
