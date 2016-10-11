<?php declare(strict_types = 1);

namespace Mihaeu\MovieManager\Tests\MovieDatabase;

use Mihaeu\MovieManager\MovieDatabase\IMDb;

class IMDbTest extends \PHPUnit_Framework_TestCase
{
    const IMDB_ID_THE_GODFATHER = 'tt0068646';
    const IMDB_RATING_THE_GODFATHER = 9.2;

    public function testFetchesRatingFromImdb()
    {
        $response = \Mockery::mock('GuzzleHttp\Message\ResponseInterface');
        $imdbTheGodfatherHtml = gzdecode(file_get_contents(__DIR__.'/../../../demo/imdb.com/title/tt0068646.gz'));
        $response->shouldReceive('getBody')->andReturn($imdbTheGodfatherHtml);

        $client = \Mockery::mock('GuzzleHttp\Client');
        $client->shouldReceive('get')->andReturn($response);

        $imdb = new IMDb($client);
        $this->assertEquals(self::IMDB_RATING_THE_GODFATHER, $imdb->getRating(self::IMDB_ID_THE_GODFATHER));
    }

    public function testDoesNotCrashWhenNoRatingAvailable()
    {
        $client = \Mockery::mock('GuzzleHttp\Client');
        $client->shouldReceive('get')->andThrow('\Exception');

        $imdb = new IMDb($client);
        $this->assertFalse($imdb->getRating('tt123I/DontExist'));
    }

    protected function tearDown()
    {
        \Mockery::close();
    }

}
