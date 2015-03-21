<?php

namespace Mihaeu\MovieManager\Tests;

use Mihaeu\MovieManager\Builder\Html;
use Mihaeu\MovieManager\Factory\FileSetFactory;
use Mihaeu\MovieManager\Factory\MovieFactory;
use Mihaeu\MovieManager\IO\Filesystem;
use Mihaeu\MovieManager\IO\Ini;
use Mihaeu\MovieManager\MovieDatabase\IMDb;
use Mihaeu\MovieManager\MovieDatabase\OMDb;
use Mihaeu\MovieManager\MovieDatabase\TMDb;
use Mihaeu\MovieManager\MovieFinder;

class HtmlTest extends BaseTestCase
{
    public function testBuildHtmlCollection()
    {
        $builder = new Html(false);
        $movieRootPath = __DIR__.'/../../../demo/movies';
        $movieFactory = new MovieFactory(null, null, null, new Ini(new Filesystem()));
        $movies = [
            $movieFactory->createFromIni($movieRootPath.'/Avatar (2009)/Avatar (2009) - IMDb.url'),
            $movieFactory->createFromIni($movieRootPath.'/The Godfather (1972)/The Godfather (1972) - IMDb.url'),
        ];
        $this->assertRegExp('/2 Movies from 2 countries in 4 languages/', $builder->build($movies, $movieRootPath));
    }
}
