<?php

namespace Mihaeu\MovieManager\Console;

use GuzzleHttp\Client;
use Mihaeu\MovieManager\Config;
use Mihaeu\MovieManager\Factory\FileSetFactory;
use Mihaeu\MovieManager\Factory\MovieFactory;
use Mihaeu\MovieManager\FileSet;
use Mihaeu\MovieManager\IO\Filesystem;
use Mihaeu\MovieManager\IO\Ini;
use Mihaeu\MovieManager\MovieDatabase\IMDb;
use Mihaeu\MovieManager\MovieDatabase\OMDb;
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
    const MSG_CREATE_TRAILER            = '[%s] Downloading trailer ... ';
    const MSG_MOVE_FILE                 = '[%s] Renaming movie file ... ';
    const MSG_MOVE_DIRECTORY            = '[%s] Renaming movie directory ... ';
    const MSG_MOVE_SEPARATE_DIRECTORY   = '[%s] Moving to separate movie directory ... ';
    const MSG_MOVE_TO_ROOT              = '[%s] Moving to new destination ... ';
    const MSG_NO_MOVIES                 = '<info>Couldn\'t find any movies.</info>';
    const MSG_NO_MATCHES                = '<error>No matches for your query.</error>';

    const QUESTION_TITLE = 'Please enter the movie title [or hit ENTER to skip, p to play, q to quit]: ';

    /**
     * @var MovieFactory
     */
    private $movieFactory;

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
        $config = new Config();
        $this->io = new IO($input, $output, $this->getHelperSet());

        $this->movieRoot = new \SplFileInfo($input->getArgument('path'));
        $finder = new MovieFinder(new FileSetFactory($this->movieRoot), $config->get('allowed-movie-formats'));
        $movieFiles = $finder->findMoviesInDir($this->movieRoot->getRealPath());

        $this->tmdb = new TMDb($config->get('tmdb-api-key'));
        $client = new Client();
        $this->movieFactory = new MovieFactory($this->tmdb, new IMDb($client), new OMDb($client));

        if (!$input->getOption('show-all')) {
            $movieFiles = $this->filterBadMovies($movieFiles);
        }

        if (empty($movieFiles)) {
            $this->io->write(self::MSG_NO_MOVIES);
            return;
        }

        $this->io->table(
            ['Name', 'Format ', 'Folder ', 'Info   ', 'Screeny', 'Poster '],
            $this->formatMoviesForTable($movieFiles)
        );

        $this->manageMoviesInteractively($movieFiles, $input, $output);
    }

    /**
     * Filters the movies so that only movies which are not properly parsed will be left.
     *
     * @param array|FileSet[] $fileSets
     *
     * @return array|FileSet[]
     */
    public function filterBadMovies(array $fileSets)
    {
        return array_filter($fileSets, function (FileSet $fileSet) {
            return !(
                   $fileSet->hasCorrectName()
                && $fileSet->hasCorrectParentFolder()
                && $fileSet->getInfoFile()
                && $fileSet->getImdbScreenshotFile()
                && $fileSet->getPosterFile()
            );
        });
    }

    /**
     * Formats a movie for pretty printing in a symfony console table.
     *
     * @param array|FileSet[] $fileSets
     *
     * @return array
     */
    public function formatMoviesForTable(array $fileSets)
    {
        return array_map(function (FileSet $fileSet) {
            return [
                substr($fileSet->getMovieFile()->getBasename(), 0, 40),
                $fileSet->hasCorrectName()         ? self::CLI_CELL_OK : self::CLI_CELL_NOK,
                $fileSet->hasCorrectParentFolder() ? self::CLI_CELL_OK : self::CLI_CELL_NOK,
                $fileSet->getInfoFile()            ? self::CLI_CELL_OK : self::CLI_CELL_NOK,
                $fileSet->getImdbScreenshotFile()  ? self::CLI_CELL_OK : self::CLI_CELL_NOK,
                $fileSet->getPosterFile()          ? self::CLI_CELL_OK : self::CLI_CELL_NOK
            ];
        }, $fileSets);
    }

    /**
     * @param array|FileSet[] $fileSets
     *
     * @throws \Exception
     */
    public function manageMoviesInteractively(array $fileSets)
    {
        $movieTitleQuestion =  new Question(self::QUESTION_TITLE);
        $movieHandler = new MovieHandler(new Filesystem());

        $index = 0;
        foreach ($fileSets as $fileSet) {
            /** @var FileSet $fileSet */
            $movieFile = $fileSet->getMovieFile();
            $this->io->write(sprintf(self::MSG_TITLE, ++$index, count($fileSets), $movieFile->getBasename()));

            if (null === $fileSet->getInfoFile()) {
                $query = $this->io->askQuestion($movieTitleQuestion);
                if (empty($query)) {
                    continue;
                }

                if ('q' === $query) {
                    break;
                }

                if ('p' === $query) {
                    system('vlc "'.$movieFile->getRealPath().'"');
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
                    $newFilename = $movieHandler->moveMovieToSeparateFolder($this->movieRoot, $movieFile);
                    $movieFile = new \SplFileObject($newFilename);
                    $this->io->write(sprintf(self::MSG_MOVE_SEPARATE_DIRECTORY, self::CLI_OK));
                }

                $this->io->write(sprintf(self::MSG_CREATE_INFO, ' '), false);
                $parsedMovie = $this->movieFactory->create($tmdbId);
                $result = $movieHandler->createMovieInfo($parsedMovie, $movieFile);
                $this->io->overwrite(sprintf(self::MSG_CREATE_INFO, $result ? self::CLI_OK : self::CLI_NOK));
            } else {
                $infoFile = $movieFile->getPath().'/'.$movieFile->getBasename('.'.$movieFile->getExtension()).' - IMDb.url';
                if (!file_exists($infoFile)) {
                    throw new \Exception('Movie info file does not exist, movie cannot be processed.'.PHP_EOL.$infoFile);
                }
                $ini = new Ini(new Filesystem());
                $movieInfo = $ini->read($infoFile);

                $parsedMovie = $this->movieFactory->create($movieInfo['info']['id']);
            }

            if (null === $fileSet->getImdbScreenshotFile()) {
                $this->io->write(sprintf(self::MSG_CREATE_SCREENY, ' '), false);
                $result = $movieHandler->downloadIMDbScreenshot($parsedMovie, $movieFile);
                $this->io->overwrite(sprintf(self::MSG_CREATE_SCREENY, $result ? self::CLI_OK : self::CLI_NOK));
            }

            if (null === $fileSet->getPosterFile()) {
                $this->io->write(sprintf(self::MSG_CREATE_POSTER, ' '), false);
                $result = $movieHandler->downloadMoviePoster($parsedMovie, $movieFile);
                $this->io->overwrite(sprintf(self::MSG_CREATE_POSTER, $result ? self::CLI_OK : self::CLI_NOK));
            }

            if (!$fileSet->hasCorrectName()) {
                $this->io->write(sprintf(self::MSG_MOVE_FILE, ' '), false);
                $newFilename = $movieHandler->renameMovie($parsedMovie, $movieFile);
                if ($newFilename) {
                    $movieFile = new \SplFileObject($newFilename);
                    $this->io->overwrite(sprintf(self::MSG_MOVE_FILE, self::CLI_OK));
                }
            }

            if (!$fileSet->hasCorrectParentFolder()) {
                $this->io->write(sprintf(self::MSG_MOVE_DIRECTORY, ' '), false);
                $newDirectory = $movieHandler->renameMovieFolder($parsedMovie, $movieFile);
                if ($newDirectory) {
                    $movieFile = new \SplFileObject($newDirectory.'/'.$movieFile->getBasename());
                    $this->io->overwrite(sprintf(self::MSG_MOVE_DIRECTORY, self::CLI_OK));
                }
            }

            if (!file_exists($movieHandler->generateFileName($parsedMovie, $movieFile, ' - Trailer.mp4'))) {
                $result = $movieHandler->downloadTrailer($parsedMovie, $movieFile);
                $this->io->write(sprintf(PHP_EOL.self::MSG_CREATE_TRAILER, $result ? self::CLI_OK : self::CLI_NOK));
            }

            if ($this->io->getOption('move-to')) {
                $this->io->write(sprintf(self::MSG_MOVE_TO_ROOT, ''), false);
                $movieHandler->moveTo($movieFile, $this->io->getOption('move-to'));
                $this->io->overwrite(sprintf(self::MSG_MOVE_TO_ROOT, self::CLI_OK));
            }
        }
    }
}
