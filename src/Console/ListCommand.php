<?php

namespace Mihaeu\MovieManager\Console;

use Mihaeu\MovieManager\Ini\Reader;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

class ListCommand extends Command
{
    /**
     * @var array|Callback[]
     */
    private $filters = [
        'year-from' => 0,
        'year-to'   => 0,
        'rating'    => 0
    ];

    public function configure()
    {
        $this
            ->setName('list')
            ->setDescription('Lists all the (correctly formatted) movies in a directory.')
            ->addArgument(
                'path',
                InputArgument::REQUIRED,
                'Path to your movie folder.'
            )
            ->addOption(
                'print0',
                null,
                InputOption::VALUE_NONE,
                'Prints the movies with a null character instead of new lines (e.g. for xargs -0).'
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
                'Stop listing movie after a certain total filesize has been reached.'
            )
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $path = $input->getArgument('path');
        $delimiter = $input->getOption('print0') ? "\0" : PHP_EOL;

        $movies = [];
        $movieFolders = array_diff(scandir($path), ['.', '..']);

        $totalSize = 0;
        foreach ($movieFolders as $movieFolder) {
            $linkFile = "$path/$movieFolder/$movieFolder - IMDb.url";
            $movieInfo = Reader::read($linkFile);
            if (false === $movieInfo) {
                continue;
            }

            // don't process files which have not been parsed
            if (!isset($movieInfo['info'])) {
                continue;
            }

            if ($this->passesFilters($input, $movieInfo['info'])) {

                // if we check for max size then try to fit in as many movies as possible
                if ($input->getOption('max-size')) {
                    $movieSize = $this->getMovieSizeInMb($path.$movieFolder);
                    if ($totalSize + $movieSize <= $input->getOption('max-size')) {
                        $totalSize += $movieSize;
                        $movies[] = realpath($path).DIRECTORY_SEPARATOR.$movieFolder;
                    }
                } else {
                    $movies[] = realpath($path).DIRECTORY_SEPARATOR.$movieFolder;
                }

            }
        }

        echo implode($delimiter, $movies).PHP_EOL;
    }

    /**
     * Tests all filters for a movie and returns true only when all filters pass.
     *
     * @param InputInterface $input
     * @param array          $movieInfo
     *
     * @return bool
     */
    public function passesFilters(InputInterface $input, $movieInfo)
    {
        $yearFrom =
            // if the filter has not been set, then it passes
            !$input->getOption('year-from')
            // if the information does not exist, we cannot filter it
            || isset($movieInfo['release_date'])
            // check the condition
            && $movieInfo['release_date'] >= $input->getOption('year-from');

        $yearTo =
            !$input->getOption('year-to')
            || isset($movieInfo['release_date'])
            && $movieInfo['release_date'] <= $input->getOption('year-to');

        $rating =
            !$input->getOption('rating')
            || isset($movieInfo['imdb_rating'])
            && $movieInfo['imdb_rating'] >= $input->getOption('rating');

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
}