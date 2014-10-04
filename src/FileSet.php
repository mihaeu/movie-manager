<?php

namespace Mihaeu\MovieManager;

/**
 * Class FileSet
 *
 * @author Michael Haeuslmann (haeuslmann@gmail.com)
 */
class FileSet
{
    /**
     * @var \SplFileObject
     */
    private $movieFile;

    /**
     * @var array|\SplFileObject[]
     */
    private $moviePartFiles;

    /**
     * @var \SplFileInfo
     */
    private $parentFolder;

    /**
     * @var \SplFileInfo
     */
    private $rootFolder;

    /**
     * @var \SplFileObject
     */
    private $infoFile;

    /**
     * @var \SplFileObject
     */
    private $posterFile;

    /**
     * @var \SplFileObject
     */
    private $imdbScreenshotFile;

    /**
     * @var array|\SplFileObject[]
     */
    private $subtitleFiles;

    /**
     * @var int
     */
    private $filesize;

    /**
     * @return int
     */
    public function getFilesize()
    {
        return $this->filesize;
    }

    /**
     * @param int $filesize
     */
    public function setFilesize($filesize)
    {
        $this->filesize = $filesize;
    }

    /**
     * @return \SplFileObject
     */
    public function getImdbScreenshotFile()
    {
        return $this->imdbScreenshotFile;
    }

    /**
     * @param \SplFileObject $imdbScreenshotFile
     */
    public function setImdbScreenshotFile($imdbScreenshotFile)
    {
        $this->imdbScreenshotFile = $imdbScreenshotFile;
    }

    /**
     * @return \SplFileObject
     */
    public function getInfoFile()
    {
        return $this->infoFile;
    }

    /**
     * @param \SplFileObject $infoFile
     */
    public function setInfoFile($infoFile)
    {
        $this->infoFile = $infoFile;
    }

    /**
     * @return \SplFileObject
     */
    public function getMovieFile()
    {
        return $this->movieFile;
    }

    /**
     * @param \SplFileObject $movieFile
     */
    public function setMovieFile($movieFile)
    {
        $this->movieFile = $movieFile;
    }

    /**
     * @return array|\SplFileObject[]
     */
    public function getMoviePartFiles()
    {
        return $this->moviePartFiles;
    }

    /**
     * @param array|\SplFileObject[] $moviePartFiles
     */
    public function setMoviePartFiles($moviePartFiles)
    {
        $this->moviePartFiles = $moviePartFiles;
    }

    /**
     * @return \SplFileInfo
     */
    public function getParentFolder()
    {
        return $this->parentFolder;
    }

    /**
     * @param \SplFileInfo $parentFolder
     */
    public function setParentFolder(\SplFileInfo $parentFolder)
    {
        $this->parentFolder = $parentFolder;
    }

    /**
     * @return \SplFileObject
     */
    public function getPosterFile()
    {
        return $this->posterFile;
    }

    /**
     * @param \SplFileObject $posterFile
     */
    public function setPosterFile($posterFile)
    {
        $this->posterFile = $posterFile;
    }

    /**
     * @return \SplFileInfo
     */
    public function getRootFolder()
    {
        return $this->rootFolder;
    }

    /**
     * @param \SplFileInfo $rootFolder
     */
    public function setRootFolder(\SplFileInfo $rootFolder)
    {
        $this->rootFolder = $rootFolder;
    }

    /**
     * @return array|\SplFileObject[]
     */
    public function getSubtitleFiles()
    {
        return $this->subtitleFiles;
    }

    /**
     * @param array|\SplFileObject[] $subtitleFiles
     */
    public function setSubtitleFiles($subtitleFiles)
    {
        $this->subtitleFiles = $subtitleFiles;
    }

    /**
     * Checks if the movie has the proper name (`%MOVIE_TITLE (%MOVIE_YEAR)`).
     *
     * Proper check is only possible if movie title and year is available.
     *
     * @param string $movieTitle
     * @param string $movieYear
     *
     * @return bool
     */
    public function hasCorrectName($movieTitle = null, $movieYear = null)
    {
        if (null === $this->getMovieFile()) {
            return false;
        }

        $movieBasename = $this->getMovieFile()->getBasename('.'.$this->getMovieFile()->getExtension());
        if (null !== $movieTitle && null !== $movieYear) {
            return "$movieTitle ($movieYear)" === $movieBasename;
        }
        return 1 === preg_match('/^.+ \(\d\d\d\d\)$/', $movieBasename);
    }

    /**
     * Checks if the movie file resides in the correct folder.
     *
     * @return bool
     */
    public function hasCorrectParentFolder()
    {
        if (null === $this->getMovieFile() || null === $this->getParentFolder()) {
            return false;
        }

        $movieBasename = $this->getMovieFile()->getBasename('.'.$this->getMovieFile()->getExtension());
        return $this->hasCorrectName()
            && $this->getRootFolder()->getPathname() === $this->getParentFolder()->getPath()
            && $this->getParentFolder()->getBasename() === $movieBasename;
    }
}
