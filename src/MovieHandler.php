<?php

namespace Mihaeu\MovieManager;

use Mihaeu\MovieManager\Console\PhantomJsWrapper;
use Mihaeu\MovieManager\Console\YoutubeDlWrapper;
use Mihaeu\MovieManager\IO\Downloader;
use Mihaeu\MovieManager\IO\Filesystem;
use Mihaeu\MovieManager\IO\FilesystemInterface;
use Mihaeu\MovieManager\IO\Ini;

/**
 * Handles all tasks to get the movie into a proper format.
 *
 * @package Mihaeu\MovieManager
 *
 * @author Michael Haeuslmann (haeuslmann@gmail.com)
 */
class MovieHandler
{
    /** @var FilesystemInterface */
    private $filesystem;

    /** @var YoutubeDlWrapper */
    private $youtubeDl;

    /** @var PhantomJsWrapper */
    private $phantomJs;

    /** @var Downloader */
    private $downloader;

    public function __construct(
        FilesystemInterface $filesystem,
        YoutubeDlWrapper $youtubeDl,
        PhantomJsWrapper $phantomJsWrapper,
        Downloader $downloader
    )
    {
        $this->filesystem = $filesystem;
        $this->youtubeDl = $youtubeDl;
        $this->phantomJs = $phantomJsWrapper;
        $this->downloader = $downloader;
    }

    /**
     * @param string $originalReleaseDate
     *
     * @return bool|string
     */
    public function convertMovieYear($originalReleaseDate)
    {
        return date('Y', strtotime($originalReleaseDate));
    }

    /**
     * @param Movie          $movie
     * @param \SplFileInfo   $movieFile
     *
     * @return bool
     */
    public function renameMovie(Movie $movie, \SplFileInfo $movieFile)
    {
        $newName = $this->generateFileName($movie, $movieFile, '.'.$movieFile->getExtension());

        if (file_exists($newName)) {
            return false;
        }

        $this->filesystem->rename($movieFile->getRealPath(), $newName);
        return $newName;
    }

    /**
     * @param Movie          $movie
     * @param \SplFileInfo   $movieFile
     *
     * @return bool|string
     */
    public function renameMovieFolder(Movie $movie, \SplFileInfo $movieFile)
    {
        $newName = $this->generateFileName($movie, $movieFile->getPathInfo());
        if (file_exists($newName)) {
            return false;
        }

        $this->filesystem->rename($movieFile->getPath(), $newName);
        return $newName;
    }

    /**
     * @param $imdbId
     *
     * @return string
     */
    public function getIMDbLink($imdbId)
    {
        return 'http://www.imdb.com/title/'.$imdbId;
    }

    /**
     * @param Movie          $movie
     * @param \SplFileInfo   $movieFile
     *
     * @return bool
     */
    public function downloadMoviePoster(Movie $movie, \SplFileInfo $movieFile)
    {
        $srcExtension = substr(strrchr($movie->getPosterUrl(), '.'), 1);
        $destination = $this->generateFileName($movie, $movieFile, ' - Poster.'.$srcExtension);
        return $this->downloader->download($movie->getPosterUrl(), $destination);
    }

    /**
     * Downloads a screenshot of the movie's IMDb page.
     *
     * @param Movie          $movie
     * @param \SplFileInfo   $movieFile
     *
     * @return bool
     */
    public function downloadIMDbScreenshot(Movie $movie, \SplFileInfo $movieFile)
    {
        return $this->phantomJs->downloadScreenshot(
            $this->getIMDbLink($movie->getImdbId()),
            $this->generateFileName($movie, $movieFile, ' - IMDb.png')
        );
    }

    /**
     * Creates the movie information file in .ini format, dressed up as a Windows
     * .url link.
     *
     * @param Movie          $movie
     * @param \SplFileInfo   $movieFile
     *
     * @return bool
     */
    public function createMovieInfo(Movie $movie, \SplFileInfo $movieFile)
    {
        $movieIni = ['info' => []];
        // we don't want loose values without sections and we don't want empty sections,
        // because we're going to render it into the INI format
        foreach ($movie->toArray() as $key => $value) {
            if (is_array($value)) {
                $movieIni[$key] = $value;
            } else {
                $movieIni['info'][$key] = $value;
            }
        }

        $iniArray = [
                'InternetShortcut' => [
                    'URL' => $this->getIMDbLink($movie->getImdbId())
                ]
            ] + $movieIni;

        $iniFile = $this->generateFileName($movie, $movieFile, ' - IMDb.url');
        $iniHandler = new Ini(new Filesystem());
        $iniHandler->write($iniFile, $iniArray);

        // this is not fast, but it doesn't really matter for this app
        return $iniHandler->read($iniFile) !== false;
    }

    /**
     * @param Movie          $movie
     * @param \SplFileInfo   $movieFile
     * @param string         $suffix        Including . dot
     *
     * @return string
     */
    public function generateFileName(Movie $movie, \SplFileInfo $movieFile, $suffix = '')
    {
        return $movieFile->getPath().'/'.$movie.$suffix;
    }

    /**
     * @param \SplFileInfo $movieFile
     * @param string       $targetDirectory
     *
     * @return string
     */
    public function moveTo(\SplFileInfo $movieFile, $targetDirectory)
    {
        if (!is_dir($targetDirectory)) {
            $this->filesystem->createDirectory($targetDirectory);
        }

        $newRootDirectory = $targetDirectory.DIRECTORY_SEPARATOR.$movieFile->getPathInfo()->getBasename();
        $this->filesystem->rename($movieFile->getPath(), $newRootDirectory);

        return $newRootDirectory;
    }


    /**
     * Check that every movie is in it's own folder e.g. ~/movies/Avatar/Avatar.mkv would be valid
     * but ~/movies/Avatar.mkv wouldn't, if the path argument was ~/movies
     *
     * @param \SplFileInfo $movieRoot
     * @param \SplFileInfo $movieFile
     *
     * @return bool
     */
    public function movieIsNotInSeparateFolder(\SplFileInfo $movieRoot, \SplFileInfo $movieFile)
    {
        $movieRoot = $movieRoot->getRealPath();
        $parentOfMovieParent = $movieFile->getPathInfo()->getPath();
        return $parentOfMovieParent !== $movieRoot;
    }

    /**
     * @param \SplFileInfo $movieRoot
     * @param \SplFileInfo $movieFile
     *
     * @return string Returns the full path to the moved movie file.
     */
    public function moveMovieToSeparateFolder(\SplFileInfo $movieRoot, \SplFileInfo $movieFile)
    {
        $newMovieFolder = $movieRoot->getRealPath().DIRECTORY_SEPARATOR.$movieFile->getBasename().time();
        mkdir($newMovieFolder);

        $newPath = $newMovieFolder.DIRECTORY_SEPARATOR.$movieFile->getBasename();
        rename($movieFile->getRealPath(), $newPath);

        return $newPath;
    }

    /**
     * Download the movie trailer using Python's youtube-dl (when installed).
     *
     * @param Movie        $movie
     * @param \SplFileInfo $movieFile
     *
     * @return bool
     */
    public function downloadTrailer(Movie $movie, \SplFileInfo $movieFile)
    {
        if (null === $movie->getTrailer()) {
            return false;
        }

        return $this->youtubeDl->download(
            $movie->getTrailer(),
            $this->generateFileName($movie, $movieFile)
        );
    }
}
