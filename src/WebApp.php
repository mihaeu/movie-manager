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

        $this->app->get('/',                        'Mihaeu\MovieManager\Controllers\MovieController::index');
        $this->app->get('/suggestions',             'Mihaeu\MovieManager\Controllers\MovieController::suggestions');
        $this->app->get('/movie',                   'Mihaeu\MovieManager\Controllers\MovieController::movie');
        $this->app->put('/movie/info',              'Mihaeu\MovieManager\Controllers\MovieController::a');
        $this->app->put('/movie/name',              'Mihaeu\MovieManager\Controllers\MovieController::b');
        $this->app->put('/movie/poster',            'Mihaeu\MovieManager\Controllers\MovieController::c');
        $this->app->put('/movie/imdb-screenshot',   'Mihaeu\MovieManager\Controllers\MovieController::d');
        $this->app->put('/movie/dir',               'Mihaeu\MovieManager\Controllers\MovieController::f');
    }

    public function run()
    {
        $this->app->run();
    }
}