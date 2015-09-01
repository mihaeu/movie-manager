<?php

namespace Mihaeu\MovieManager\Console;

use Mihaeu\MovieManager\Factory\MovieFactory;
use Mihaeu\MovieManager\IO\Filesystem;
use Mihaeu\MovieManager\IO\Ini;
use Mihaeu\MovieManager\Movie;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

/**
 * This command class is extended by all other commands with
 * movie related tasks.
 *
 * @package Mihaeu\MovieManager\Console
 *
 * @author  Michael Haeuslmann (haeuslmann@gmail.com)
 */
class Command extends BaseCommand
{
    const RETURN_CODE_OK            = 0;
    const RETURN_CODE_BAD_DIRECTORY = 1;
    const RETURN_CODE_NO_MATCHES    = 2;

    /**
     * @var array
     */
    protected $movies;

    /**
     * @var array
     */
    protected $options;

    /**
     * Configures arguments and options which are shared by all cli commands.
     */
    public function configure()
    {
        $this
            ->addArgument(
                'path',
                InputArgument::REQUIRED,
                'Path to your movie folder.'
            )
            ->addOption(
                'limit',
                null,
                InputOption::VALUE_REQUIRED,
                'Limit the number of movies.',
                -1
            )
            ->addOption(
                'year-from',
                null,
                InputOption::VALUE_REQUIRED,
                'List only movies from a certain year (e.g. -yf 2000 list movies between 2000-2014).'
            )
            ->addOption(
                'year-to',
                null,
                InputOption::VALUE_REQUIRED,
                'List only movies up to a certain year (e.g. -yt 2000 list movies between 1900-2000).'
            )
            ->addOption(
                'rating',
                null,
                InputOption::VALUE_REQUIRED,
                'List only movies with an IMDb rating equal or higher then this rating.'
            )
            ->addOption(
                'max-size',
                null,
                InputOption::VALUE_REQUIRED,
                'Stop listing movie after a certain total filesize in MB has been reached.'
            )
            ->addOption(
                'sort-by',
                null,
                InputOption::VALUE_REQUIRED,
                'Sort the result by the value provided.'
            )
            ->addOption(
                'desc',
                null,
                InputOption::VALUE_NONE,
                'Sort in descending order.'
            )
          ->addOption(
                'max-size-movie',
                null,
                InputOption::VALUE_REQUIRED,
                'List only movies smaller than x MB.'
          )
        ;
    }

    /**
     * Processes all movies in a directory and filters out unwanted movies.
     *
     * @param string $path
     * @param array  $options Command line options and arguments.
     *
     * @return array|Movie[]
     */
    public function getFilteredMovies($path, $options)
    {
        $this->options = $options;
        $this->movies = $this->findParsedMovies($path);

        if ($this->options['sort-by']) {
            $this->sortMovies($this->options['sort-by']);
        }

        if ($this->options['desc']) {
            $this->movies = array_reverse($this->movies, true);
        }

        if ($this->options['max-size-movie']) {
            $movies = [];
            foreach ($this->movies as $movieDirectory => $movie) {
                $movieSize = $this->getMovieSizeInMb($movieDirectory);
                if ($movieSize < $this->options['max-size-movie']) {
                    $movies[$movieDirectory] = $movie;
                }
            }
            $this->movies = $movies;
        }

        if ($this->options['max-size']) {
            $movies = [];
            $totalSize = 0;
            foreach ($this->movies as $movieDirectory => $movie) {
                $movieSize = $this->getMovieSizeInMb($movieDirectory);
                if ($totalSize + $movieSize <= $this->options['max-size']) {
                    $totalSize += $movieSize;
                    $movies[$movieDirectory] = $movie;
                }
            }
            $this->movies = $movies;
        }

        if ($this->options['limit'] && -1 !== $this->options['limit']) {
            $this->movies = array_slice($this->movies, 0, $this->options['limit']);
        }

        return $this->movies;
    }

    /**
     * Tests all filters for a movie and returns true only when all filters pass.
     *
     * @param Movie $movie
     *
     * @return bool
     */
    public function passesFilters(Movie $movie)
    {
        $yearFrom =
          // if the filter has not been set, then it passes
          !$this->options['year-from']
          // check the condition
          || $movie->getReleaseDate() >= $this->options['year-from'];

        $yearTo =
          !$this->options['year-to']
          || $movie->getReleaseDate() <= $this->options['year-to'];

        $rating =
          !$this->options['rating']
          && $movie->getImdbRating() >= $this->options['rating'];

        return $yearFrom && $yearTo && $rating;
    }

    /**
     * Calculate the total size of a movie folder.
     *
     * @param string $dir Movie Directory
     *
     * @return int
     */
    public function getMovieSizeInMb($dir)
    {
        if (!is_dir($dir)) {
            return 0;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        $totalSize = 0;
        foreach ($files as $file) {
            $totalSize += filesize($dir.DIRECTORY_SEPARATOR.$file) / 1024 / 1024;
        }
        return (int) $totalSize;
    }

    /**
     * Sorts movies using the key supplied in the options.
     *
     * @param string $sortBy
     */
    public function sortMovies($sortBy)
    {
        uasort($this->movies, function ($movieA, $movieB) use ($sortBy) {
            $getValue = 'get'.implode('', array_map('ucfirst', explode('_', $sortBy))).'()';
            if (!isset($movieA->$getValue) && !isset($movieB->$getValue)) {
              return 0;
            } elseif (isset($movieA->$getValue) && !isset($movieB->$getValue)) {
              return 1;
            } elseif (!isset($movieA->$getValue) && isset($movieB->$getValue)) {
              return -1;
            }

            if ($movieA->$getValue === $movieB->$getValue) {
              return 0;
            } elseif ($movieA->$getValue > $movieB->$getValue) {
              return 1;
            } else {
              return -1;
            }
        });
    }

    /**
     * Parses all properly formatted movies in the directory.
     *
     * @param string $path
     *
     * @return array|Movie[]
     */
    public function findParsedMovies($path)
    {
        $movies = [];
        $movieFolders = array_diff(scandir($path), ['.', '..']);

        $ini = new Ini(new Filesystem());
        $movieFactory = new MovieFactory(null, null, null, $ini);

        foreach ($movieFolders as $movieFolder) {
            $infoFile = $path."/$movieFolder/$movieFolder - IMDb.url";
            $movie = $movieFactory->createFromIni($infoFile);

            // don't process files which have not been parsed or which don't pass the filters
            if (!$movie || !$this->passesFilters($movie)) {
                continue;
            }

            $movies[$path.DIRECTORY_SEPARATOR.$movieFolder] = $movie;
        }
        return $movies;
    }
}
