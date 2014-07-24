<?php

namespace Mihaeu\MovieManager\Factory;

use Mihaeu\MovieManager\FileSet;

/**
 * Class FileSetFactory
 *
 * @author Michael Haeuslmann (haeuslmann@gmail.com)
 */
class FileSetFactory
{
    /**
     * @var \SplFileObject
     */
    private $root;

    /**
     * %s is the base name of the movie so for `Lawrence of Arabia (1962).mkv` it would be `Lawrence of Arabia (1962)`
     */
    const POSTER_FORMAT             = '%s - Poster.jpg';
    const INFO_FILE_FORMAT          = '%s.ini';
    const IMDB_SCREENSHOT_FORMAT    = '%s - IMDb.png';

    /**
     * @param string $rootFolder
     */
    public function __construct($rootFolder)
    {
        $this->root = new \SplFileObject($rootFolder);
    }

    /**
     * @param  string $movieFilename
     *
     * @return FileSet
     */
    public function create($movieFilename)
    {
        $movieFile = new \SplFileObject($movieFilename);
        $fileSet = new FileSet();
        $fileSet->setMovieFile($movieFile);
        $fileSet->setParentFolder(new \SplFileObject($movieFile->getPath()));

        // for /tmp/Avatar.mkv the basePath would be /tmp/Avatar
        // which will be used only to construct the paths of other files
        $basePath = $movieFile->getPath().DIRECTORY_SEPARATOR.$movieFile->getBasename('.'.$movieFile->getExtension());

        $posterFilename = sprintf(self::POSTER_FORMAT, $basePath);
        if (file_exists($posterFilename)) {
            $fileSet->setPosterFile(new \SplFileObject($posterFilename));
        }

        $infoFileFilename = sprintf(self::INFO_FILE_FORMAT, $basePath);
        if (file_exists($infoFileFilename)) {
            $fileSet->setInfoFile(new \SplFileObject($infoFileFilename));
        }

        $imdbScreenshotFilename = sprintf(self::IMDB_SCREENSHOT_FORMAT);
        if (file_exists($imdbScreenshotFilename)) {
            $fileSet->setImdbScreenshotFile(new \SplFileObject($imdbScreenshotFilename));
        }

        return $fileSet;
    }
} 