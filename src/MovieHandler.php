<?php

namespace Mihaeu\MovieManager;

use Mihaeu\MovieManager\Ini\Reader;
use Mihaeu\MovieManager\Ini\Writer;
use Mihaeu\MovieManager\MovieDatabase\TMDb;
use Symfony\Component\Filesystem\Filesystem;

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
     * @param $movieTitle
     * @param $movieYear
     * @param \SplFileObject $movieFile
     *
     * @return bool
     */
    public function renameMovie($movieTitle, $movieYear, \SplFileObject $movieFile)
    {
        $newName = $movieFile->getPath().DIRECTORY_SEPARATOR.$this->convertMovieTitle($movieTitle).' ('.$movieYear.').'.$movieFile->getExtension();

        if (file_exists($newName)) {
            return false;
        }

        $fs = new Filesystem();
        $fs->rename($movieFile->getRealPath(), $newName);
        return $newName;
    }

    /**
     * @param $movieTitle
     * @param $movieYear
     * @param \SplFileObject $movieFile
     *
     * @return bool|string
     */
    public function renameMovieFolder($movieTitle, $movieYear,  \SplFileObject $movieFile)
    {
        $newName = $movieFile->getPathInfo()->getPath().DIRECTORY_SEPARATOR.$this->convertMovieTitle($movieTitle).' ('.$movieYear.')';
        if (file_exists($newName)) {
            return false;
        }

        $fs = new Filesystem();
        $fs->rename($movieFile->getPath(), $newName);
        return $newName;
    }

    /**
     * @param $imdbId
     *
     * @return string
     */
    public function getIMDbLink($imdbId)
    {
        if (strpos($imdbId, 'tt') === false) {
            return 'http://www.imdb.com/title/tt' . $imdbId;
        } else {
            return 'http://www.imdb.com/title/' . $imdbId;
        }
    }

    /**
     * @param string $movieTitle
     * @param int    $movieYear
     * @param string $posterSrc
     * @param string $filePath
     *
     * @return bool
     *
     * @throws \TMDbException
     */
    public function downloadMoviePoster($movieTitle, $movieYear, $posterSrc, $filePath)
    {
        $srcExtension = substr(strrchr($posterSrc, '.'), 1);
        $destination = $filePath.DIRECTORY_SEPARATOR.$this->convertMovieTitle($movieTitle)." ($movieYear) - Poster.$srcExtension";
        file_put_contents($destination, file_get_contents($posterSrc));

        return file_exists($destination);
    }

    /**
     * Downloads a screenshot of the movie's IMDb page.
     *
     * @param string $imdbId
     * @param string $movieTitle
     * @param int    $movieYear
     * @param string $movieFolder
     *
     * @return bool
     */
    public function downloadIMDbScreenshot($imdbId, $movieTitle, $movieYear, $movieFolder)
    {
        // check if phantomjs exists in the right version
        if (exec('phantomjs --version') < 1) {
            return false;
        }

        $url = $this->getIMDbLink($imdbId);
        $script = __DIR__.'/../rasterize.js';
        $target = $movieFolder.DIRECTORY_SEPARATOR.$this->convertMovieTitle($movieTitle)." ($movieYear) - IMDb.png";
        $cmd = "phantomjs $script \"$url\" \"$target\"";
        $returnVal = false;
        exec($cmd, $returnVal);

        return false !== $returnVal;
    }

    /**
     * Creates the movie information file in .ini format, dressed up as a Windows
     * .url link.
     *
     * @param Movie $movie
     * @param $movieDirectory
     *
     * @return bool
     */
    public function createMovieInfo(Movie $movie, $movieDirectory)
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

        $iniFile = $movieDirectory.DIRECTORY_SEPARATOR.$this->convertMovieTitle($movie->getTitle()).' ('.$movie->getYear().') - IMDb.url';
        Writer::write($iniFile, $iniArray);

        // this is not fast, but it doesn't really matter for this app
        return Reader::read($iniFile) !== false;
    }
}
