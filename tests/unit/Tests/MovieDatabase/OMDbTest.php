<?php

namespace unit\Tests\MovieDatabase;

use GuzzleHttp\Client;
use Mihaeu\MovieManager\MovieDatabase\OMDb;
use Mihaeu\MovieManager\Tests\BaseTestCase;
use Mihaeu\MovieManager\Tests\MovieDatabase\IMDbTest;

class OMDbTest extends BaseTestCase
{
    public function testFetchesRatingFromImdb()
    {
        $omdb = new OMDb(new Client());
        $this->assertEquals(IMDbTest::IMDB_RATING_THE_GODFATHER, $omdb->getIMDbRating(IMDbTest::IMDB_ID_THE_GODFATHER));
    }

    public function testDoesNotCrashWhenNoRatingAvailable()
    {
        $omdb = new OMDb(new Client());
        $this->assertFalse($omdb->getIMDbRating('tt123/IDontExist'));
    }
}
