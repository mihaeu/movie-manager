<?php

namespace Mihaeu\MovieManager\Console;

use Mihaeu\MovieManager\FileSet;
use Mihaeu\MovieManager\IO\Filesystem;
use Mihaeu\MovieManager\IO\Ini;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class Command extends BaseCommand
{
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
     * @return array|FileSet[]
     */
    public function getFilteredMovies($path, $options)
    {
        $this->options = $options;
        $this->movies = $this->parseMovies();

        if ($this->options['sort-by']) {
            $this->sortMovies();
        }

        $matchedMovies = array_keys($this->movies);
        if ($this->options['desc']) {
            $matchedMovies = array_reverse($matchedMovies);
        }

        if ($this->options['max-size-movie']) {
            $movies = [];
            foreach ($matchedMovies as $movieDir) {
                $movieSize = $this->getMovieSizeInMb($movieDir);
                if ($movieSize < $this->options['max-size-movie']) {
                    $movies[] = $movieDir;
                }
            }
            $matchedMovies = $movies;
        }

        if ($this->options['max-size']) {
            $movies = [];
            $totalSize = 0;
            foreach ($matchedMovies as $movieDir) {
                $movieSize = $this->getMovieSizeInMb($movieDir);
                if ($totalSize + $movieSize <= $this->options['max-size']) {
                    $totalSize += $movieSize;
                    $movies[] = $movieDir;
                }
            }
            $matchedMovies = $movies;
        }

        return $matchedMovies;
    }

    /**
     * Tests all filters for a movie and returns true only when all filters pass.
     *
     * @param array $movieInfo
     *
     * @return bool
     */
    public function passesFilters($movieInfo)
    {
        $yearFrom =
          // if the filter has not been set, then it passes
          !$this->options['year-from']
          // if the information does not exist, we cannot filter it
          || isset($movieInfo['release_date'])
          // check the condition
          && $movieInfo['release_date'] >= $this->options['year-from'];

        $yearTo =
          !$this->options['year-to']
          || isset($movieInfo['release_date'])
          && $movieInfo['release_date'] <= $this->options['year-to'];

        $rating =
          !$this->options['rating']
          || isset($movieInfo['imdb_rating'])
          && $movieInfo['imdb_rating'] >= $this->options['rating'];

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
     */
    public function sortMovies()
    {
        $sortBy = $this->options['sort-by'];
        uasort($this->movies, function ($arrayA, $arrayB) use ($sortBy) {
              if (!isset($arrayA['info'][$sortBy]) && !isset($arrayB['info'][$sortBy])) {
                  return 0;
              } elseif (isset($arrayA['info'][$sortBy]) && !isset($arrayB['info'][$sortBy])) {
                  return 1;
              } elseif (!isset($arrayA['info'][$sortBy]) && isset($arrayB['info'][$sortBy])) {
                  return -1;
              }

              if ($arrayA['info'][$sortBy] === $arrayB['info'][$sortBy]) {
                  return 0;
              } elseif ($arrayA['info'][$sortBy] > $arrayB['info'][$sortBy]) {
                  return 1;
              } else {
                  return -1;
              }
          });
    }

    /**
     * Parses all properly formatted movies in the directory.
     *
     * @return array
     */
    public function parseMovies()
    {
        $movies = [];
        $movieFolders = array_diff(scandir($this->options['path']), ['.', '..']);
        $ini = new Ini(new Filesystem());
        foreach ($movieFolders as $movieFolder) {
            $linkFile = $this->options['path']."/$movieFolder/$movieFolder - IMDb.url";
            $movieInfo = $ini->read($linkFile);

            // don't process files which have not been parsed or which don't pass the filters
            if (!isset($movieInfo['info']) || !$this->passesFilters($movieInfo['info'])) {
                continue;
            }

            $movies[$this->options['path'].DIRECTORY_SEPARATOR.$movieFolder] = $movieInfo;
        }
        return $movies;
    }
}
