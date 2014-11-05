<?php

namespace Mihaeu\MovieManager\Console;

use Mihaeu\MovieManager\FileSet;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class Command extends BaseCommand
{
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
        ;
    }

    /**
     * Processes all movies in a directory and filters out unwanted movies.
     *
     * @param string $path
     *
     * @return array|FileSet[]
     */
    public function getFilteredMovies($path)
    {
        return [];
    }
}
