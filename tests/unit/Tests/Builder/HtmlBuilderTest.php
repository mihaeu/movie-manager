<?php

namespace Mihaeu\MovieManager\Tests;

use Mihaeu\MovieManager\Builder\HtmlBuilder;
use Mihaeu\MovieManager\Factory\MovieFactory;
use Mihaeu\MovieManager\IO\Filesystem;
use Mihaeu\MovieManager\IO\Ini;

class HtmlBuilderTest extends BaseTestCase
{
    public function testBuildHtmlCollection()
    {
        $ini = new Ini(new Filesystem());
        $movieFactory = new MovieFactory(null, null, null, $ini);
        $basePath = __DIR__.'/../../../demo/movies';
        $movies = [
            $movieFactory->createFromIni($basePath.'/Avatar (2009)/Avatar (2009) - IMDb.url'),
            $movieFactory->createFromIni($basePath.'/The Godfather (1972)/The Godfather (1972) - IMDb.url')
        ];

        $builder = new HtmlBuilder(false);
        $this->assertRegExp('/2 Movies from 2 countries in 4 languages/', $builder->build($movies, $basePath));
    }
}
