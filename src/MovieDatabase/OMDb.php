<?php

namespace Mihaeu\MovieManager\MovieDatabase;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Message\ResponseInterface;

/**
 * Class OMDb
 *
 * Uses the super simple OMDb API from http://www.omdbapi.com
 *
 * @author Michael Haeuslmann (haeuslmann@gmail.com)
 */
class OMDb
{
    const OMDB_API_BASE_URL = 'http://www.omdbapi.com/?i=';

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
     * @param string $imdbId
     *
     * @return float|bool
     */
    public function getIMDbRating($imdbId)
    {
        try {
            $response = $this->client->get(self::OMDB_API_BASE_URL.$imdbId);
            $jsonData = $response->json();
        } catch (\Exception $e) {
            return false;
        }

        return isset($jsonData['imdbRating']) ? floatval($jsonData['imdbRating']) : false;
    }
}
