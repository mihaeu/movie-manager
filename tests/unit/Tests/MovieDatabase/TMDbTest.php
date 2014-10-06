<?php

namespace Mihaeu\MovieManager\Tests\MovieDatabase;

use Mihaeu\MovieManager\Builder\Movie;
use Mihaeu\MovieManager\Config;
use Mihaeu\MovieManager\MovieDatabase\TMDb;

class TMDbTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TMDb
     */
    private $tmdb;

    const THE_GODFATHER_TMDB_ID = 238;
    const THE_GODFATHER_TMDB_TITLE = 'The Godfather';

    public function setUp()
    {
        $config = new Config();
        $this->tmdb = new TMDb($config->get('tmdb-api-key'));
    }

    public function testVeryPreciseQuery()
    {
        $suggestions = $this->tmdb->getMovieSuggestionsFromQuery('shawshank redemption');
        $resultIds = [];
        foreach ($suggestions as $suggestion) {
            $resultIds[] = $suggestion['id'];
        }

        $shawshankRedemptionTMDbId = 278;
        $this->assertContains($shawshankRedemptionTMDbId, $resultIds);
    }

    /**
     * If we search for something like `the godfather` we should get
     * the classic "The Godfather" from 1972.
     */
    public function testListsMostProbableHitFirst()
    {
        $suggestions = $this->tmdb->getMovieSuggestionsFromQuery('the godfather');

        $this->assertEquals(self::THE_GODFATHER_TMDB_ID, $suggestions[0]['id']);
    }

    public function testConvertsImdbToTmdbId()
    {
        $this->assertEquals(self::THE_GODFATHER_TMDB_ID, $this->tmdb->getTmdbIdFromImdbId('tt0068646'));
    }

    public function testThrowsExceptionOnBadId()
    {
        $this->setExpectedException('\Exception');
        $this->tmdb->getTmdbIdFromImdbId('badId');
    }

    public function testFindsTmdbInfo()
    {
        $movie = $this->tmdb->getMovieFromTmdbId(self::THE_GODFATHER_TMDB_ID);
        $this->assertEquals(1972, $movie['year']);
    }
}
