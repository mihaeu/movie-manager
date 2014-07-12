<?php

namespace Mihaeu\MovieManager\Tests\MovieDatabase\TMDb;

use Mihaeu\MovieManager\Config;
use Mihaeu\MovieManager\MovieDatabase\TMDb;

class TMDbTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TMDb
     */
    private $tmdb;

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

        $theGodfatherTMDbId = 238;
        $this->assertEquals($theGodfatherTMDbId, $suggestions[0]['id']);
    }

    public function testConvertsImdbToTmdbId()
    {
        $this->assertEquals($this->tmdb->getTmdbIdFromImdbId('tt0068646'), 238);
    }
}
