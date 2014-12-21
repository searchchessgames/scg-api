<?php

namespace SearchChessGames\GameBundle\Repository;

use Doctrine\ORM\EntityRepository;
use SearchChessGames\GameBundle\Entity\Game;

class GameRepository extends EntityRepository
{
    /**
     * @param  string $search
     * @param  int    $page
     * @return type
     */
    public function findBySearchQuery($search, $page)
    {
        $dbQuery = $this->createDoctrineQueryForSearch($search, false);
        $dbQuery->setFirstResult(($page - 1) * 30);
        $dbQuery->setMaxResults(30);

        return $dbQuery->getResult();
    }

    public function countBySearchQuery($search)
    {
        $dbQuery = $this->createDoctrineQueryForSearch($search, true);

        return $dbQuery->getSingleScalarResult();
    }

    /**
     * @param  string              $search
     * @return \Doctrine\ORM\Query
     */
    private function createDoctrineQueryForSearch($search, $count)
    {
        $em = $this->getEntityManager();
        $dbQuery = $em->createQuery(
                'SELECT ' . ($count ? 'COUNT(g)' : 'g') . '
                FROM SearchChessGamesGameBundle:Game g
                WHERE g.white LIKE :queryX OR g.white LIKE :spaceQueryX
                OR g.black LIKE :queryX OR g.black LIKE :spaceQueryX
                OR g.moves LIKE :queryX
                OR g.event LIKE :queryX OR g.event LIKE :spaceQueryX
                OR g.site LIKE :queryX OR g.site LIKE :spaceQueryX
                ORDER BY g.whiteElo DESC, g.blackElo DESC')
            ->setParameter('queryX', "$search%")
            ->setParameter('spaceQueryX', " $search%");

        return $dbQuery;
    }

    public function setSlugsAndSaveAll(array $games)
    {
        $em = $this->getEntityManager();
        foreach ($games as $game) {
            $slug = $this->getAvailableSlugForGame($game);
            $game->setSlug($slug);
            $em->persist($game);
            self::$unflushedSlugs[] = strtolower($slug);
        }
        $em->flush();
        self::$unflushedSlugs = array();
    }

    public function setSlugAndSave(Game $game)
    {
        $game->setSlug($this->getAvailableSlugForGame($game));
        $em = $this->getEntityManager();
        $em->persist($game);
        $em->flush();
    }

    private function getAvailableSlugForGame(Game $game)
    {
        if ($game->getSlug()) {
            return $game->getSlug();
        }

        $slug = self::getSurname($game->getWhite())
            . '-'
            . self::getSurname($game->getBlack())
            . '-';

        // The event will often contain the year and/or a number like "39th". Remove those.
        $event = preg_replace('/\d+[a-z]+/', '', $game->getEvent());
        $event = preg_replace('/[^a-zA-Z ]/', '', $event);

        $slug .= $event
            . '-'
            . $game->getYear();

        // Tidy up whitespace and multiple dashes ----.
        $slug = str_replace('/', '-', $slug);
        $slug = preg_replace('/\s+/', '-', $slug);
        $slug = preg_replace('/-{2,}/', '-', $slug);

        $isSlugUsed = $this->isSlugUsed($slug);
        if (!$isSlugUsed) {
            return $slug;
        } else {
            $i = 2;
            while ($isSlugUsed) {
                $numberedSlug = $slug . '-' . $i;
                $isSlugUsed = $this->isSlugUsed($numberedSlug);
                $i++;
            }

            return $numberedSlug;
        }
    }

    /**
     * Only put lowercase slugs in here.
     */
    private static $unflushedSlugs = array();

    public function isSlugUsed($slug)
    {
        return in_array(strtolower($slug), self::$unflushedSlugs)
            || $this->findOneBySlug($slug) !== null;
    }

    private static function getSurname($name)
    {
        $comma = strpos($name, ',');
        if ($comma !== false) {
            return substr($name, 0, $comma);
        }

        $space = strrpos($name, ' ');
        if ($space !== false) {
            return substr($name, $space + 1);
        }

        return $name;
    }

}
