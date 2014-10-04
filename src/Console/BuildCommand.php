<?php

namespace Mihaeu\MovieManager\Console;

use Mihaeu\MovieManager\Builder\Html;

use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

class BuildCommand extends BaseCommand
{
    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this
            ->setName('build')
            ->setDescription('Builds a nice collection file in HTML.')
            ->addArgument(
                'path',
                InputArgument::REQUIRED,
                'Path to your movie folder.'
            )
            ->addArgument(
                'save',
                InputArgument::OPTIONAL,
                'Save the result to a file.',
                'php://output'
            )
            ->addOption(
                'limit',
                null,
                InputOption::VALUE_REQUIRED,
                'Limit the number of movies.',
                -1
            )
            ->addOption(
                'no-posters',
                null,
                InputOption::VALUE_NONE,
                'Limit the number of movies.'
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $builder = new Html();

        $path = realpath($input->getArgument('path'));
        $buildWithPosters = !$input->getOption('no-posters');
        file_put_contents(
            $input->getArgument('save'),
            $builder->build($path, $input->getOption('limit'), $buildWithPosters)
        );
    }
}