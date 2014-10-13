<?php

namespace Mihaeu\MovieManager\Factory;

use \Mihaeu\MovieManager\Movie;
use Mihaeu\MovieManager\MovieDatabase\OMDb;
use Mihaeu\MovieManager\MovieDatabase\TMDb;
use Mihaeu\MovieManager\MovieDatabase\IMDb;

/**
 * Movie Builder
 *
 * @author Michael Haeuslmann (haeuslmann@gmail.com)
 */
class MovieFactory
{
    /**
     * @var TMDb
     */
    private $tmdb;

    /**
     * @var IMDb
     */
    private $imdb;

    /**
     * @var OMDb
     */
    private $omdb;

    /**
     * @param TMDb $tmdb
     * @param IMDb $imdb
     * @param OMDb $omdb
     */
    public function __construct(TMDb $tmdb, IMDb $imdb, OMDb $omdb)
    {
        $this->tmdb = $tmdb;
        $this->imdb = $imdb;
        $this->omdb = $omdb;
    }

    /**
     * @param int $tmdbId
     *
     * @return Movie
     */
    public function create($tmdbId)
    {
        $movie = new Movie();

        // set TMDb data
        $tmdbMovie = $this->tmdb->getMovieFromTMDbId($tmdbId, true);
        foreach ($tmdbMovie as $key => $value) {
            // determines the name of the setter method
            // e.g. productionCountries => setProductionCountries
            $setterName = 'set'.ucfirst($key);
            call_user_func([$movie, $setterName], $value);
        }

        // set IMDb data
        $movie->setImdbRating($this->getIMDbRating($movie->getImdbId()));

        return $movie;
    }

    /**
     * Fetches the IMDb rating from OMDb using IMDb as a fallback option.
     *
     * @param string $imdbId
     *
     * @return float
     */
    private function getIMDbRating($imdbId)
    {
        $imdbRating = $this->omdb->getIMDbRating($imdbId);

        // fall back on IMDb
        if (false === $imdbRating) {
            $imdbRating = $this->imdb->getRating($imdbId);
        }

        // default rating 0.0
        if (false === $imdbRating) {
            $imdbRating = 0.0;
        }

        return $imdbRating;
    }
}
