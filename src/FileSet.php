<?php

namespace Mihaeu\MovieManager;

/**
 * Class FileSet
 *
 * @author Michael Haeuslmann (haeuslmann@gmail.com)
 */
class FileSet
{
    const B_TO_KB  = 1024;
    const KB_TO_MB = 1024;

    /**
     * @var \SplFileInfo
     */
    private $movieFile;

    /**
     * @var array|\SplFileInfo[]
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
     * @var \SplFileInfo
     */
    private $infoFile;

    /**
     * @var \SplFileInfo
     */
    private $posterFile;

    /**
     * @var \SplFileInfo
     */
    private $imdbScreenshotFile;

    /**
     * @var array|\SplFileInfo[]
     */
    private $subtitleFiles;

    /**
     * @var int
     */
    private $filesize;

    /**
     * @return int filesize in MB
     */
    public function getFilesize()
    {
        if (null === $this->filesize) {
            $this->filesize = $this->movieFile->getSize() / self::B_TO_KB / self::KB_TO_MB;
        }
        return $this->filesize;
    }

    /**
     * @return \SplFileInfo
     */
    public function getImdbScreenshotFile()
    {
        return $this->imdbScreenshotFile;
    }

    /**
     * @param \SplFileInfo $imdbScreenshotFile
     */
    public function setImdbScreenshotFile($imdbScreenshotFile)
    {
        $this->imdbScreenshotFile = $imdbScreenshotFile;
    }

    /**
     * @return \SplFileInfo
     */
    public function getInfoFile()
    {
        return $this->infoFile;
    }

    /**
     * @param \SplFileInfo $infoFile
     */
    public function setInfoFile($infoFile)
    {
        $this->infoFile = $infoFile;
    }

    /**
     * @return \SplFileInfo
     */
    public function getMovieFile()
    {
        return $this->movieFile;
    }

    /**
     * @param \SplFileInfo $movieFile
     */
    public function setMovieFile($movieFile)
    {
        $this->movieFile = $movieFile;
    }

    /**
     * @return array|\SplFileInfo[]
     */
    public function getMoviePartFiles()
    {
        return $this->moviePartFiles;
    }

    /**
     * @param array|\SplFileInfo[] $moviePartFiles
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
     * @return \SplFileInfo
     */
    public function getPosterFile()
    {
        return $this->posterFile;
    }

    /**
     * @param \SplFileInfo $posterFile
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
     * @return array|\SplFileInfo[]
     */
    public function getSubtitleFiles()
    {
        return $this->subtitleFiles;
    }

    /**
     * @param array|\SplFileInfo[] $subtitleFiles
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
        $rootSameAsParentOfMovie = $this->getRootFolder()->getRealPath() === $this->getParentFolder()->getPath();
        $movieNameSameAsFolder = $this->getParentFolder()->getBasename() === $movieBasename;
        return $this->hasCorrectName()
            && $rootSameAsParentOfMovie
            && $movieNameSameAsFolder;
    }
}
