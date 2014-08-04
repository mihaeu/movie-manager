<?php

namespace Mihaeu\MovieManager;

use Mihaeu\MovieManager\MovieDatabase\TMDb;

use Silex\Application;
use Silex\Provider\TwigServiceProvider;
use Whoops\Provider\Silex\WhoopsServiceProvider;
use Symfony\Component\HttpFoundation\Request;

/**
 * Silex based WebApp
 *
 * @author Michael Haeuslmann (haeuslmann@gmail.com)
 */
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

        $this->app->get('/',                        'Mihaeu\MovieManager\Controller\MovieController::index');
        $this->app->get('/movies',                  'Mihaeu\MovieManager\Controller\MovieController::movies');
        $this->app->get('/suggestions',             'Mihaeu\MovieManager\Controller\MovieController::suggestions');
        $this->app->get('/movie',                   'Mihaeu\MovieManager\Controller\MovieController::movie');

        // not active yet
        $this->app->put('/movie/info',              'Mihaeu\MovieManager\Controller\MovieController::a');
        $this->app->put('/movie/name',              'Mihaeu\MovieManager\Controller\MovieController::b');
        $this->app->put('/movie/poster',            'Mihaeu\MovieManager\Controller\MovieController::c');
        $this->app->put('/movie/imdb-screenshot',   'Mihaeu\MovieManager\Controller\MovieController::d');
        $this->app->put('/movie/dir',               'Mihaeu\MovieManager\Controller\MovieController::f');
    }

    public function run()
    {
        $this->app->run();
    }
}
