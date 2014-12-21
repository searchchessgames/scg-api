<?php

namespace SearchChessGames\GameBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class GameController extends Controller
{
    public function searchAction()
    {
        $query = $this->getRequest()->get('q');
        if (!$query) {
            return new JsonResponse([
                'error' => 'Please enter a search query',
            ]);
        }

        $page = (int) $this->getRequest()->get('p', 1);

        $repo = $this->getDoctrine()
            ->getEntityManager()
            ->getRepository('SearchChessGamesGameBundle:Game');
        $games = $repo->findBySearchQuery($query, $page);
        $count = $repo->countBySearchQuery($query);

        return new JsonResponse([
            'games' => $games,
            'query' => $query,
            'page' => $page,
            'count' => $count
        ]);
    }
}
