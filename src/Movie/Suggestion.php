<?php

namespace Mihaeu\MovieManager\Movie;

/**
 * Class Suggestion
 *
 * @author Michael Haeuslmann (haeuslmann@gmail.com)
 */
class Suggestion
{
    /**
     * @var int
     */
    private $tmdbId;

    /**
     * @var string
     */
    private $title;

    /**
     * @var int
     */
    private $year;

    /**
     * @var string
     */
    private $poster;

    /**
     * @param int    $tmdbId
     * @param string $poster
     * @param string $title
     * @param int    $year
     */
    function __construct($tmdbId, $title, $year, $poster)
    {
        $this->tmdbId = $tmdbId;
        $this->title  = $title;
        $this->year   = $year;
        $this->poster = $poster;
    }

    /**
     * @return string
     */
    public function getPoster()
    {
        return $this->poster;
    }

    /**
     * @param string $poster
     */
    public function setPoster($poster)
    {
        $this->poster = $poster;
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
     * @return int
     */
    public function getTmdbId()
    {
        return $this->tmdbId;
    }

    /**
     * @param int $tmdbId
     */
    public function setTmdbId($tmdbId)
    {
        $this->tmdbId = $tmdbId;
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
}
