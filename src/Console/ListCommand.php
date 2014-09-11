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
                'yearFrom',
                'yf',
                InputOption::VALUE_REQUIRED,
                'List only movies from a certain year (e.g. -yf 2000 list movies between 2000-2014).'
            )
            ->addOption(
                'yearTo',
                'yt',
                InputOption::VALUE_REQUIRED,
                'List only movies up to a certain year (e.g. -yt 2000 list movies between 1900-2000).'
            )
            ->addOption(
                'rating',
                'r',
                InputOption::VALUE_REQUIRED,
                'List only movies with an IMDb rating equal or higher then this rating.'
            )
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $path = $input->getArgument('path');
        $delimiter = $input->getOption('print0') ? "\0" : PHP_EOL;

        $movies = [];
        $movieFolders = array_diff(scandir($path), ['.', '..']);
        foreach ($movieFolders as $movieFolder) {
            $linkFile = "$path/$movieFolder/$movieFolder - IMDb.url";
            $movieInfo = Reader::read($linkFile);
            if (false === $movieInfo) {
                continue;
            }

            if (!isset($movieInfo['info'])) {
                continue;
            }

            $movies[] = $movieFolder;
        }
        echo implode($delimiter, $movies);
    }
}