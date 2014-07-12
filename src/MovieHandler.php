<?php

namespace Mihaeu\MovieManager;

use Mihaeu\MovieManager\Ini\Reader;
use Mihaeu\MovieManager\Ini\Writer;
use Mihaeu\MovieManager\MovieDatabase\TMDb;
use Symfony\Component\DomCrawler\Crawler;

class MovieHandler
{
    /**
     * @var Config
     */
    private $config;

    /**
     * TMDb API Wrapper
     *
     * @var Object
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
                $tmdbId = TMDb::getTmdbIdFromImdbId($imdbId);
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
        if (!$hasCorrectName) {
            echo '$hasCorrectName failer';
        }

        $hasIMDbLink = $this->createIMDbLink($movieTitle, $movieYear, $filePath, $imdbId, $movie);
        if (!$hasIMDbLink) {
            echo '$hasIMDbLink failer';
        }

        $hasPoster = $this->downloadMoviePoster($movieTitle, $movieYear, $filePath, $movie);
        if (!$hasPoster) {
            echo '$hasPoster failer';
        }

        $hasCorrectFolder = $this->renameMovieFolder($movieTitle, $movieYear, $filePath, $movieFolder);
        if (!$hasCorrectFolder) {
            echo '$hasCorrectFolder failer';
        }


        if ($hasCorrectName && $hasIMDbLink && $hasPoster && $hasCorrectFolder) {
            return "$movieTitle ($movieYear)";
        } else {
            return false;
        }
    }



    public function convertMovieTitle($originalTitle)
    {
        // : is not allowed in most OS, replace with - and add spaces
        $movieTitle = str_replace(':', ' - ', $originalTitle);

        // replace other illegal characters with spaces
        $movieTitle = preg_replace('/[\/\:\*\?"\\<>\|]/', ' ', $movieTitle);

        // trim spaces to one space max
        $movieTitle = preg_replace('/  +/', ' ', $movieTitle);

        return $movieTitle;
    }

    public function convertMovieYear($originalReleaseDate)
    {
        return date('Y', strtotime($originalReleaseDate));
    }

    private function renameFile($movieTitle, $movieYear, $file, $filePath, $fileExt, $maxRetries = 5)
    {
        $retries = 0;
        $success = @rename($file, "$filePath/$movieTitle ($movieYear).$fileExt");
        while (!$success && ++$retries < $maxRetries) {
            echo 'Renaming ' . basename($file) . " unsuccessful. Retry $retries of $maxRetries.\n";
            usleep(100);
            $success = @rename($file, "$filePath/$movieTitle ($movieYear).$fileExt");
        }
        return $success;
    }

    private function renameMovieFolder($movieTitle, $movieYear, $filePath, $movieFolder, $maxRetries = 5)
    {
        $newPath = "$movieFolder/$movieTitle ($movieYear)";
        if (is_dir($newPath)) {
            return true;
        }

        $retries = 0;
        $success = @rename($filePath, $newPath);
        while (!$success && ++$retries < $maxRetries) {
            echo 'Renaming ' . basename($movieFolder) . " unsuccessful. Retry $retries of $maxRetries.\n";
            usleep(100);
            $success = @rename($filePath, $newPath);
        }
        return $success;
    }

    private function createIMDbLink($movieTitle, $movieYear, $filePath, $imdbId, Array $movie)
    {
        $movie['info'] = [];
        // we don't want loose values without sections
        // and we don't want empty section
        // because we're going to render it into the INI format
        foreach ($movie as $key => $value) {
            if (!is_array($value)) {
                $movie['info'][$key] = $value;
                unset($movie[$key]);
            } else {
                switch ($key) {
                    case 'genres':
                        foreach ($movie['genres'] as $genre) {
                            $movie['genres'][$genre['id']] = $genre['name'];
                            unset($movie['genres'][$key]);
                        }
                        break;
                    case 'production_companies':
                        foreach ($movie['production_companies'] as $company) {
                            $movie['production_companies'][$company['id']] = $company['name'];
                            unset($movie['production_companies'][$key]);
                        }
                        break;
                    case 'production_countries':
                        foreach ($movie['production_countries'] as $country) {
                            $movie['production_countries'][$country['iso_3166_1']] = $country['name'];
                            unset($movie['production_countries'][$key]);
                        }
                        break;
                    case 'spoken_languages':
                        foreach ($movie['spoken_languages'] as $language) {
                            // language key no (=Norsk) is not allowed in .ini files
                            // so we cannot use the iso shortcode for the key
                            $movie['spoken_languages'][] = $language['name'];
                            unset($movie['spoken_languages'][$key]);
                        }
                        break;
                }
            }
        }

        $url = $this->getIMDbLink($imdbId);
        $iniArray = [
                'InternetShortcut' => [
                    'URL' => $url
                ]
            ] + $movie;

        $iniFile = "$filePath/$movieTitle ($movieYear) - IMDb.url";
        Writer::write($iniFile,$iniArray);

        // this is not fast, but it doesn't really matter for this app
        return Reader::read($iniFile) !== false;
    }

    private function getIMDbLink($imdbId)
    {
        if (strpos($imdbId, 'tt') === false) {
            return 'http://www.imdb.com/title/tt' . $imdbId;
        } else {
            return 'http://www.imdb.com/title/' . $imdbId;
        }
    }

    private function downloadMoviePoster($movieTitle, $movieYear, $filePath, Array $movie)
    {
        $posterSrc = $this->tmdb->getImageUrl(
            $movie['poster_path'],
            \TMDb::IMAGE_PROFILE,
            'original'
        );
        $srcExtension = substr(strrchr($posterSrc, '.'), 1);

        file_put_contents(
            "$filePath/$movieTitle ($movieYear) - Poster.$srcExtension",
            file_get_contents($posterSrc)
        );

        // take a screenshot with PhantomJS and save it
        $output = '';
        $url = $this->getIMDbLink($movie['imdb_id']);
        $script = __DIR__ . '/../../../rasterize.js';
        $target = "$filePath/$movieTitle ($movieYear) - IMDb.png";
        $cmd = "phantomjs $script \"$url\" \"$target\"";
        system($cmd, $output);

        return 0 === $output;
    }
}
