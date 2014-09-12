<?php

namespace Mihaeu\MovieManager\Builder;

use Mihaeu\MovieManager\Ini\Reader;

/**
 * Class Html
 *
 * @package Mihaeu\MovieManager
 *
 * @author Michael Haeuslmann (haeuslmann@gmail.com)
 */
class Html
{
    /**
     * @var \Twig_Environment
     */
    private $templating;

    /**
     * @var string
     */
    private $templateDir;

    /**
     * @var array
     */
    private $movies = [];

    /**
     * @var string
     */
    private $moviesJson;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->templateDir = realpath(__DIR__ . '/../../templates');
        $this->setUpTemplating();
    }

    /**
     * Builds the HTML.
     *
     * @param  string $pathToMovies
     * @param  int    $limit
     * @return string
     */
    public function build($pathToMovies, $limit = -1)
    {
        $moviesJson     = [];
        $movieYears     = [];
        $movieGenres    = [];
        $movieLanguages = [];
        $movieCountries = [];

        $movieFolders = array_diff(scandir($pathToMovies), ['.', '..']);
        foreach ($movieFolders as $movieFolder) {
            if (--$limit === 0) {
                break;
            }

            $linkFile = "$pathToMovies/$movieFolder/$movieFolder - IMDb.url";
            $movieInfo = Reader::read($linkFile);
            if (false === $movieInfo) {
                continue;
            }

            if (isset($movieInfo['info'])) {
                $posterFile = str_replace('- IMDb.url', '- Poster.jpg', $linkFile);
                $cast = [];
                if (isset($movieInfo['cast'])) {
                    foreach ($movieInfo['cast'] as $id => $name) {
                        $character = '';
                        if (isset($movieInfo['character'][$id])) {
                            $character = $movieInfo['character'][$id];
                        }
                        $cast[] = [
                            'id' => $id,
                            'name' => $name,
                            'character' => $character
                        ];
                    }
                }

                $movie = [
                    'id'         => $movieInfo['info']['imdb_id'],
                    'title'      => $movieInfo['info']['title'],
                    'year'       => preg_replace('/(\d{4}).*/', '$1', $movieInfo['info']['release_date']),
                    'directors'  => isset($movieInfo['directors']) ? $movieInfo['directors'] : '',
                    'cast'       => isset($cast) ? $cast : '',
                    'rating'     => isset($movieInfo['info']['imdb_rating']) ? $movieInfo['info']['imdb_rating'] : $movieInfo['info']['vote_average'],
                    'length'     => $movieInfo['info']['runtime'],
                    'genre'      => isset($movieInfo['genres']) ? array_values($movieInfo['genres']) : [],
                    'languages'  => isset($movieInfo['spoken_languages']) ? array_values(
                                        $movieInfo['spoken_languages']
                                    ) : [],
                    'countries'  => isset($movieInfo['production_countries']) ? array_values(
                                        $movieInfo['production_countries']
                                    ) : [],
                    'plot'       => $movieInfo['info']['overview'],
                    'tagline'    => $movieInfo['info']['tagline']
                ];

                // the poster should not be part of the json file, so let's add that later
                $moviesJson[$movie['id']] = $movie;

                $movie['poster'] = $this->getScaledPosterAsBase64($posterFile, 400, 266);

                $movieYears[$movie['year']] = $movie['year'];
                foreach ($movie['genre'] as $genre) {
                    $movieGenres[$genre] = $genre;
                }
                if (isset($movieInfo['spoken_languages'])) {
                    foreach ($movieInfo['spoken_languages'] as $language) {
                        if (!empty($language)) {
                            $movieLanguages[$language] = $language;
                        }
                    }
                }
                foreach ($movie['countries'] as $country) {
                    $movieCountries[$country] = $country;
                }
                $this->movies[$movie['id']] = $movie;
            }
        }

        $this->moviesJson = str_replace("[\n\r]", ' ', json_encode($moviesJson, JSON_HEX_APOS | JSON_HEX_QUOT));
        asort($movieYears);
        return $this->templating->render(
            'collection.html.twig',
            [
                'years'      => $movieYears,
                'genres'     => $movieGenres,
                'languages'  => $movieLanguages,
                'countries'  => $movieCountries,
                'movies'     => $this->getMovies(),
                'moviesJson' => $this->getMoviesJson()
            ]
        );
    }

    /**
     * @return array
     */
    public function getMovies()
    {
        return $this->movies;
    }

    /**
     * @return string
     */
    public function getMoviesJson()
    {
        return $this->moviesJson;
    }


    /**
     * Scales a .jpeg image and returns the base64 encoded data.
     *
     * @param string $file
     * @param int    $newHeight
     * @param int    $newWidth
     *
     * @return string
     */
    public function getScaledPosterAsBase64($file, $newHeight = 400, $newWidth = 0)
    {
        if (!file_exists($file)) {
            return '';
        }

        list($width, $height) = getimagesize($file);
        if ($newWidth === 0) {
            $newWidth = $width / ($height / $newHeight);
        }

        $original = @imagecreatefromjpeg($file);
        if ($original === false) {
            return '';
        }
        $thumb = imagecreatetruecolor($newWidth, $newHeight);

        imagecopyresized($thumb, $original, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        // buffer output
        ob_start();
        imagejpeg($thumb);
        $img = ob_get_clean();

        return base64_encode($img);
    }

    /**
     * Sets up Twig environment, extensions and functions.
     */
    public function setUpTemplating()
    {
        $loader = new \Twig_Loader_Filesystem(
            [
                $this->templateDir.'/movie-collection',
                $this->templateDir.'/movie-collection/assets/css',
                $this->templateDir.'/movie-collection/assets/js'
            ]
        );
        $this->templating = new \Twig_Environment($loader, ['debug' => true]);
        $this->templating->addExtension(new \Twig_Extension_Debug());

        // add custom functions
        $this->templating->addFunction(
            new \Twig_SimpleFunction(
                'filedump', function ($file) {
                    $data = file_get_contents($this->templateDir.'/movie-collection/'.$file);
                    echo $data;
                }
            )
        );
        $this->templating->addFunction(
            new \Twig_SimpleFunction(
                'base64filedump', function ($file) {
                    $data = file_get_contents($this->templateDir.'/movie-collection/'.$file);
                    echo base64_encode($data);
                }
            )
        );
    }
}
