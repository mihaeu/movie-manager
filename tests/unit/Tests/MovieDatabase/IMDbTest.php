<?php

namespace Mihaeu\MovieManager\Tests\MovieDatabase;

use Mihaeu\MovieManager\MovieDatabase\IMDb;

class IMDbTest extends \PHPUnit_Framework_TestCase
{
    public function testFetchesRatingFromImdb()
    {
        $imdb = new IMDb();
        $imdbIdOfTheGodfather = 'tt0068646';
        $this->assertEquals(9.2, $imdb->getRating($imdbIdOfTheGodfather));
    }
}
