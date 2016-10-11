<?php declare(strict_types = 1);

namespace Mihaeu\MovieManager\Factory;

use Mihaeu\MovieManager\IO\Ini;
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
     * @var Ini
     */
    private $ini;

    /**
     * @param TMDb $tmdb
     * @param IMDb $imdb
     * @param OMDb $omdb
     * @param Ini  $ini
     */
    public function __construct(TMDb $tmdb = null, IMDb $imdb = null, OMDb $omdb = null, Ini $ini = null)
    {
        $this->tmdb = $tmdb;
        $this->imdb = $imdb;
        $this->omdb = $omdb;
        $this->ini  = $ini;
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
     * @param string $iniFilename
     *
     * @return Movie|null
     */
    public function createFromIni($iniFilename)
    {
        $iniFilename = realpath($iniFilename);
        if (!$iniFilename) {
            return null;
        }

        $data = $this->ini->read($iniFilename);
        $movie = new Movie();
        foreach ($data as $key => $value) {
            if ('info' === $key) {
                foreach ($value as $infoKey => $infoValue) {
                    $setterName = 'set'.$this->convertToCamelcase($infoKey, true);
                    if (method_exists($movie, $setterName)) {
                        call_user_func([$movie, $setterName], $infoValue);
                    }
                }
            }

            $setterName = 'set'.$this->convertToCamelcase($key, true);
            if (method_exists($movie, $setterName)) {
                call_user_func([$movie, $setterName], $value);
            }
        }

        if ($movie->getYear() === null) {
            $movie->setYear(substr($movie->getReleaseDate(), 0, 4));
        }

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

    /**
     * Translates a string with underscores
     * into camel case (e.g. first_name -> firstName)
     *
     * @param string $str String in underscore format
     * @param bool $capitalise_first_char If true, capitalise the first char in $str
     *
     * @return string $str translated into camel caps
     */
    private function convertToCamelcase($str, $capitalise_first_char = false)
    {
        if ($capitalise_first_char) {
            $str = strtoupper(substr($str, 0, 1)).substr($str, 1);
        }
        $func = create_function('$c', 'return strtoupper($c[1]);');
        return preg_replace_callback('/_([a-z])/', $func, $str);
    }
}
