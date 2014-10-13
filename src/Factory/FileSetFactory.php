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
     * %s is the base name of the movie so for `Lawrence of Arabia (1962).mkv`
     * it would be `Lawrence of Arabia (1962)`
     */
    const POSTER_FORMAT             = '%s - Poster.jpg';
    const INFO_FILE_FORMAT          = '%s - IMDb.url';
    const IMDB_SCREENSHOT_FORMAT    = '%s - IMDb.png';

    /**
     * @param string $rootFolder
     */
    public function __construct($rootFolder)
    {
        $this->root = new \SplFileInfo($rootFolder);
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
        $fileSet->setRootFolder($this->root);
        $fileSet->setParentFolder(new \SplFileInfo($movieFile->getPath()));

        $allFiles = iterator_to_array(new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($fileSet->getParentFolder()->getPathname()),
            \RecursiveIteratorIterator::SELF_FIRST,
            \RecursiveIteratorIterator::CATCH_GET_CHILD
        ));

        $fileSet->setSubtitleFiles(array_filter($allFiles, function (\SplFileInfo $file) {
            $fileExtension = strtolower($file->getExtension());
            $allowedfileExtensions = ['srt', 'sub', 'ass'];
            return in_array($fileExtension, $allowedfileExtensions);
        }));

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

        $imdbScreenshotFilename = sprintf(self::IMDB_SCREENSHOT_FORMAT, $basePath);
        if (file_exists($imdbScreenshotFilename)) {
            $fileSet->setImdbScreenshotFile(new \SplFileObject($imdbScreenshotFilename));
        }

        $fileSet->setMoviePartFiles(array_filter($allFiles, function (\SplFileInfo $file) use ($movieFile) {
            return $file->getExtension() === $movieFile->getExtension();
        }));

        return $fileSet;
    }
}
