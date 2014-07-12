<?php

namespace Mihaeu\MovieManager\Tests\MovieDatabase\TMDb;

use Mihaeu\MovieManager\Config;
use Mihaeu\MovieManager\Movie\Suggestion;
use Mihaeu\MovieManager\MovieDatabase\TMDb;

class TMDbTest extends \PHPUnit_Framework_TestCase
{
    public function testVeryPreciseQuery()
    {
        $config = new Config();
        $apiKey = $config->get('tmdb-api-key');

        $tmdb = new TMDb($apiKey);
        $suggestions = $tmdb->getMovieSuggestionsFromQuery('shawshank redemption');
        $resultIds = [];
        foreach ($suggestions as $suggestion) {
            /** @var Suggestion $suggestion */
            $resultIds[] = $suggestion->getTmdbId();
        }

        $shawshankRedemptionTMDbId = 278;
        $this->assertContains($shawshankRedemptionTMDbId, $resultIds);
    }
}
 