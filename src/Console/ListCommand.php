<?php

namespace Mihaeu\MovieManager\Console;

use Mihaeu\MovieManager\IO\Filesystem;
use Mihaeu\MovieManager\IO\Ini;
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

    /**
     * {@inheritdoc}
     */
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

    /**
     * Lists movies from a directory which have been previously parsed by
     * movie manager. The filters and sorts etc. have to be applied in the
     * right order to achieve the right results.
     *
     * @param InputInterface   $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->options = $input->getOptions();
        $this->options['path'] = realpath($input->getArgument('path'));
        if (!$this->options['path']) {
            $output->writeln('<error>Directory doesn\'t exist or is not readable.</error>');
            return 1;
        }

        $this->movies = $this->parseMovies();

        if ($this->options['sort-by']) {
            $this->sortMovies();
        }

        $matchedMovies = array_keys($this->movies);
        if ($this->options['desc']) {
            $matchedMovies = array_reverse($matchedMovies);
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

        if ($this->options['print0']) {
            $output->write(implode("\0", $matchedMovies));
        } else {
            $output->writeln(implode("\n", $matchedMovies));
        }
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
