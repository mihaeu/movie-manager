<?php

namespace Mihaeu\MovieManager\Tests\Factory;

use Mihaeu\MovieManager\Config;
use Mihaeu\MovieManager\MovieDatabase\TMDb;
use Mihaeu\MovieManager\MovieDatabase\IMDb;
use Mihaeu\MovieManager\Factory\MovieFactory;
use Mihaeu\MovieManager\Tests\MovieDatabase\TMDbTest;

class MovieFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreatesACompleteMovieFile()
    {
        $config  = new Config();
        $tmdb    = new TMDb($config->get('tmdb-api-key'));
        $imdb    = new IMDb();

        $factory = new MovieFactory($tmdb, $imdb);
        $movie = $factory->create(TMDbTest::THE_GODFATHER_TMDB_ID);

        $this->assertEquals('The Godfather', $movie->getTitle());
        $this->assertEquals(9.2,             $movie->getImdbRating());
    }
}
