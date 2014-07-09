<?php

namespace Mihaeu\MovieManager\MovieDatabase;

/**
 * Class TMDb
 *
 * @author Michael Haeuslmann (haeuslmann@gmail.com)
 */
class TMDb
{
    /**
     * Searches TMDb for movies matching a query string.
     *
     * @param  string $query
     *
     * @return array
     */
    public function searchByQuery($query)
    {
        $query = $this->tmdb->searchMovie($query, 1, true, null, 'en');

        $movies = [];
        foreach ($query['results'] as $movie) {
            $movies[$movie['id']] = $this->getMovieFromTMDbResult($movie);
        }

        return $movies;
    }
}