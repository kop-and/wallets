<?php

namespace App\Tests;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Router;

/**
 * Class IntegrationTestBase The functional test base should be used where the scope of test is to ensure connected classes
 * process correctly. This will ensure e.g. database entities are created.
 *
 * @package Tests\Helpers
 */
class IntegrationTestBase extends WebTestCase
{
    /** @var  Application $application */
    protected static $application;

    /** @var  Client $client */
    protected static $client;

    protected static $server_parameters;

    /** @var  ContainerInterface $container */
    protected static $container;

    /** @var  EntityManager $entityManager */
    protected static $entityManager;

    /** @var  Registry $registry */
    protected static $registry;

    /** @var  Router $router */
    protected static $router;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        self::$client = static::createClient();
        self::$container = self::$client->getContainer();
        self::$entityManager = self::$container->get('doctrine.orm.entity_manager');
        self::$registry = self::$container->get('doctrine');
        self::$router =  self::$container->get('router');

        self::$server_parameters = [];
        self::$server_parameters['CONTENT_TYPE'] = 'application/json';
        self::$server_parameters['ACCEPT'] = 'application/json';

        parent::setUp();
    }

    protected static function getApplication()
    {
        if (null === self::$application) {
            $client = static::createClient();

            self::$application = new Application($client->getKernel());
            self::$application->setAutoExit(false);
        }

        return self::$application;
    }


    /**
     * @param string $method
     * @param $url
     * @param array $content
     * @param array $parameters
     * @param array $files
     */
    public function sendRequest($method = 'GET', $url, $content = [], $parameters = [], $files = [], $json = true)
    {
        self::$client->request($method, $url, $parameters, $files, self::$server_parameters, ($json ? json_encode($content): $content));
    }

    /**
     * Returns response content as array
     *
     * @return array
     */
    protected function getContentAsArray()
    {
        return json_decode(self::$client->getResponse()->getContent(), true);
    }
}