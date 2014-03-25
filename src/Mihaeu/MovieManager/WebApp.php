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
        $this->app->get('/', function () {
            echo 'hello world!';
        });
    }

    public function run()
    {
        $this->app->run();
    }
}