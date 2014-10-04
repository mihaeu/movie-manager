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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Filesystem\Filesystem;

class ManageCommand extends BaseCommand
{
    const CLI_OK        = '<info>✔</info>';
    const CLI_NOK       = '<error>✘</error>';
    const CLI_CELL_OK   = '<fg=black;bg=green>   ✔   </fg=black;bg=green>';
    const CLI_CELL_NOK  = '<fg=black;bg=red>   ✘   </fg=black;bg=red>';

    const MSG_TITLE                     = '<info>[%d/%d] %s</info>';
    const MSG_CREATE_INFO               = '[%s] Creating movie information file ... ';
    const MSG_CREATE_SCREENY            = '[%s] Downloading IMDb screenshot ... ';
    const MSG_CREATE_POSTER             = '[%s] Downloading poster ... ';
    const MSG_MOVE_FILE                 = '[%s] Renaming movie file ... ';
    const MSG_MOVE_DIRECTORY            = '[%s] Renaming movie directory ... ';
    const MSG_MOVE_SEPARATE_DIRECTORY   = '[%s] Moving to separate movie directory ... ';
    const MSG_MOVE_TO_ROOT              = '[%s] Moving to new destination ... ';
    const MSG_NO_MOVIES                 = '<info>Couldn\'t find any movies.</info>';
    const MSG_NO_MATCHES                = '<error>No matches for your query.</error>';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var MovieFactory
     */
    private $movieFactory;

    /**
     * @var FileSetFactory
     */
    private $fileSetFactory;

    /**
     * @var \SplFileInfo
     */
    private $movieRoot;

    /**
     * @var TMDb
     */
    private $tmdb;

    /**
     * @var IO
     */
    private $io;

    /**
     * {@inheritdoc}
     */
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
            ->addOption(
                'move-to',
                null,
                InputOption::VALUE_REQUIRED,
                'Moves the parsed file to another directory.'
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->config = new Config();
        $this->io = new IO($input, $output, $this->getHelperSet());

        $this->movieRoot = new \SplFileInfo($input->getArgument('path'));
        $finder = new MovieFinder();
        $movieFiles = $finder->findMoviesInDir(
            $this->movieRoot->getRealPath(),
            $this->config->get('allowed-movie-formats')
        );

        $this->tmdb = new TMDb($this->config->get('tmdb-api-key'));
        $imdb = new IMDb();
        $this->movieFactory = new MovieFactory($this->tmdb, $imdb);
        $this->fileSetFactory = new FileSetFactory($this->movieRoot);

        if (!$input->getOption('show-all')) {
            $movieFiles = $this->filterBadMovies($movieFiles);
        }

        if (empty($movieFiles)) {
            $this->io->write(self::MSG_NO_MOVIES);
            return;
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
     *
     * @throws \Exception
     */
    public function manageMoviesInteractively(array $movieFiles)
    {
        $movieTitleQuestion =  new Question('Please enter the movie title [or hit ENTER to skip, p to play, q to quit]: ');
        $movieHandler = new MovieHandler(new Filesystem());

        $index = 0;
        foreach ($movieFiles as $movie) {
            $movieFile = new \SplFileObject($movie['fullname']);
            $this->io->write(sprintf(self::MSG_TITLE, ++$index, count($movieFiles), $movie['name']));

            if (!$movie['link']) {
                $query = $this->io->askQuestion($movieTitleQuestion);
                if (empty($query)) {
                    continue;
                }

                if ('q' === $query) {
                    break;
                }

                if ('p' === $query) {
                    system('vlc "'.$movie['fullname'].'"');
                    $query = $this->io->askQuestion($movieTitleQuestion);

                    if (empty($query)) {
                        continue;
                    }

                    if ('q' === $query) {
                        break;
                    }
                }

                $suggestions = $this->tmdb->getMovieSuggestionsFromQuery($query);

                if (empty($suggestions)) {
                    $this->io->write(self::MSG_NO_MATCHES);
                    continue;
                }

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
                $titleChoice = $this->io->askQuestion($suggestionQuestion);
                $tmdbId = preg_replace('/^.*\/movie\/(\d+)$/', '$1', $titleChoice);

                if ($movieHandler->movieIsNotInSeparateFolder($this->movieRoot, $movieFile)) {
                    $movie['fullname'] = $movieHandler->moveMovieToSeparateFolder($this->movieRoot, $movieFile);
                    $movie['path'] = dirname($movie['fullname']);
                    $movie['link'] = false;
                    $movie['poster'] = false;
                    $movie['screenshot'] = false;
                    $this->io->write(sprintf(self::MSG_MOVE_SEPARATE_DIRECTORY, self::CLI_OK));
                }

                $this->io->write(sprintf(self::MSG_CREATE_INFO, ' '), false);
                $parsedMovie = $this->movieFactory->create($tmdbId);
                $result = $movieHandler->createMovieInfo($parsedMovie, $movieFile);
                $this->io->overwrite(sprintf(self::MSG_CREATE_INFO, $result ? self::CLI_OK : self::CLI_NOK));
            } else {
                $infoFile = $movieFile->getPath().DIRECTORY_SEPARATOR.$movieFile->getBasename('.'.$movieFile->getExtension()).' - IMDb.url';
                if (!file_exists($infoFile)) {
                    throw new \Exception('Movie info file does not exist, movie cannot be processed.'.PHP_EOL.$infoFile);
                }
                $movieInfo = Reader::read($infoFile);

                $parsedMovie = $this->movieFactory->create($movieInfo['info']['id']);
            }

            if (!$movie['screenshot']) {
                $this->io->write(sprintf(self::MSG_CREATE_SCREENY, ' '), false);
                $result = $movieHandler->downloadIMDbScreenshot($parsedMovie, $movieFile);
                $this->io->overwrite(sprintf(self::MSG_CREATE_SCREENY, $result ? self::CLI_OK : self::CLI_NOK));
            }

            if (!$movie['poster']) {
                $this->io->write(sprintf(self::MSG_CREATE_POSTER, ' '), false);
                $result = $movieHandler->downloadMoviePoster($parsedMovie, $movieFile);
                $this->io->overwrite(sprintf(self::MSG_CREATE_POSTER, $result ? self::CLI_OK : self::CLI_NOK));
            }

            $movieWasRenamed = $movieHandler->renameMovie($parsedMovie, $movieFile);
            if ($movieWasRenamed) {
                $this->io->write(sprintf(self::MSG_MOVE_FILE, self::CLI_OK));
            }

            $newDirectory = $movieHandler->renameMovieFolder($parsedMovie, $movieFile);
            if ($newDirectory) {
                $this->io->write(sprintf(self::MSG_MOVE_DIRECTORY, self::CLI_OK));
            }

            if ($this->io->getOption('move-to')) {
                $movieHandler->moveTo($movieFile, $this->io->getOption('move-to'));
                $this->io->write(sprintf(self::MSG_MOVE_TO_ROOT, self::CLI_OK));
            }
        }
    }
}