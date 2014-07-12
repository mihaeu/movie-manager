<?php

namespace Mihaeu\MovieManager\MovieDatabase;

use Mihaeu\MovieManager\Movie\Suggestion;

use Tmdb\ApiToken;
use Tmdb\Client;
use Tmdb\Model\Movie;
use Tmdb\Model\Search\SearchQuery\MovieSearchQuery;
use Tmdb\Repository\SearchRepository;
use Tmdb\Repository\ConfigurationRepository;
use Tmdb\Helper\ImageHelper;

/**
 * TMDb Wrapper
 *
 * Wraps wtfzdotnet's TMDb library, especially since every library has different return types.
 *
 * @author Michael Haeuslmann (haeuslmann@gmail.com)
 */
class TMDb
{
    /**
     * @var Client
     */
    private $client;

    /**
     * Constructor.
     *
     * @param string $apiKey
     */
    public function __construct($apiKey)
    {
        $token  = new ApiToken($apiKey);
        $this->client = new Client($token);
    }

    /**
     * Searches TMDb for movies matching a query string.
     *
     * @param  string $query
     *
     * @return array
     */
    public function getMovieSuggestionsFromQuery($query)
    {
        $repository = new SearchRepository($this->client);
        $parameters = new MovieSearchQuery();
        $results = $repository->searchMovie($query, $parameters);

        $configRepository = new ConfigurationRepository($this->client);
        $config = $configRepository->load();
        $imageHelper = new ImageHelper($config);

        $suggestions = [];
        foreach ($results as $result) {
            /** @var Movie $result */
            $suggestions[] = new Suggestion(
                ((int) $result->getId()),
                $result->getTitle(),
                ((int) $result->getReleaseDate()->format('Y')),
                $imageHelper->getUrl($result->getPosterImage()->getFilePath(), 'w342')
            );
        }
        return $suggestions;
    }
}