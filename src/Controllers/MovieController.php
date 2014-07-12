<?php

namespace Mihaeu\MovieManager\Controllers;

use Mihaeu\MovieManager\Config;
use Mihaeu\MovieManager\MovieFinder;
use Mihaeu\MovieManager\MovieDatabase\TMDb;

use Mihaeu\MovieManager\MovieHandler;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

/**
 * MovieController for Silex.
 *
 * @author Michael Haeuslmann (haeuslmann@gmail.com)
 */
class MovieController
{
    public function index(Application $app, Request $request)
    {
        $dir = $request->get('dir');
        if (empty($dir)) {
            $dir = '/media/media/videos/movies';
        }

        $config = new Config();
        $finder = new MovieFinder();
        $movieFiles = $finder->findMoviesInDir($dir, $config->get('allowed-movie-formats'));

        return $app['twig']->render('index.html.twig', ['files' => $movieFiles]);
    }

    public function suggestions(Application $app, Request $request) {
        $config = new Config();
        $tmdb = new TMDb($config->get('tmdb-api-key'));
        $suggestions = $tmdb->getMovieSuggestionsFromQuery($request->get('query'));

        if (empty($suggestions)) {
            return $app->json(['message' => 'No match found.'], 404);
        }
        return $app->json($suggestions);
    }

    public function movie(Application $app, Request $request) {
        $id   = $request->get('id');
        $file = $request->get('file');

        $config = new Config();
        $movieHandler = new MovieHandler($config);
        $result = $movieHandler->handleMovie($file, $id);
        if ($result === false) {
            return $app->json(['message' => 'failure'], 404);
        }

        return $app->json(['message' => 'success'], 200);
    }
} 