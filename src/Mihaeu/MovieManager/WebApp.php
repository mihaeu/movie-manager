<?php

namespace Mihaeu\MovieManager;

use Slim\Slim;

class WebApp
{
    /**
     * @var Slim
     */
    private $app;


    public function __construct()
    {
        $this->app = new Slim();
        $this->configureRoutes();
    }

    public function configureRoutes()
    {
        // @TODO move this to a separate route
        $this->app->get('/', function () {
            $dir = '/media/media/videos/movies';

            $movieHandler = new MovieHandler;
            $movieFiles = $movieHandler->findMoviesInDir($dir);

            // @TODO adjust to slim
            // return View::make('index', ['files' => $movieFiles]);
            $loader = new \Twig_Loader_Filesystem(__DIR__.'/../../../templates/movie-manager');
            $twig = new \Twig_Environment($loader);
            echo $twig->render('index.html.twig', ['files' => $movieFiles]);
        });

        // @TODO should be get
        $this->app->post('/imdb/:query', function($query) {
            $movieHandler = new Moab\MovieHandler;
            $result = $movieHandler->searchMoviesOnTMDb($query);

            $suggestions = [];
            foreach ($result as $id => $movie)
            {
                $suggestions[] = "<img src='{$movie['posterThumbnailSrc']}'></img>{$movie['title']} ({$movie['year']})<span class='btn btn-warning rename'>Rename movie</span><input type='hidden' class='imdb-id' value='{$id}'>";
            }

            // @TODO adjust to slim
            return $suggestions;
        });

        // @TODO should be get
        $this->app->post('/movie/:file/:id', function() {
            $movieHandler = new Moab\MovieHandler;
            $success = $movieHandler->handleMovie($file, $id);

            // @TODO adjust to slim
            return Response::json(['success' => $success]);
        });
    }

    public function run()
    {
        $this->app->run();
    }
}