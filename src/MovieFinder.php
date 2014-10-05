<?php

namespace Mihaeu\MovieManager;

use Mihaeu\MovieManager\Factory\FileSetFactory;

class MovieFinder
{
    /**
     * @var FileSetFactory
     */
    private $fileSetFactory;

    /**
     * @var string
     */
    private $allowedExtensionsRegex;

    /**
     * @param FileSetFactory $fileSetFactory
     * @param  array  $allowedFormats   Movie formats (extensions) which are allowed.
     */
    public function __construct(FileSetFactory $fileSetFactory, array $allowedFormats)
    {
        $this->fileSetFactory = $fileSetFactory;
        $this->allowedExtensionsRegex = '/(' . implode('|', $allowedFormats) . ')$/i';
    }

    /**
     * Looks recursively for movie files in a directory.
     *
     * @param  string $path Path which contains the movies.
     *
     * @return array matched movies
     */
    public function findMoviesInDir($path = '')
    {
        // only files
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));

        $fileSets = [];
        foreach ($files as $name => $file) {
            /** @var \SplFileInfo $file */
            if ($file->isReadable() && $this->isCorrectMovieFormat($file->getExtension()) && $this->isNotMultiPartMovie($name)) {
                $fileSets[] = $this->fileSetFactory->create($file->getRealPath());
            }
        }

        ksort($fileSets);
        return $fileSets;
    }

    /**
     * @param string $movieFileExtension
     *
     * @return bool
     */
    public function isCorrectMovieFormat($movieFileExtension)
    {
        return 1 === preg_match($this->allowedExtensionsRegex, $movieFileExtension);
    }

    /**
     * @param $filename

     * @return bool
     */
    public function isNotMultiPartMovie($filename)
    {
        return 1 !== preg_match('/.*CD[2-9]\.\w+$/', $filename);
    }
}
