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
            $dir = Input::get('dir', '/mnt/usb-passport/videos/movies');

            $movieHandler = new Moab\MovieHandler;
            $movieFiles = $movieHandler->findMoviesInDir($dir);

            // @TODO adjust to slim
            return View::make('index', ['files' => $movieFiles]);
        });

        // @TODO should be get
        $this->app->post('/imdb', function() {
            $query = urldecode(Input::get('query'));

            $movieHandler = new Moab\MovieHandler;
            $result = $movieHandler->searchMoviesOnTMDb($query);

            $suggestions = [];
            foreach ($result as $id => $movie)
            {
                $suggestions[] = "<img src='{$movie['posterThumbnailSrc']}'></img>{$movie['title']} ({$movie['year']})<span class='btn btn-warning rename'>Rename movie</span><input type='hidden' class='imdb-id' value='{$id}'>";
            }

            // @TODO adjust to slim
            return View::make('imdb', ['suggestions' => $suggestions]);
        });

        // @TODO should be get
        $this->app->post('/movie', function() {
            $file = Input::get('file');
            $imdbId = Input::get('id');

            $movieHandler = new Moab\MovieHandler;
            $success = $movieHandler->handleMovie($file, $imdbId);

            // @TODO adjust to slim
            return Response::json(['success' => $success]);
        });
    }

    public function run()
    {
        $this->app->run();
    }
}