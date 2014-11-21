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
     * @return int|null
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->options = $input->getOptions();
        $this->options['path'] = realpath($input->getArgument('path'));
        if (!$this->options['path']) {
            $output->writeln('<error>Directory doesn\'t exist or is not readable.</error>');
            return 1;
        }

        $movies = $this->getFilteredMovies($this->options['path'], $this->options);

        if (empty($movies)) {
            $output->writeln('<error>No movies found or no movies matched the filters.</error>');
        } else if ($this->options['print0']) {
            $output->write(implode("\0", $movies));
        } else {
            $output->writeln(implode("\n", $movies));
        }

        return 0;
    }
}
