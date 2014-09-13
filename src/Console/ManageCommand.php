<?php

namespace Mihaeu\MovieManager\Console;

use Mihaeu\MovieManager\Config;
use Mihaeu\MovieManager\MovieFinder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ManageCommand extends Command
{
    public function configure()
    {
        $this
            ->setName('manage')
            ->setDescription('Manage your movie collection.')
            ->addArgument(
                'path',
                InputArgument::REQUIRED,
                'Path to your movie folder.'
            )
            ->addOption(
                'show-all',
                null,
                InputOption::VALUE_NONE,
                'Shows all movies instead of only bad ones.'
            )
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $config = new Config();
        $finder = new MovieFinder();
        $movieFiles = $finder->findMoviesInDir($input->getArgument('path'), $config->get('allowed-movie-formats'));

        if (!$input->getOption('show-all')) {
            $movieFiles = array_filter($movieFiles, function ($movie) {
                return !$movie['format']
                    || !$movie['folder']
                    || !$movie['link']
                    || !$movie['screenshot']
                    || !$movie['poster'];
            });
        }

        $movies = array_map(function (array $movie) {
            return [
                $movie['name'],
                $movie['format']        ? '<fg=black;bg=green>   ✔   </fg=black;bg=green>' : '<fg=black;bg=red>   ✘   </fg=black;bg=red>',
                $movie['folder']        ? '<fg=black;bg=green>   ✔   </fg=black;bg=green>' : '<fg=black;bg=red>   ✘   </fg=black;bg=red>',
                $movie['link']          ? '<fg=black;bg=green>   ✔   </fg=black;bg=green>' : '<fg=black;bg=red>   ✘   </fg=black;bg=red>',
                $movie['screenshot']    ? '<fg=black;bg=green>   ✔   </fg=black;bg=green>' : '<fg=black;bg=red>   ✘   </fg=black;bg=red>',
                $movie['poster']        ? '<fg=black;bg=green>   ✔   </fg=black;bg=green>' : '<fg=black;bg=red>   ✘   </fg=black;bg=red>'
            ];
        }, $movieFiles);

        $table = $this->getHelper('table');
        $table
            ->setHeaders([
                'Name',
                    'Format ',
                    'Folder ',
                    'Info   ',
                    'Screeny',
                    'Poster '
            ])
            ->setRows($movies)
        ;
        $table->render($output);
    }
}