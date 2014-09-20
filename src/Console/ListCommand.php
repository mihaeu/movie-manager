<?php

namespace Mihaeu\MovieManager\Console;

use Mihaeu\MovieManager\Ini\Reader;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Lists all the (correctly formatted) movies in a directory.
 *
 * @package Mihaeu\MovieManager\Console
 *
 * @author Michael Haeuslmann (haeuslmann@gmail.com)
 */
class ListCommand extends Command
{
    /**
     * @var array
     */
    private $movies = [];

    /**
     * @var array
     */
    private $options;

    public function configure()
    {
        parent::configure();

        $this
            ->setName('list')
            ->setDescription('Lists all the (correctly formatted) movies in a directory.')
            ->addOption(
                'print0',
                null,
                InputOption::VALUE_NONE,
                'Prints the movies with a null character instead of new lines (e.g. for xargs -0).'
            )
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->options = $input->getOptions();
        $this->options['path'] = realpath($input->getArgument('path'));

        $this->movies = $this->parseMovies();

        if ($this->options['sort-by']) {
            $this->sortMovies();
        }

        $matchedMovies = array_keys($this->movies);
        if ($this->options['desc']) {
            $matchedMovies = array_reverse($matchedMovies);
        }

        if ($this->options['print0']) {
            echo implode("\0", $matchedMovies);
        } else {
            echo implode("\n", $matchedMovies).PHP_EOL;
        }
    }

    /**
     * Tests all filters for a movie and returns true only when all filters pass.
     *
     * @param array          $movieInfo
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
            } else if (isset($arrayA['info'][$sortBy]) && !isset($arrayB['info'][$sortBy])) {
                return 1;
            }  else if (!isset($arrayA['info'][$sortBy]) && isset($arrayB['info'][$sortBy])) {
                return -1;
            }

            if ($arrayA['info'][$sortBy] === $arrayB['info'][$sortBy]) {
                return 0;
            } else if ($arrayA['info'][$sortBy] > $arrayB['info'][$sortBy]) {
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
        $totalSize = 0;
        $movieFolders = array_diff(scandir($this->options['path']), ['.', '..']);
        foreach ($movieFolders as $movieFolder) {
            $linkFile = $this->options['path']."/$movieFolder/$movieFolder - IMDb.url";
            $movieInfo = Reader::read($linkFile);

            // don't process files which have not been parsed
            if (!isset($movieInfo['info'])) {
                continue;
            }

            if ($this->passesFilters($movieInfo['info'])) {
                // if we check for max size then try to fit in as many movies as possible
                if ($this->options['max-size']) {
                    $movieSize = $this->getMovieSizeInMb($this->options['path'].$movieFolder);
                    if ($totalSize + $movieSize <= $this->options['max-size']) {
                        $totalSize += $movieSize;
                        $movies[$this->options['path'].DIRECTORY_SEPARATOR.$movieFolder] = $movieInfo;
                    }
                } else {
                    $movies[$this->options['path'].DIRECTORY_SEPARATOR.$movieFolder] = $movieInfo;
                }

            }
        }
        return $movies;
    }
}