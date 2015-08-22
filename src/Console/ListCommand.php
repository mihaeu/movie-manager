<?php

namespace Mihaeu\MovieManager\Console;

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
     * {@inheritdoc}
     */
    public function configure()
    {
        parent::configure();

        $this
            ->setName('print-list')
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
     * movie manager.
     *
     * @param InputInterface   $input
     * @param OutputInterface $output
     *
     * @return int|null
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $path = realpath($input->getArgument('path'));
        if (!$path) {
            $output->writeln('<error>Directory doesn\'t exist or is not readable.</error>');
            return self::RETURN_CODE_BAD_DIRECTORY;
        }

        $movies = $this->getFilteredMovies($path, $input->getOptions());

        if (empty($movies)) {
            $output->writeln('<error>No movies found or no movies matched the filters.</error>');
            return self::RETURN_CODE_NO_MATCHES;
        }

        $eol = "\n";
        if ($input->getOption('print0')) {
            $eol = "\0";
        }
        foreach ($movies as $movieDirectory => $movie) {
            $output->write($movieDirectory.$eol);
        }

        return self::RETURN_CODE_OK;
    }
}
