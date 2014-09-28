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
     * @var Config
     */
    private $config;

    /**
     * TMDb API Wrapper
     *
     * @var \TMDb
     */
    private $tmdb;

    /**
     * Constructor instantiates TMDb.
     *
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->tmdb = new \TMDb($this->config->get('tmdb-api-key'), 'en');
    }

    /**
     * @return \TMDb
     */
    public function GetTMDb()
    {
        return $this->tmdb;
    }

    /**
     * Handles movie related tasts like renaming, downloading the poster etc.
     *
     * @param  string $file    Movie file
     * @param  int    $imdbId  IMDb ID which is also used by TMDb.org **NOOOOOT anymore :(**
     * @param  bool   $isIMDb  Workaround to accept both IMDb and TMDb IDs.
     *
     * @return boolean          success flag
     */
    public function handleMovie($file, $imdbId, $isIMDb = false)
    {
        // by default (=webapp) this id is from tmdb
        $tmdbId = $imdbId;
        if ($isIMDb) {
            try {
                $tmdbId = TMDb::getTmdbIdFromImdbId('tt'.$imdbId);
            } catch (\Exception $e) {
                // can't recover without the ID, abort
                echo "$file couldn't be handled.\n";
                return false;
            }
        }

        $movie = $this->tmdb->getMovie($tmdbId);
        $imdbId = str_replace('tt', '', $movie['imdb_id']);

        $movieTitle = $this->convertMovieTitle($movie['title']);
        $movieYear = $this->convertMovieYear($movie['release_date']);

        $fileInfo = new \SplFileInfo($file);
        $filePath = $fileInfo->getPath();
        $fileExt = $fileInfo->getExtension();

        $movieFolder = realpath($filePath . '/..');

        // make sure the crucial parts are in order
        if (empty($movieTitle) || empty($imdbId) || empty($movieYear)) {
            return false;
        }

        $hasCorrectName = $this->renameFile($movieTitle, $movieYear, $file, $filePath, $fileExt);
        $hasIMDbLink = $this->createIMDbLink($movieTitle, $movieYear, $filePath, $movie);
        $hasPoster = $this->downloadMoviePoster($movieTitle, $movieYear, $filePath, $movie);
        $hasCorrectFolder = $this->renameMovieFolder($movieTitle, $movieYear, $filePath, $movieFolder);
        $hasScreenshot = $this->downloadIMDbScreenshot($imdbId, $movieTitle, $movieYear, $filePath);

        if ($hasCorrectName && $hasIMDbLink && $hasPoster && $hasCorrectFolder && $hasScreenshot) {
            return "$movieTitle ($movieYear)";
        } else {
            return false;
        }
    }

    /**
     * @param $originalTitle
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
     * @param $originalReleaseDate
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
     * @param $file
     * @param $filePath
     * @param $fileExt
     * @param int $maxRetries
     *
     * @return bool
     */
    public function renameFile($movieTitle, $movieYear, $file, $filePath, $fileExt, $maxRetries = 5)
    {
        $retries = 0;
        $success = rename($file, "$filePath/$movieTitle ($movieYear).$fileExt");
        while (!$success && ++$retries < $maxRetries) {
            echo 'Renaming ' . basename($file) . " unsuccessful. Retry $retries of $maxRetries.\n";
            usleep(100);
            $success = rename($file, "$filePath/$movieTitle ($movieYear).$fileExt");
        }
        return $success;
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
     * @param $filePath
     * @param $movieFolder
     * @param int $maxRetries
     *
     * @return bool
     */
    public function renameMovieFolder($movieTitle, $movieYear, $filePath, $movieFolder, $maxRetries = 5)
    {
        $newPath = "$movieFolder/$movieTitle ($movieYear)";
        if (is_dir($newPath)) {
            return true;
        }

        $retries = 0;
        $success = rename($filePath, $newPath);
        while (!$success && ++$retries < $maxRetries) {
            echo 'Renaming ' . basename($movieFolder) . " unsuccessful. Retry $retries of $maxRetries.\n";
            usleep(100);
            $success = rename($filePath, $newPath);
        }
        return $success;
    }

    /**
     * @param $movieTitle
     * @param $movieYear
     * @param \SplFileObject $movieFile
     *
     * @return bool|string
     */
    public function _renameMovieFolder($movieTitle, $movieYear,  \SplFileObject $movieFile)
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
     * Old version, please use createMovieInfo().
     *
     * @deprecated
     *
     * @param $movieTitle
     * @param $movieYear
     * @param $filePath
     * @param array $movie
     *
     * @return bool
     */
    public function createIMDbLink($movieTitle, $movieYear, $filePath, array $movie)
    {
        $url = $this->getIMDbLink($movie['imdb_id']);

        $movieIni = ['info' => []];
        // we don't want loose values without sections and we don't want empty sections,
        // because we're going to render it into the INI format
        foreach ($movie as $key => $value) {
            if (!is_array($value)) {
                $movieIni['info'][$key] = $value;
            } else {
                switch ($key) {
                    case 'genres':
                        foreach ($movie['genres'] as $genre) {
                            $movieIni['genres'][$genre['id']] = $genre['name'];
                        }
                        break;
                    case 'production_companies':
                        foreach ($movie['production_companies'] as $company) {
                            $movieIni['production_companies'][$company['id']] = $company['name'];
                        }
                        break;
                    case 'production_countries':
                        foreach ($movie['production_countries'] as $country) {
                            $movieIni['production_countries'][$country['iso_3166_1']] = $country['name'];
                        }
                        break;
                    case 'spoken_languages':
                        foreach ($movie['spoken_languages'] as $language) {
                            // language key no (=Norsk) is not allowed in .ini files
                            // so we cannot use the iso shortcode for the key
                            $movieIni['spoken_languages'][] = $language['name'];
                        }
                        break;
                }
            }
        }

        $iniArray = [
                'InternetShortcut' => [
                    'URL' => $url
                ]
            ] + $movieIni;

        $iniFile = "$filePath/".$this->convertMovieTitle($movieTitle)." ($movieYear) - IMDb.url";
        Writer::write($iniFile,$iniArray);

        // this is not fast, but it doesn't really matter for this app
        return Reader::read($iniFile) !== false;
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
     * @param $movieTitle
     * @param $movieYear
     * @param $filePath
     * @param array $movie
     *
     * @return bool
     *
     * @throws \TMDbException
     */
    public function downloadMoviePoster($movieTitle, $movieYear, $filePath, array $movie)
    {
        $posterSrc = $this->tmdb->getImageUrl(
            $movie['poster_path'],
            \TMDb::IMAGE_PROFILE,
            'original'
        );
        $srcExtension = substr(strrchr($posterSrc, '.'), 1);

        $destination = "$filePath/".$this->convertMovieTitle($movieTitle)." ($movieYear) - Poster.$srcExtension";
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
        // take a screenshot with PhantomJS and save it
        $output = '';
        $url = $this->getIMDbLink($imdbId);
        $script = __DIR__.'/../rasterize.js';
        $target = "$movieFolder/".$this->convertMovieTitle($movieTitle)." ($movieYear) - IMDb.png";
        $cmd = "phantomjs $script \"$url\" \"$target\"";
        system($cmd, $output);

        return 0 === $output;
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

        $iniFile = $movieDirectory.'/'.$this->convertMovieTitle($movie->getTitle()).' ('.$movie->getYear().') - IMDb.url';
        Writer::write($iniFile, $iniArray);

        // this is not fast, but it doesn't really matter for this app
        return Reader::read($iniFile) !== false;
    }
}
