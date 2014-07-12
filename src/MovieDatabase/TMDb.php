<?php

namespace Mihaeu\MovieManager\MovieDatabase;

use Mihaeu\MovieManager\Movie\Suggestion;

use Symfony\Component\DomCrawler\Crawler;
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

            // check filepath, because Image Helper returns url even if filepath is empty
            $url = '';
            $filepath = $result->getPosterImage()->getFilePath();
            if (!empty($filepath)) {
                $url = $imageHelper->getUrl($filepath, 'w342');
            }
            $suggestions[] = [
                'id'        => ((int) $result->getId()),
                'title'     => $result->getTitle(),
                'year'      => ((int) $result->getReleaseDate()->format('Y')),
                'poster'    => $url
            ];
        }
        return $suggestions;
    }

    /**
     * Retrieves the IMDb ID by crawling TMDb's site.
     *
     * This is a **HACK** and was only intended for one time use.
     * (Isn't it always?)
     *
     * TMDb search retrieves only a single result when searching
     * for an IMDb ID, so crawling the result is simple.
     *
     * @param  string $imdbId Should be a string, because of leading 0s
     *
     * @throws \Exception
     *
     * @return string
     */
    public static function getTmdbIdFromImdbId($imdbId)
    {
        $url = 'https://www.themoviedb.org/search?query=tt' . $imdbId;
        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $data = curl_exec($ch);
        curl_close($ch);

        $crawler = new Crawler($data);

        // this is highly volatile and was only used for a quick hack
        $xpath = '//*[@id="container"]/div[5]/div[1]/ul/li/div[2]/h3/a';

        $tmdbUrl = $crawler->filterXpath($xpath)->attr('href');
        $tmdbId = preg_replace('/^\/movie\/(\d+).*$/', '$1', $tmdbUrl);

        if (!is_numeric($tmdbId)) {
            throw new \Exception("TMDb ID \"$tmdbId\" extracted from \"$tmdbUrl\" is not valid" . PHP_EOL, 1);
        }

        return $tmdbId;
    }
}
