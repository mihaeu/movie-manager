<?php

namespace Mihaeu\MovieManager\Tests\Factory;

use Mihaeu\MovieManager\Factory\MovieFactory;
use Mihaeu\MovieManager\IO\Filesystem;
use Mihaeu\MovieManager\IO\Ini;
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
        $ini = \Mockery::mock('Mihaeu\MovieManager\IO\Ini');

        $factory = new MovieFactory($tmdb, $imdb, $omdb, $ini);
        $movie = $factory->create(TMDbTest::THE_GODFATHER_TMDB_ID);

        $this->assertEquals(TMDbTest::THE_GODFATHER_TMDB_TITLE, $movie->getTitle());
        $this->assertEquals(IMDbTest::IMDB_RATING_THE_GODFATHER, $movie->getImdbRating());
    }

    public function testCreatesMovieFromIni()
    {
        $tmdb = \Mockery::mock('Mihaeu\MovieManager\MovieDatabase\TMDb');
        $imdb = \Mockery::mock('Mihaeu\MovieManager\MovieDatabase\IMDb');
        $omdb = \Mockery::mock('Mihaeu\MovieManager\MovieDatabase\OMDb');

        $ini = new Ini(new Filesystem());
        $factory = new MovieFactory($tmdb, $imdb, $omdb, $ini);
        $movie = $factory->createFromIni(__DIR__.'/../../../demo/movies/Avatar (2009)/Avatar (2009) - IMDb.url');
        $this->assertEquals('Avatar', $movie->getTitle());
        $this->assertEquals(2009, $movie->getYear());
        $this->assertEquals('tt0499549', $movie->getImdbId());
    }
}
