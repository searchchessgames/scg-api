<?php

namespace SearchChessGames\GameBundle\Tests\Controller;

use SearchChessGames\TestBundle\Tests\AbstractFunctionalControllerTest;
use SearchChessGames\GameBundle\Controller\GameController;

class GameControllerTest extends AbstractFunctionalControllerTest
{
    protected function setUp()
    {
        parent::setUp();
        parent::loadPgnFixtureFile('robfin11.pgn');
    }

    public function testSearchAction()
    {
        $this->request->request->set('q', 'e4');

        $controller = new GameController();
        $controller->setContainer($this->container);
        $response = $controller->searchAction();
        $json = json_decode($response->getContent(), true);

        $this->assertEquals(1, $json['page']);
        $this->assertEquals(6, $json['count']);
        $this->assertEquals('e4', $json['query']);
        $this->assertInternalType('array', $json['games']);
        $this->assertCount(6, $json['games']);
    }

    public function testSearchAction_secondPage()
    {
        $this->request->request->set('q', 'e4');
        $this->request->request->set('p', 2);

        $controller = new GameController();
        $controller->setContainer($this->container);
        $response = $controller->searchAction();
        $json = json_decode($response->getContent(), true);

        $this->assertEquals(2, $json['page']);
        $this->assertEquals(6, $json['count']);
        $this->assertEquals('e4', $json['query']);
        $this->assertInternalType('array', $json['games']);
        $this->assertCount(0, $json['games']);
    }

    public function testSearchAction_noResults()
    {
        $this->request->request->set('q', 'h4 e4');

        $controller = new GameController();
        $controller->setContainer($this->container);
        $response = $controller->searchAction();
        $json = json_decode($response->getContent(), true);

        $this->assertEquals(0, $json['count']);
        $this->assertCount(0, $json['games']);
    }

    public function testGameAction_exists()
    {
        $this->request->request->set('slug', 'Robson-Finegold-RobsonFinegold-Classical-Match-2011-3');

        $controller = new GameController();
        $controller->setContainer($this->container);
        $response = $controller->gameAction();
        $json = json_decode($response->getContent(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Robson, Ray', $json['game']['white']);
        $this->assertEquals('1-0', $json['game']['result']);
    }

    public function testGameAction_doesNotExist()
    {
        $this->request->request->set('slug', 'Z-Z-Z');

        $controller = new GameController();
        $controller->setContainer($this->container);
        $response = $controller->gameAction();
        $json = json_decode($response->getContent(), true);

        $this->assertEquals(404, $response->getStatusCode());
    }
}
