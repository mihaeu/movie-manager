<?php

namespace Mihaeu\MovieManager\MovieDatabase;

use GuzzleHttp\ClientInterface;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class IMDb
 *
 * Crawl IMDb for the IMDb rating (not available on TMDb).
 *
 * @author Michael Haeuslmann (haeuslmann@gmail.com)
 */
class IMDb
{
    const IMDB_BASE_URL = 'http://www.imdb.com/title/';

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @param ClientInterface $client
     */
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * Find the IMDb user rating for a movie.
     *
     * @param  string $imdbId
     *
     * @return float|bool
     */
    public function getRating($imdbId)
    {
        try {
            $response = $this->client->get(self::IMDB_BASE_URL.$imdbId);
            $content = $response->getBody();
        } catch (\Exception $e) {
            return false;
        }

        $crawler = new Crawler();
        $crawler->addContent($content);
        $ratingCrawler = $crawler->filter('.star-box-giga-star');
        $rating = $ratingCrawler->text();
        return empty($rating) ? false : floatval($rating);
    }
}
