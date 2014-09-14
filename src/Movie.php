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
     * @return boolean
     */
    public function getAdult()
    {
        return $this->adult;
    }

    /**
     * @param boolean $adult
     */
    public function setAdult($adult)
    {
        $this->adult = $adult;
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
     */
    public function setBackdropUrl($backdropUrl)
    {
        $this->backdropUrl = $backdropUrl;
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
     */
    public function setBudget($budget)
    {
        $this->budget = $budget;
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
     */
    public function setGenres($genres)
    {
        $this->genres = $genres;
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
     */
    public function setHomepage($homepage)
    {
        $this->homepage = $homepage;
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
     */
    public function setId($id)
    {
        $this->id = $id;
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
     */
    public function setImdbId($imdbId)
    {
        $this->imdbId = $imdbId;
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
     */
    public function setImdbRating($imdbRating)
    {
        $this->imdbRating = $imdbRating;
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
     */
    public function setOriginalTitle($originalTitle)
    {
        $this->originalTitle = $originalTitle;
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
     */
    public function setOverview($overview)
    {
        $this->overview = $overview;
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
     */
    public function setPopularity($popularity)
    {
        $this->popularity = $popularity;
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
     */
    public function setPosterUrl($posterUrl)
    {
        $this->posterUrl = $posterUrl;
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
     */
    public function setProductionCompanies($productionCompanies)
    {
        $this->productionCompanies = $productionCompanies;
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
     */
    public function setProductionCountries($productionCountries)
    {
        $this->productionCountries = $productionCountries;
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
     */
    public function setReleaseDate($releaseDate)
    {
        $this->releaseDate = $releaseDate;
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
     */
    public function setRevenue($revenue)
    {
        $this->revenue = $revenue;
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
     */
    public function setRuntime($runtime)
    {
        $this->runtime = $runtime;
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
     */
    public function setSpokenLanguages($spokenLanguages)
    {
        $this->spokenLanguages = $spokenLanguages;
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
     */
    public function setStatus($status)
    {
        $this->status = $status;
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
     */
    public function setTagline($tagline)
    {
        $this->tagline = $tagline;
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
     */
    public function setTitle($title)
    {
        $this->title = $title;
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
     */
    public function setVoteAverage($voteAverage)
    {
        $this->voteAverage = $voteAverage;
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
     */
    public function setVoteCount($voteCount)
    {
        $this->voteCount = $voteCount;
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
     */
    public function setYear($year)
    {
        $this->year = $year;
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
}
