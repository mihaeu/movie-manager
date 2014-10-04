<?php

namespace Mihaeu\MovieManager\MovieDatabase;

use Guzzle\Http\Client;
use Symfony\Component\DomCrawler\Crawler;

class IMDb
{
    const IMDB_BASE_URL = 'http://www.imdb.com/title/';

    /**
     * Find the IMDb user rating for a movie.
     *
     * @param  string $imdbId
     *
     * @return float
     */
    public function getRating($imdbId)
    {
        $crawler = new Crawler();
        $crawler->addContent($this->downloadIMDbPage($imdbId));
        return floatval($crawler->filter('.star-box-giga-star')->text());
    }

    /**
     * Download (HTML) content from a URL using Guzzle.
     *
     * @param  string $imdbId
     *
     * @return String
     */
    private function downloadIMDbPage($imdbId)
    {
        $client = new Client();
        $body = '';
        try {
            $request = $client->get(self::IMDB_BASE_URL.$imdbId, []);
            $response = $request->send();
            $body = $response->getBody();
        } catch (\Exception $e) {
            // This is not fatal, because we're simply going
            // to return an empty result.
        }

        return (string) $body;
    }
}
