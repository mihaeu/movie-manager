<?php declare(strict_types = 1);

namespace Mihaeu\MovieManager\Console;

use Mihaeu\MovieManager\Builder\HtmlBuilder;

use Mihaeu\MovieManager\Factory\MovieFactory;
use Mihaeu\MovieManager\IO\Filesystem;
use Mihaeu\MovieManager\IO\Ini;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

class BuildCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        parent::configure();

        $this
            ->setName('build')
            ->setDescription('Builds a nice collection file in HTML.')
            ->addArgument(
                'save',
                InputArgument::OPTIONAL,
                'Save the result to a file.',
                'php://output'
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
        $movieFactory = new MovieFactory(null, null, null, new Ini(new Filesystem()));
        $buildWithPosters = !$input->getOption('no-posters');
        $builder = new HtmlBuilder($movieFactory, $buildWithPosters);

        $path = realpath($input->getArgument('path'));
        $movies = $this->getFilteredMovies($path, $input->getOptions());
        if (empty($movies)) {
            $output->writeln('<error>No movies found or no movies matched the filters.</error>');
            return self::RETURN_CODE_NO_MATCHES;
        }

        file_put_contents(
            $input->getArgument('save'),
            $builder->build($movies, $path)
        );
    }
}
