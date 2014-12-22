<?php

namespace Mihaeu\MovieManager\Tests;

use Mihaeu\MovieManager\Builder\HtmlBuilder;
use Mihaeu\MovieManager\Factory\FileSetFactory;
use Mihaeu\MovieManager\Factory\MovieFactory;
use Mihaeu\MovieManager\IO\Filesystem;
use Mihaeu\MovieManager\IO\Ini;
use Mihaeu\MovieManager\MovieDatabase\IMDb;
use Mihaeu\MovieManager\MovieDatabase\OMDb;
use Mihaeu\MovieManager\MovieDatabase\TMDb;
use Mihaeu\MovieManager\MovieFinder;

class HtmlBuilderTest extends BaseTestCase
{
    public function testBuildHtmlCollection()
    {
        $ini = new Ini(new Filesystem());
        $movieFactory = new MovieFactory(null, null, null, $ini);
        $avatarIni = __DIR__.'/../../../demo/movies/Avatar (2009)/Avatar (2009) - IMDb.url';
        $godfatherIni = __DIR__.'/../../../demo/movies/The Godfather (1972)/The Godfather (1972) - IMDb.url';
        $movieFactory->createFromIni($godfatherIni);
        $movies = [
            $avatarIni      => $movieFactory->createFromIni($avatarIni),
            $godfatherIni   => $movieFactory->createFromIni($godfatherIni)
        ];

        $builder = new HtmlBuilder($movieFactory);
        $this->assertRegExp('/2 Movies from 2 countries in 4 languages/', $builder->build($movies));
    }
}
