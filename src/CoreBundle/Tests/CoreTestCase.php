<?php

namespace CoreBundle\Tests;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CoreTestCase extends WebTestCase
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->client = static::createClient();
    }

    /**
     * Clear db
     */
    protected function clearDb()
    {
        $purger = new ORMPurger();
        $purger->setPurgeMode(ORMPurger::PURGE_MODE_TRUNCATE);

        $executor = new ORMExecutor($this->getEntityManager(), $purger);

        $this->getEntityManager()->getConnection()->query('SET FOREIGN_KEY_CHECKS = 0');
        $executor->execute([]);
        $this->getEntityManager()->getConnection()->query('SET FOREIGN_KEY_CHECKS = 1');
    }

    /**
     * @param FixtureInterface $fixtures
     * @return array
     */
    protected function loadFixtures(FixtureInterface $fixtures)
    {
        $loader = new Loader();
        $loader->addFixture($fixtures);

        $purger = new ORMPurger();
        $purger->setPurgeMode(ORMPurger::PURGE_MODE_TRUNCATE);

        $executor = new ORMExecutor($this->getEntityManager(), $purger);

        $fixtures = $loader->getFixtures();

        $this->getEntityManager()->getConnection()->query('SET FOREIGN_KEY_CHECKS = 0');
        $executor->execute($loader->getFixtures());
        $this->getEntityManager()->getConnection()->query('SET FOREIGN_KEY_CHECKS = 1');

        return $fixtures;
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager(): EntityManager
    {
        return $this->getContainer()->get('doctrine.orm.entity_manager');
    }

    /**
     * @return ContainerInterface
     */
    protected function getContainer(): ContainerInterface
    {
        return static::$kernel->getContainer();
    }
}
