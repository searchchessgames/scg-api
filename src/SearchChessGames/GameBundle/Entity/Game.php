<?php

namespace SearchChessGames\GameBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use AmyBoyd\PgnParser\Game as BaseGame;

/**
 * @ORM\Entity(repositoryClass="SearchChessGames\GameBundle\Repository\GameRepository")
 * @ORM\Table(name="game")
 */
class Game extends BaseGame implements \JsonSerializable
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $viewCount;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $downloadCount;

    /**
     * URL slug.
     *
     * @ORM\Column(type="string", length=100, unique=true)
     */
    private $slug;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * The filename of the PGN database this game came from.
     * @ORM\Column(type="string", length=50, nullable=false)
     */
    protected $fromPgnDatabase;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $pgn;

    /**
     * All moves concatenated, with move numbers removed. Example: "e4 e5 f4 exf4".
     * @ORM\Column(type="text", nullable=true)
     */
    protected $moves;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $movesCount;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $event;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $site;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $date;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $round;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $white;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $black;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $result;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $whiteElo;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $blackElo;

    /**
     * Opening ECO code.
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $eco;

    // Transient fields below here.

    private $datePrettyPrint = false;

    private $eventSitePrettyPrint = false;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->viewCount = 0;
        $this->downloadCount = 0;
    }

    public static function createFromBaseGame(BaseGame $baseGame)
    {
        $game = new Game();
        $game->setFromPgnDatabase($baseGame->getFromPgnDatabase());
        $game->setPgn($baseGame->getPgn());
        $game->setMoves($baseGame->getMoves());
        $game->setMovesCount($baseGame->getMovesCount());
        $game->setEvent($baseGame->getEvent());
        $game->setSite($baseGame->getSite());
        $game->setDate($baseGame->getDate());
        $game->setRound($baseGame->getRound());
        $game->setWhite($baseGame->getWhite());
        $game->setBlack($baseGame->getBlack());
        $game->setResult($baseGame->getResult());
        $game->setWhiteElo($baseGame->getWhiteElo());
        $game->setBlackElo($baseGame->getBlackElo());
        $game->setEco($baseGame->getEco());

        return $game;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * Set viewCount
     *
     * @param integer $viewCount
     */
    public function setViewCount($viewCount)
    {
        $this->viewCount = $viewCount;
    }

    /**
     * Get viewCount
     *
     * @return integer
     */
    public function getViewCount()
    {
        return $this->viewCount;
    }

    public function getDownloadCount()
    {
        return $this->downloadCount;
    }

    public function setDownloadCount($downloadCount)
    {
        $this->downloadCount = $downloadCount;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    public function jsonSerialize()
    {
        return json_decode($this->toJSON());
    }
}
