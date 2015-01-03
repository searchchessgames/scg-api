<?php

namespace SearchChessGames\TestBundle\Tests;

use AmyBoyd\PgnParser\PgnParser;
use SearchChessGames\GameBundle\Entity\Game;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractFunctionalControllerTest extends WebTestCase
{
    protected $client;

    protected $container;

    protected $entityManager;

    protected function setUp()
    {
        parent::setUp();

        $this->client = static::createClient(['environment' => 'test']);
        $this->request = new Request();
        $this->container = $this->client->getContainer();
        $this->container->set('request', $this->request);
        $this->entityManager = $this->container->get('doctrine.orm.entity_manager');

        $this->clearDatabase();
    }

    private function clearDatabase()
    {
        $this->entityManager
            ->createQuery('DELETE FROM SearchChessGamesGameBundle:Game g')
            ->execute();
    }

    protected function loadPgnFixtureFile($filename)
    {
        $pgnParser = new PgnParser(__DIR__ . '/../Resources/pgn/' . $filename);
        $games = $pgnParser->getGames();

        foreach ($games as $i => $game) {
            $games[$i] = Game::createFromBaseGame($game);
        }

        $this->entityManager
            ->getRepository('SearchChessGamesGameBundle:Game')
            ->setSlugsAndSaveAll($games);
    }
}
