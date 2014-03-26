<?php

namespace Mihaeu\MovieManager;

use Silex\Application;
use Silex\Provider\TwigServiceProvider;
use Whoops\Provider\Silex\WhoopsServiceProvider;

class WebApp
{
    /**
     * @var Slim
     */
    private $app;


    public function __construct()
    {
        $this->app = new Application();
        $this->app['debug'] = true;
        if($this->app['debug']) {
            $this->app->register(new WhoopsServiceProvider());
        }

        $this->app->register(new TwigServiceProvider(), [
            'twig.path' => __DIR__.'/../../../templates/movie-manager',
        ]);


        $this->configureRoutes();
    }

    public function configureRoutes()
    {
        // @TODO move this to a separate route
        $this->app->get('/', function (Application $app) {
            $dir = '/media/media/videos/movies';

            $movieHandler = new MovieHandler;
            $movieFiles = $movieHandler->findMoviesInDir($dir);

            return $app['twig']->render('index.html.twig', ['files' => $movieFiles]);
        });

        // @TODO should be get
        $this->app->post('/imdb/{query}', function(Application $app, $query) {
            $movieHandler = new Moab\MovieHandler;
            $result = $movieHandler->searchMoviesOnTMDb($query);
            if (empty($result)) {
                return $app->json('No match found.', 404);
            }

            $suggestions = [];
            foreach ($result as $id => $movie) {
                // $suggestions[] = 
                //     "<img src='{$movie['posterThumbnailSrc']}'></img>
                //     {$movie['title']} ({$movie['year']})
                //     <span class='btn btn-warning rename'>Rename movie</span>
                //     <input type='hidden' class='imdb-id' value='{$id}'>";
                $suggestions[] = [
                    'id'     => $id,
                    'title'  => $movie['title'],
                    'year'   => $movie['year'],
                    'poster' => $movie['posterThumbnailSrc']
                ];
            }

            return $app->json($suggestions);
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