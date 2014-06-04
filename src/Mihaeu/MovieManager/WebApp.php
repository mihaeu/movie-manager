<?php

namespace Mihaeu\MovieManager;

use Silex\Application;
use Silex\Provider\TwigServiceProvider;
use Whoops\Provider\Silex\WhoopsServiceProvider;
use Symfony\Component\HttpFoundation\Request;

class WebApp
{
    /**
     * @var Application
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
        $this->app->get('/', function (Application $app, Request $request) {
            $dir = $request->get('dir');
            if (empty($dir)) {
                $dir = '/media/media/videos/movies';
            }

            $movieHandler = new MovieHandler;
            $movieFiles = $movieHandler->findMoviesInDir($dir);

            return $app['twig']->render('index.html.twig', ['files' => $movieFiles]);
        });

        $this->app->get('/imdb', function(Application $app, Request $request) {
            $query = $request->get('query');
            $movieHandler = new MovieHandler();
            $result = $movieHandler->searchMoviesOnTMDb($query);
            if (empty($result)) {
                return $app->json('No match found.', 404);
            }

            $suggestions = [];
            foreach ($result as $id => $movie) {
                $suggestions[] = [
                    'id'     => $id,
                    'title'  => $movie['title'],
                    'year'   => $movie['year'],
                    'poster' => $movie['posterThumbnailSrc']
                ];
            }

            return $app->json($suggestions);
        });

        $this->app->get('/movie', function(Application $app, Request $request) {
            $id   = $request->get('id');
            $file = $request->get('file');
            
            $movieHandler = new MovieHandler();
            $result = $movieHandler->handleMovie($file, $id);
            if ($result === false) {
                return $app->json(['message' => 'failure'], 404);
            }

            return $app->json(['message' => 'success'], 200);
        });
    }

    public function run()
    {
        $this->app->run();
    }
}