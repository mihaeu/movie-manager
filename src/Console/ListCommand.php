<?php

namespace Mihaeu\MovieManager\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this
            ->setName('list')
            ->setDescription('Describes the application and lists all available commands.')
        ;
    }

    /**
     * Copies movies from a directory which have been previously parsed by
     * movie manager.
     *
     * @param InputInterface   $input
     * @param OutputInterface $output
     *
     * @return int|null
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->write('<info> __  __  _____  _  _  ____  ____
(  \/  )(  _  )( \/ )(_  _)( ___)
 )    (  )(_)(  \  /  _)(_  )__)
(_/\/\_)(_____)  \/  (____)(____)
 __  __    __    _  _    __    ___  ____  ____
(  \/  )  /__\  ( \( )  /__\  / __)( ___)(  _ \
 )    (  /(__)\  )  (  /(__)\( (_-. )__)  )   /
(_/\/\_)(__)(__)(_)\_)(__)(__)\___/(____)(_)\_)</info>

Movie manager for nerds (and people who suffer from OCD).

Usage: moviemanager [command] [options]

Commands:

    manage        Identify movies, rename and move them to a proper folder
                  structure, download official poster and trailer and save the
                  movie information

    build         Build a movie collection in a single HTML file

    copy          Copy all movies that match your filters to
                  somewhere else

    print-list    List movies on the console
                  (e.g. for processing with xargs)

    list          This command

    help          Print further help for a command

If you experience any issues, please submit them at https://github.com/mihaeu/movie-manager/issues');
    }
}