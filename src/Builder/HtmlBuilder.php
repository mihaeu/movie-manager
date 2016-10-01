<?php

namespace Mihaeu\MovieManager\Builder;

use Mihaeu\MovieManager\Movie;

/**
 * Class HtmlBuilder
 *
 * @package Mihaeu\MovieManager
 *
 * @author Michael Haeuslmann (haeuslmann@gmail.com)
 */
class HtmlBuilder implements Builder
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
     * @var bool
     */
    private $buildWithPosters;

    /**
     * @param bool $buildWithPosters
     */
    public function __construct($buildWithPosters = true)
    {
        $this->templateDir = realpath(__DIR__.'/../../templates');
        $this->setUpTemplating();

        $this->buildWithPosters = $buildWithPosters;
    }

    /**
     * @inheritdoc
     *
     * @throws \Exception
     */
    public function build(array $movies, string $movieRootPath)
    {
        $posters = [];
        if ($this->buildWithPosters) {
            foreach ($movies as $movie) {
                /** @var Movie $movie */
                $posterPath = "$movieRootPath/$movie/$movie - Poster.jpg";
                $posters[$movie->getId()] = $this->getBase64Poster($posterPath);
            }
        }
        return $this->templating->render('collection.html.twig', [
            'movies'    => $movies,
            'posters'   => $posters,
            'languages' => $this->extractLanguages($movies),
            'genres'    => $this->extractGenres($movies),
            'countries' => $this->extractCountries($movies),
            'years'     => $this->extractYears($movies),
            'json'      => $this->generateJson($movies)
        ]);
    }

    /**
     * @param array $movies
     *
     * @return array|Movie[]
     */
    public function extractLanguages(array $movies)
    {
        $languages = [];
        foreach ($movies as $movie) {
            /** @var Movie $movie */
            if (null === $movie->getSpokenLanguages()) {
                continue;
            }
            foreach ($movie->getSpokenLanguages() as $language) {
                $languages[$language] = 0;
            }
        }
        return array_keys($languages);
    }

    /**
     * @param array $movies
     *
     * @return array|Movie[]
     */
    public function extractGenres(array $movies)
    {
        $genres = [];
        foreach ($movies as $movie) {
            /** @var Movie $movie */
            if (null === $movie->getGenres()) {
                continue;
            }
            foreach ($movie->getGenres() as $genre) {
                $genres[$genre] = 0;
            }
        }
        return array_keys($genres);
    }

    /**
     * @param array $movies
     *
     * @return array|Movie[]
     */
    public function extractCountries(array $movies)
    {
        $countries = [];
        foreach ($movies as $movie) {
            /** @var Movie $movie */
            if (null === $movie->getProductionCountries()) {
                continue;
            }
            foreach ($movie->getProductionCountries() as $country) {
                $countries[$country] = 0;
            }
        }
        return array_keys($countries);
    }

    /**
     * @param array $movies
     *
     * @return array|Movie[]
     */
    public function extractYears(array $movies)
    {
        $years = [];
        foreach ($movies as $movie) {
            /** @var Movie $movie */
            if (null === $movie->getYear()) {
                continue;
            }
            $years[$movie->getYear()] = 0;
        }
        return array_keys($years);
    }

    /**
     * @param array|Movie[] $movies
     *
     * @return string
     */
    public function generateJson(array $movies)
    {
        $jsonData = [];
        foreach ($movies as $movie) {
            /** @var Movie $movie */
            $jsonData[$movie->getId()] = [
                'id'            => $movie->getId(),
                'title'         => $movie->getTitle(),
                'year'          => $movie->getYear(),
                'rating'        => $movie->getImdbRating(),
                'length'        => $movie->getRuntime(),
                'genre'         => null !== $movie->getGenres()              ? array_values($movie->getGenres()) : [],
                'languages'     => null !== $movie->getSpokenLanguages()     ? array_values($movie->getSpokenLanguages()) : [],
                'countries'     => null !== $movie->getProductionCountries() ? array_values($movie->getProductionCountries()) : [],
                'cast'          => null !== $movie->getCast()                ? array_values($movie->getCast()) : [],
                'directors'     => null !== $movie->getDirectors()           ? array_values($movie->getDirectors()) : []
            ];
        }
        return str_replace("[\n\r]", ' ', json_encode($jsonData, JSON_HEX_APOS | JSON_HEX_QUOT));
    }

    /**
     * @param string $file
     * @param int    $newHeight
     * @param int    $newWidth
     *
     * @return string
     */
    public function getScaledPoster($file, $newHeight = 400, $newWidth = 0) : string
    {
        if ($this->imageMagickIsInstalled()) {
            $tempFile = sys_get_temp_dir().'/'.uniqid('asda', true);
            $command = 'convert "'.$file.'" -resize '.$newWidth.'x'.$newHeight.' "'.$tempFile.'"';
            exec($command);
            return file_get_contents($tempFile);
        }

        if ($this->gdLibraryIsInstalled()) {
            list($width, $height) = getimagesize($file);
            if ($newWidth === 0) {
                $newWidth = (int) ($width / ($height / $newHeight));
            }

            $original = imagecreatefromjpeg($file);
            if ($original === false) {
                return '';
            }
            $thumb = imagecreatetruecolor($newWidth, $newHeight);

            imagecopyresized($thumb, $original, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

            // buffer output
            ob_start();
            imagejpeg($thumb);

            return ob_get_clean();
        }

        return '';
    }

    /**
     * @param string $posterPath
     *
     * @return string|null
     */
    public function getBase64Poster($posterPath)
    {
        return base64_encode($this->getScaledPoster($posterPath, 400, 266));
    }

    /**
     * Sets up Twig environment, extensions and functions.
     */
    private function setUpTemplating()
    {
        $loader = new \Twig_Loader_Filesystem([
            $this->templateDir.'/movie-collection',
            $this->templateDir.'/movie-collection/assets/css',
            $this->templateDir.'/movie-collection/assets/js'
        ]);
        $this->templating = new \Twig_Environment($loader, ['debug' => true]);
        $this->templating->addExtension(new \Twig_Extension_Debug());

        // add custom functions
        $this->templating->addFunction(
            new \Twig_SimpleFunction(
                'filedump',
                function ($file) {
                    $data = file_get_contents($this->templateDir.'/movie-collection/'.$file);
                    echo $data;
                }
            )
        );
        $this->templating->addFunction(
            new \Twig_SimpleFunction(
                'base64filedump',
                function ($file) {
                    $data = file_get_contents($this->templateDir.'/movie-collection/'.$file);
                    echo base64_encode($data);
                }
            )
        );
    }

    /**
     * @return bool
     */
    private function gdLibraryIsInstalled() : bool
    {
        return function_exists('imagecreatefromjpeg');
    }

    /**
     * @return bool
     */
    private function imageMagickIsInstalled() : bool
    {
        exec('convert -version', $output, $returnVar);
        return $returnVar === 0;
    }
}
