<?php

namespace Mihaeu\MovieManager\Tests\MovieDatabase;

use GuzzleHttp\Client;
use Mihaeu\MovieManager\MovieDatabase\IMDb;

class IMDbTest extends \PHPUnit_Framework_TestCase
{
    const IMDB_ID_THE_GODFATHER = 'tt0068646';
    const IMDB_RATING_THE_GODFATHER = 9.2;

    public function testFetchesRatingFromImdb()
    {
        $imdb = new IMDb(new Client());
        $this->assertEquals(self::IMDB_RATING_THE_GODFATHER, $imdb->getRating(self::IMDB_ID_THE_GODFATHER));
    }

    public function testDoesNotCrashWhenNoRatingAvailable()
    {
        $imdb = new IMDb(new Client());
        $this->assertFalse($imdb->getRating('tt123I/DontExist'));
    }
}
