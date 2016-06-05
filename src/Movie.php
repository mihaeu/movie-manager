<?php

namespace Mihaeu\MovieManager;

/**
 * Class Movie
 *
 * @author Michael Haeuslmann (haeuslmann@gmail.com)
 */
class Movie
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $imdbId;

    /**
     * @var bool
     */
    private $adult;

    /**
     * @var string
     */
    private $posterUrl;

    /**
     * @var string
     */
    private $backdropUrl;

    /**
     * @var int
     */
    private $budget;

    /**
     * @var string
     */
    private $homepage;

    /**
     * @var string
     */
    private $originalTitle;

    /**
     * @var string
     */
    private $overview;

    /**
     * @var float
     */
    private $popularity;

    /**
     * @var string
     */
    private $releaseDate;

    /**
     * @var int
     */
    private $year;

    /**
     * @var int
     */
    private $revenue;

    /**
     * @var int
     */
    private $runtime;

    /**
     * @var string
     */
    private $status;

    /**
     * @var string
     */
    private $tagline;

    /**
     * @var string
     */
    private $title;

    /**
     * @var float
     */
    private $voteAverage;

    /**
     * @var int
     */
    private $voteCount;

    /**
     * @var array
     */
    private $genres;

    /**
     * @var array
     */
    private $productionCompanies;

    /**
     * @var array
     */
    private $productionCountries;

    /**
     * @var array
     */
    private $spokenLanguages;

    /**
     * @var float
     */
    private $imdbRating;

    /**
     * @var array
     */
    private $cast;

    /**
     * @var array
     */
    private $character;

    /**
     * @var array
     */
    private $directors;

    /**
     * @var string
     */
    private $trailer;

    /**
     * @return boolean
     */
    public function getAdult()
    {
        return $this->adult;
    }

    /**
     * @param boolean $adult
     * @return Movie
     */
    public function setAdult($adult) : Movie
    {
        $this->adult = $adult;
        return $this;
    }

    /**
     * @return string
     */
    public function getBackdropUrl()
    {
        return $this->backdropUrl;
    }

    /**
     * @param string $backdropUrl
     * @return Movie
     */
    public function setBackdropUrl($backdropUrl) : Movie
    {
        $this->backdropUrl = $backdropUrl;
        return $this;
    }

    /**
     * @return int
     */
    public function getBudget()
    {
        return $this->budget;
    }

    /**
     * @param int $budget
     * @return Movie
     */
    public function setBudget($budget) : Movie
    {
        $this->budget = $budget;
        return $this;
    }

    /**
     * @return array
     */
    public function getGenres()
    {
        return $this->genres;
    }

    /**
     * @param array $genres
     * @return Movie
     */
    public function setGenres($genres) : Movie
    {
        $this->genres = $genres;
        return $this;
    }

    /**
     * @return string
     */
    public function getHomepage()
    {
        return $this->homepage;
    }

    /**
     * @param string $homepage
     * @return Movie
     */
    public function setHomepage($homepage) : Movie
    {
        $this->homepage = $homepage;
        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Movie
     */
    public function setId($id) : Movie
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getImdbId()
    {
        return $this->imdbId;
    }

    /**
     * @param string $imdbId
     * @return Movie
     */
    public function setImdbId($imdbId) : Movie
    {
        $this->imdbId = $imdbId;
        return $this;
    }

    /**
     * @return float
     */
    public function getImdbRating()
    {
        return $this->imdbRating;
    }

    /**
     * @param float $imdbRating
     * @return Movie
     */
    public function setImdbRating($imdbRating) : Movie
    {
        $this->imdbRating = $imdbRating;
        return $this;
    }

    /**
     * @return string
     */
    public function getOriginalTitle()
    {
        return $this->originalTitle;
    }

    /**
     * @param string $originalTitle
     * @return Movie
     */
    public function setOriginalTitle($originalTitle) : Movie
    {
        $this->originalTitle = $originalTitle;
        return $this;
    }

    /**
     * @return string
     */
    public function getOverview()
    {
        return $this->overview;
    }

    /**
     * @param string $overview
     * @return Movie
     */
    public function setOverview($overview) : Movie
    {
        $this->overview = $overview;
        return $this;
    }

    /**
     * @return float
     */
    public function getPopularity()
    {
        return $this->popularity;
    }

    /**
     * @param float $popularity
     * @return Movie
     */
    public function setPopularity($popularity) : Movie
    {
        $this->popularity = $popularity;
        return $this;
    }

    /**
     * @return string
     */
    public function getPosterUrl()
    {
        return $this->posterUrl;
    }

    /**
     * @param string $posterUrl
     * @return Movie
     */
    public function setPosterUrl($posterUrl) : Movie
    {
        $this->posterUrl = $posterUrl;
        return $this;
    }

    /**
     * @return array
     */
    public function getProductionCompanies()
    {
        return $this->productionCompanies;
    }

    /**
     * @param array $productionCompanies
     * @return Movie
     */
    public function setProductionCompanies($productionCompanies) : Movie
    {
        $this->productionCompanies = $productionCompanies;
        return $this;
    }

    /**
     * @return array
     */
    public function getProductionCountries()
    {
        return $this->productionCountries;
    }

    /**
     * @param array $productionCountries
     * @return Movie
     */
    public function setProductionCountries($productionCountries) : Movie
    {
        $this->productionCountries = $productionCountries;
        return $this;
    }

    /**
     * @return string
     */
    public function getReleaseDate()
    {
        return $this->releaseDate;
    }

    /**
     * @param string $releaseDate
     * @return Movie
     */
    public function setReleaseDate($releaseDate) : Movie
    {
        $this->releaseDate = $releaseDate;
        return $this;
    }

    /**
     * @return int
     */
    public function getRevenue()
    {
        return $this->revenue;
    }

    /**
     * @param int $revenue
     * @return Movie
     */
    public function setRevenue($revenue) : Movie
    {
        $this->revenue = $revenue;
        return $this;
    }

    /**
     * @return int
     */
    public function getRuntime()
    {
        return $this->runtime;
    }

    /**
     * @param int $runtime
     * @return Movie
     */
    public function setRuntime($runtime) : Movie
    {
        $this->runtime = $runtime;
        return $this;
    }

    /**
     * @return array
     */
    public function getSpokenLanguages()
    {
        return $this->spokenLanguages;
    }

    /**
     * @param array $spokenLanguages
     * @return Movie
     */
    public function setSpokenLanguages($spokenLanguages) : Movie
    {
        $this->spokenLanguages = $spokenLanguages;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return Movie
     */
    public function setStatus($status) : Movie
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return string
     */
    public function getTagline()
    {
        return $this->tagline;
    }

    /**
     * @param string $tagline
     * @return Movie
     */
    public function setTagline($tagline) : Movie
    {
        $this->tagline = $tagline;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return Movie
     */
    public function setTitle($title) : Movie
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return float
     */
    public function getVoteAverage()
    {
        return $this->voteAverage;
    }

    /**
     * @param float $voteAverage
     * @return Movie
     */
    public function setVoteAverage($voteAverage) : Movie
    {
        $this->voteAverage = $voteAverage;
        return $this;
    }

    /**
     * @return int
     */
    public function getVoteCount()
    {
        return $this->voteCount;
    }

    /**
     * @param int $voteCount
     * @return Movie
     */
    public function setVoteCount($voteCount) : Movie
    {
        $this->voteCount = $voteCount;
        return $this;
    }

    /**
     * @return int
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * @param int $year
     * @return Movie
     */
    public function setYear($year) : Movie
    {
        $this->year = $year;
        return $this;
    }

    /**
     * @return array
     */
    public function getCast()
    {
        return $this->cast;
    }

    /**
     * @param array $cast
     * @return Movie
     */
    public function setCast($cast) : Movie
    {
        $this->cast = $cast;
        return $this;
    }

    /**
     * @return array
     */
    public function getCharacter()
    {
        return $this->character;
    }

    /**
     * @param array $character
     * @return Movie
     */
    public function setCharacter($character) : Movie
    {
        $this->character = $character;
        return $this;
    }

    /**
     * @return array
     */
    public function getDirectors()
    {
        return $this->directors;
    }

    /**
     * @param array $directors
     * @return Movie
     */
    public function setDirectors($directors) : Movie
    {
        $this->directors = $directors;
        return $this;
    }

    /**
     * @return string
     */
    public function getTrailer()
    {
        return $this->trailer;
    }

    /**
     * @param string $trailer
     * @return Movie
     */
    public function setTrailer($trailer) : Movie
    {
        $this->trailer = $trailer;
        return $this;
    }

    /**
     * Transforms this movie into array form.
     *
     * @return array
     */
    public function toArray()
    {
        $class = new \ReflectionClass(__CLASS__);
        $properties = $class->getProperties();
        $result = [];
        foreach ($properties as $property) {
            /** @var \ReflectionProperty */
            $methodName = 'get'.ucfirst($property->name);
            $result[$property->name] = $this->$methodName();
        }
        return $result;
    }

    /**
     * Prints a readable representation of the Movie.
     */
    public function __toString()
    {
        return $this->getFilesystemCompatibleTitle().' ('.$this->getYear().')';
    }

    /**
     * Movies occasionally contain characters which aren't allowed in filenames.
     * This method prints a title with only valid characters.
     *
     * @return string
     */
    public function getFilesystemCompatibleTitle()
    {
        // : is not allowed in most OS, replace with - and add spaces
        $movieTitle = str_replace(':', ' - ', $this->getTitle());

        // replace other illegal characters with spaces
        $movieTitle = str_replace(['/', '*', '?', '"', '\\', '<', '>', '|'], ' ', $movieTitle);

        // trim spaces to one space max
        $movieTitle = preg_replace('/  +/', ' ', $movieTitle);

        return $movieTitle;
    }
}
