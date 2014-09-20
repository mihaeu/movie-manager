<?php

namespace Mihaeu\MovieManager\Factory;

use \Mihaeu\MovieManager\Movie;
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
     * @param TMDb $tmdb
     * @param IMDb $imdb
     */
    public function __construct(TMDb $tmdb, IMDb $imdb)
    {
        $this->tmdb = $tmdb;
        $this->imdb = $imdb;
    }

    /**
     * @param  int $tmdbId
     *
     * @return Movie
     */
    public function create($tmdbId)
    {
        $movie = new Movie();

        // set TMDb data
        foreach ($this->tmdb->getMovieFromTmdbId($tmdbId) as $key => $value) {
            // determines the name of the setter method
            // e.g. productionCountries => setProductionCountries
            $setterName = 'set'.ucfirst($key);
            call_user_func([$movie, $setterName], $value);
        }

        // set IMDb data
        $imdbRating = $this->imdb->getRating($movie->getImdbId());
        $movie->setImdbRating($imdbRating);

        return $movie;
    }
}
