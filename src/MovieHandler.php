<?php

namespace Mihaeu\MovieManager;

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
    /**
     * @var FilesystemInterface
     */
    private $filesystem;

    public function __construct(FilesystemInterface $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * @param string $originalTitle
     *
     * @return mixed
     */
    public function convertMovieTitle($originalTitle)
    {
        // : is not allowed in most OS, replace with - and add spaces
        $movieTitle = str_replace(':', ' - ', $originalTitle);

        // replace other illegal characters with spaces
        $movieTitle = str_replace(['/', '*', '?', '"', '\\', '<', '>', '|'], ' ', $movieTitle);

        // trim spaces to one space max
        $movieTitle = preg_replace('/  +/', ' ', $movieTitle);

        return $movieTitle;
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
        file_put_contents($destination, file_get_contents($movie->getPosterUrl()));

        return file_exists($destination);
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
        // check if phantomjs exists in the right version
        if (exec('phantomjs --version') < 1) {
            return false;
        }

        $url = $this->getIMDbLink($movie->getImdbId());
        $script = __DIR__.'/../rasterize.js';
        $target = $this->generateFileName($movie, $movieFile, ' - IMDb.png');
        $cmd = "phantomjs $script \"$url\" \"$target\"";
        $returnVal = 1;
        $output = [];
        exec($cmd, $output, $returnVal);

        return 0 === $returnVal;
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
     * @param string         $suffix
     *
     * @return string
     */
    public function generateFileName(Movie $movie, \SplFileInfo $movieFile, $suffix = '')
    {
        return $movieFile->getPath()
            .DIRECTORY_SEPARATOR
            .$this->convertMovieTitle($movie->getTitle()).' ('.$movie->getYear().')'.$suffix;
    }

    /**
     * @param \SplFileInfo $movieFile
     * @param string       $targetDirectory
     *
     * @return string
     */
    public function moveTo(\SplFileInfo $movieFile, $targetDirectory)
    {
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
        // check if youtube-dl exists in the right version
        if (exec('youtube-dl --version') < 1 || null === $movie->getTrailer()) {
            return false;
        }

        $filesBefore = scandir($movieFile->getPath());
        $cmd = 'youtube-dl "'.$movie->getTrailer().'" --output "'
            .$this->generateFileName($movie, $movieFile).' - Trailer.%(ext)s"';
        $returnVal = false;
        system($cmd, $returnVal);
        $filesAfter = scandir($movieFile->getPath());
        $trailerFilename = $movieFile->getPath().'/'.array_pop(array_diff($filesAfter, $filesBefore));
        return file_exists($trailerFilename);
    }
}
