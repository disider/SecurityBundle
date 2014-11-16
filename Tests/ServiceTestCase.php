<?php

namespace Diside\SecurityBundle\Tests;

use Diside\SecurityBundle\DisideSecurityBundle;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Console\Input\ArrayInput;
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

//    protected static $entityManager;
//    protected static $client;
//
//    protected static $isFirstTest = true;
//
//    /**
//     * Prepare each test
//     */
//    public function setUp()
//    {
//        parent::setUp();
//
//        static::$client = static::createClient();
//
//        if (!$this->useCachedDatabase()) {
//            $this->databaseInit();
//            $this->loadFixtures();
//        }
//    }
//
//    /**
//     * Initialize database
//     */
//
//    /**
//     * Load tests fixtures
//     */
//    protected function loadFixtures()
//    {
//        $this->runConsole("doctrine:fixtures:load");
//    }
//
//    /**
//     * Use cached database for testing or return false if not
//     */
//    protected function useCachedDatabase()
//    {
//        $container = static::$kernel->getContainer();
//        $registry = $container->get('doctrine');
//        $om = $registry->getEntityManager();
//        $connection = $om->getConnection();
//
//        if ($connection->getDriver() instanceOf SqliteDriver) {
//            $params = $connection->getParams();
//            $name = isset($params['path']) ? $params['path'] : $params['dbname'];
//            $filename = pathinfo($name, PATHINFO_BASENAME);
//            $backup = $container->getParameter('kernel.cache_dir') . '/'.$filename;
//
//            // The first time we won't use the cached version
//            if (self::$isFirstTest) {
//                self::$isFirstTest = false;
//                return false;
//            }
//
//            self::$isFirstTest = false;
//
//            // Regenerate not-existing database
//            if (!file_exists($name)) {
//                @unlink($backup);
//                return false;
//            }
//
//            $om->flush();
//            $om->clear();
//
//            // Copy backup to database
//            if (!file_exists($backup)) {
//                copy($name, $backup);
//            }
//
//            copy($backup, $name);
//            return true;
//        }
//
//        return false;
//    }
