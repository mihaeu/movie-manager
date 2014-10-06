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

    public function testListsSuggestionsForAPreciseQuery()
    {
        $suggestions = $this->tmdb->getMovieSuggestionsFromQuery('the godfather');
        $resultIds = [];
        foreach ($suggestions as $suggestion) {
            $resultIds[] = $suggestion['id'];
        }

        $shawshankRedemptionTMDbId = self::THE_GODFATHER_TMDB_ID;
        $this->assertContains($shawshankRedemptionTMDbId, $resultIds);
    }

    public function testFindsTmdbInfo()
    {
        $movie = $this->tmdb->getMovieFromTmdbId(self::THE_GODFATHER_TMDB_ID);
        $this->assertEquals(1972, $movie['year']);
    }
}
