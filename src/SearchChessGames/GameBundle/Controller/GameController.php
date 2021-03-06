<?php

namespace SearchChessGames\GameBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class GameController extends Controller
{
    public function searchAction()
    {
        $query = $this->get('request')->get('q');
        if (!$query) {
            return new JsonResponse([
                'error' => 'Please enter a search query',
            ]);
        }

        $page = (int) $this->get('request')->get('p', 1);

        $repo = $this->getDoctrine()
            ->getManager()
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

    public function gameAction()
    {
        $slug = $this->get('request')->get('slug');
        if (!$slug) {
            return new JsonResponse(null, 404);
        }

        $repo = $this->getDoctrine()
            ->getManager()
            ->getRepository('SearchChessGamesGameBundle:Game');
        $game = $repo->findOneBySlug($slug);

        if (!$game) {
            return new JsonResponse(null, 404);
        }

        return new JsonResponse([
            'game' => $game,
        ]);
    }
}
