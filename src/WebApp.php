<?php

namespace Mihaeu\MovieManager;

use Mihaeu\MovieManager\MovieDatabase\TMDb;
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
            'twig.path' => __DIR__.'/../templates/movie-manager',
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

            $config = new Config();
            $finder = new MovieFinder();
            $movieFiles = $finder->findMoviesInDir($dir, $config->get('allowed-movie-formats'));

            return $app['twig']->render('index.html.twig', ['files' => $movieFiles]);
        });

        $this->app->get('/suggestions', function(Application $app, Request $request) {
            $config = new Config();
            $tmdb = new TMDb($config->get('tmdb-api-key'));
            $suggestions = $tmdb->getMovieSuggestionsFromQuery($request->get('query'));

            if (empty($suggestions)) {
                return $app->json(['message' => 'No match found.'], 404);
            }
            return $app->json($suggestions);
        });

        $this->app->get('/movie', function(Application $app, Request $request) {
            $id   = $request->get('id');
            $file = $request->get('file');

            $config = new Config();
            $movieHandler = new MovieHandler($config);
            $result = $movieHandler->handleMovie($file, $id);
            if ($result === false) {
                return $app->json(['message' => 'failure'], 404);
            }

            return $app->json(['message' => 'success'], 200);
        });

        $this->app->put('/movie/info', function(Application $app, Request $request) {

            return $app->json(['message' => 'success'], 200);
        });

        $this->app->put('/movie/name', function(Application $app, Request $request) {

            return $app->json(['message' => 'success'], 200);
        });

        $this->app->put('/movie/poster', function(Application $app, Request $request) {

            return $app->json(['message' => 'success'], 200);
        });

        $this->app->put('/movie/imdb-screenshot', function(Application $app, Request $request) {

            return $app->json(['message' => 'success'], 200);
        });

        $this->app->put('/movie/dir', function(Application $app, Request $request) {

            return $app->json(['message' => 'success'], 200);
        });
    }

    public function run()
    {
        $this->app->run();
    }
}