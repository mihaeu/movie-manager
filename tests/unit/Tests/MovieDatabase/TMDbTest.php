<?php declare(strict_types = 1);

namespace Mihaeu\MovieManager\Tests\MovieDatabase;

use Guzzle\Http\ClientInterface;
use Mihaeu\MovieManager\Builder\Movie;
use Mihaeu\MovieManager\Config;
use Mihaeu\MovieManager\MovieDatabase\TMDb;

class TMDbTest extends \PHPUnit_Framework_TestCase
{
    const THE_GODFATHER_TMDB_ID = 238;
    const THE_GODFATHER_TMDB_TITLE = 'The Godfather';

    public function testListsSuggestionsForAPreciseQuery()
    {
        $tmdbTheGodfatherSearch = __DIR__.'/../../../demo/api.themoviedb.org/3/search/movie?query=the%2Bgodfather';
        $tmdb = new TMDb('tmdb-api-key', $this->getMockedGuzzleV3ClientForJson($tmdbTheGodfatherSearch));
        $suggestions = $tmdb->getMovieSuggestionsFromQuery('the godfather');
        $resultIds = [];
        foreach ($suggestions as $suggestion) {
            $resultIds[] = $suggestion['id'];
        }
        $this->assertContains(self::THE_GODFATHER_TMDB_ID, $resultIds);
    }

    public function testFindsTmdbInfo()
    {
        $tmdbTheGodfatherMovie = __DIR__.'/../../../demo/api.themoviedb.org/3/movie/238';
        $tmdb = new TMDb('tmdb-api-key', $this->getMockedGuzzleV3ClientForJson($tmdbTheGodfatherMovie));
        $movie = $tmdb->getMovieFromTMDbId(self::THE_GODFATHER_TMDB_ID);
        $this->assertEquals(1972, $movie['year']);
    }

    /**
     * @param string $cachedJsonResponse Path to a file that contains the expected JSON response
     *
     * @return ClientInterface
     */
    protected function getMockedGuzzleV3ClientForJson($cachedJsonResponse)
    {
        $jsonResponseAssocArray = json_decode(file_get_contents($cachedJsonResponse),true);

        // TMDb API uses Guzzle v3
        $response = \Mockery::mock('Guzzle\Http\Message\Response');
        $response->shouldReceive('json')->andReturn($jsonResponseAssocArray);

        $request = \Mockery::mock('Guzzle\Http\Message\Request');
        $request->shouldReceive('send')->andReturn($response);

        $client = \Mockery::mock('Guzzle\Http\ClientInterface');
        $client->shouldReceive('get')->andReturn($request);
        $client->shouldReceive('addSubscriber');

        return $client;
    }
}
