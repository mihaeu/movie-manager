<?php

namespace Mihaeu\MovieManager\Console;

use Mihaeu\MovieManager\Config;
use Mihaeu\MovieManager\Factory\FileSetFactory;
use Mihaeu\MovieManager\Factory\MovieFactory;
use Mihaeu\MovieManager\Ini\Reader;
use Mihaeu\MovieManager\MovieDatabase\IMDb;
use Mihaeu\MovieManager\MovieDatabase\TMDb;
use Mihaeu\MovieManager\MovieFinder;
use Mihaeu\MovieManager\MovieHandler;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

class ManageCommand extends BaseCommand
{
    const CLI_OK = '<info>✔</info>';
    const CLI_NOK = '<error>✘</error>';
    const CLI_CELL_OK = '<fg=black;bg=green>   ✔   </fg=black;bg=green>';
    const CLI_CELL_NOK = '<fg=black;bg=red>   ✘   </fg=black;bg=red>';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var MovieFactory
     */
    private $movieFactory;

    /**
     * @var FileSetFactory
     */
    private $fileSetFactory;

    /**
     * @var TMDb
     */
    private $tmdb;

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
        $this->config = new Config();
        $this->input = $input;
        $this->output = $output;

        $finder = new MovieFinder();
        $movieFiles = $finder->findMoviesInDir($input->getArgument('path'), $this->config->get('allowed-movie-formats'));

        $tmdb = new TMDb($this->config->get('tmdb-api-key'));
        $imdb = new IMDb();
        $this->movieFactory = new MovieFactory($tmdb, $imdb);
        $this->fileSetFactory = new FileSetFactory($input->getArgument('path'));

        if (!$input->getOption('show-all')) {
            $movieFiles = $this->filterBadMovies($movieFiles);
        }

        $table = $this->getHelper('table');
        $table
            ->setHeaders(['Name', 'Format ', 'Folder ', 'Info   ', 'Screeny', 'Poster '])
            ->setRows($this->formatMoviesForTable($movieFiles))
        ;
        $table->render($output);

        $this->manageMoviesInteractively($movieFiles, $input, $output);
    }

    /**
     * Filters the movies so that only movies which are not properly parsed will be left.
     *
     * @param array $movieFiles
     *
     * @return array
     */
    public function filterBadMovies(array $movieFiles)
    {
        return array_filter($movieFiles, function ($movie) {
            return !$movie['format']
            || !$movie['folder']
            || !$movie['link']
            || !$movie['screenshot']
            || !$movie['poster'];
        });
    }

    /**
     * Formats a movie for pretty printing in a symfony console table.
     *
     * @param array $movieFiles
     *
     * @return array
     */
    public function formatMoviesForTable(array $movieFiles)
    {
        return array_map(function (array $movie) {
            return [
                substr($movie['name'], 0, 40),
                $movie['format']        ? self::CLI_CELL_OK : self::CLI_CELL_NOK,
                $movie['folder']        ? self::CLI_CELL_OK : self::CLI_CELL_NOK,
                $movie['link']          ? self::CLI_CELL_OK : self::CLI_CELL_NOK,
                $movie['screenshot']    ? self::CLI_CELL_OK : self::CLI_CELL_NOK,
                $movie['poster']        ? self::CLI_CELL_OK : self::CLI_CELL_NOK
            ];
        }, $movieFiles);
    }

    /**
     * @param array $movieFiles
     */
    public function manageMoviesInteractively(array $movieFiles)
    {
        /** @var QuestionHelper $dialog */
        $helper = $this->getHelper('question');
        $movieTitleQuestion =  new Question('Please enter the movie title [or hit ENTER to skip or q to quit]: ');

        $movieHandler = new MovieHandler($this->config);
        $oldTMDbHandler = $movieHandler->getTMDB();

        $index = 0;
        foreach ($movieFiles as $movie) {

            $this->output->writeln(sprintf("\n<info>[%d/%d] %s</info>", ++$index, count($movieFiles), $movie['name']));

            if (!$movie['link']) {
                $query = $helper->ask($this->input, $this->output, $movieTitleQuestion);
                if (empty($query)) {
                    continue;
                }

                if ('q' === $query) {
                    break;
                }

                $suggestions = $this->tmdb->getMovieSuggestionsFromQuery($query);
                $suggestionChoices = [];
                foreach ($suggestions as $suggestion) {
                    $suggestionChoices[] = sprintf(
                        '%-50s (%4d)   %s',
                        $suggestion['title'],
                        $suggestion['year'],
                        'https://www.themoviedb.org/movie/'.$suggestion['id']
                    );
                }
                $suggestion['q'] = 'quit';
                $suggestionQuestion = new ChoiceQuestion(
                    'What is the correct title?',
                    $suggestionChoices
                );
                $titleChoice = $helper->ask($this->input, $this->output, $suggestionQuestion);
                $tmdbId = preg_replace('/^.*\/movie\/(\d+)$/', '$1', $titleChoice);

                $this->output->write('Creating movie information file ... ');
                $parsedMovie = $this->movieFactory->create($tmdbId);
                $result = $movieHandler->createMovieInfo($parsedMovie, $movie['path']);
                $this->output->writeln($result ? self::CLI_OK : self::CLI_NOK);
            }

            if ($movie['link']) {
                $infoFile = $movie['path'].DIRECTORY_SEPARATOR.basename($movie['path']).' - IMDb.url';
                $movieInfo = Reader::read($infoFile);
                $title = $movieInfo['info']['title'];
                $year = $movieInfo['info']['year'];

                $tmdbMovie = $oldTMDbHandler->getMovie($movieInfo['info']['id']);
                if (!$movie['screenshot']) {
                    $this->output->write('Downloading IMDb screenshot ... ');
                    $result = $movieHandler->downloadIMDbScreenshot($movieInfo['info']['imdb_id'], $title, $year, $movie['path']);
                    $this->output->writeln($result ? self::CLI_OK : self::CLI_NOK);
                }

                if (!$movie['poster']) {
                    $this->output->write('Downloading movie poster ... ');
                    $result = $movieHandler->downloadIMDbScreenshot($movieInfo['info']['imdb_id'], $title, $year, $movie['path']);
                    $oldTMDbHandler = $movieHandler->getTMDB();
                    $movieHandler->downloadMoviePoster(
                        $title,
                        $year,
                        $movie['path'],
                        $tmdbMovie
                    );
                    $this->output->writeln($result ? self::CLI_OK : self::CLI_NOK);
                }
            }
        }
    }
}