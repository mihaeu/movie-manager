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
        $ini = new Ini(new Filesystem());
        $movieFactory = new MovieFactory(null, null, null, $ini);

        $builder = new Html($movieFactory);
        $movieFinder = new MovieFinder(new FileSetFactory(__DIR__.'/../../../demo/movies'), ['mkv', 'avi']);
        $fileSets = $movieFinder->findMoviesInDir();
        $this->assertRegExp('/2 Movies from 2 countries in 4 languages/', $builder->build($fileSets));
    }
}
