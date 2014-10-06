<?php

namespace Mihaeu\MovieManager\Tests\Factory;

use GuzzleHttp\Client;
use Mihaeu\MovieManager\Config;
use Mihaeu\MovieManager\MovieDatabase\OMDb;
use Mihaeu\MovieManager\MovieDatabase\TMDb;
use Mihaeu\MovieManager\MovieDatabase\IMDb;
use Mihaeu\MovieManager\Factory\MovieFactory;
use Mihaeu\MovieManager\Tests\MovieDatabase\IMDbTest;
use Mihaeu\MovieManager\Tests\MovieDatabase\TMDbTest;

class MovieFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreatesACompleteMovieFile()
    {
        $config  = new Config();
        $tmdb    = new TMDb($config->get('tmdb-api-key'));
        $client  = new Client();

        $factory = new MovieFactory($tmdb, new IMDb($client), new OMDb($client));
        $movie = $factory->create(TMDbTest::THE_GODFATHER_TMDB_ID);

        $this->assertEquals(TMDbTest::THE_GODFATHER_TMDB_TITLE, $movie->getTitle());
        $this->assertEquals(IMDbTest::IMDB_RATING_THE_GODFATHER, $movie->getImdbRating());
    }
}
