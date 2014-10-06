<?php

namespace Mihaeu\MovieManager\Tests\Factory;

use Mihaeu\MovieManager\Factory\MovieFactory;
use Mihaeu\MovieManager\Tests\MovieDatabase\IMDbTest;
use Mihaeu\MovieManager\Tests\MovieDatabase\TMDbTest;

class MovieFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreatesACompleteMovieFile()
    {
        $tmdb = \Mockery::mock('Mihaeu\MovieManager\MovieDatabase\TMDb');
        $tmdb->shouldReceive('getMovieFromTMDbId')->andReturn(['title' => TMDbTest::THE_GODFATHER_TMDB_TITLE]);
        $imdb = \Mockery::mock('Mihaeu\MovieManager\MovieDatabase\IMDb');
        $omdb = \Mockery::mock('Mihaeu\MovieManager\MovieDatabase\OMDb');
        $omdb->shouldReceive('getIMDbRating')->andReturn(IMDbTest::IMDB_RATING_THE_GODFATHER);

        $factory = new MovieFactory($tmdb, $imdb, $omdb);
        $movie = $factory->create(TMDbTest::THE_GODFATHER_TMDB_ID);

        $this->assertEquals(TMDbTest::THE_GODFATHER_TMDB_TITLE, $movie->getTitle());
        $this->assertEquals(IMDbTest::IMDB_RATING_THE_GODFATHER, $movie->getImdbRating());
    }
}
