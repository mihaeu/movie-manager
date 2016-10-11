<?php declare(strict_types = 1);

namespace unit\Tests\MovieDatabase;

use Mihaeu\MovieManager\MovieDatabase\OMDb;
use Mihaeu\MovieManager\Tests\BaseTestCase;
use Mihaeu\MovieManager\Tests\MovieDatabase\IMDbTest;

class OMDbTest extends BaseTestCase
{
    public function testFetchesRatingFromImdb()
    {
        $response = \Mockery::mock('GuzzleHttp\Message\ResponseInterface');
        $omdbTheGodfatherJson = json_decode(file_get_contents(__DIR__.'/../../../demo/omdbapi.com/?i=tt0068646'), true);
        $response->shouldReceive('json')->andReturn($omdbTheGodfatherJson);

        $client = \Mockery::mock('GuzzleHttp\Client');
        $client->shouldReceive('get')->andReturn($response);

        $omdb = new OMDb($client);
        $this->assertEquals(IMDbTest::IMDB_RATING_THE_GODFATHER, $omdb->getIMDbRating(IMDbTest::IMDB_ID_THE_GODFATHER));
    }

    public function testDoesNotCrashWhenNoRatingAvailable()
    {
        $client = \Mockery::mock('GuzzleHttp\Client');
        $client->shouldReceive('get')->andThrow('\Exception');

        $omdb = new OMDb($client);
        $this->assertFalse($omdb->getIMDbRating('tt123/IDontExist'));
    }
}
