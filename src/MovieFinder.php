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
     * @return array matched movies
     */
    public function findMoviesInDir()
    {
        $path = $this->fileSetFactory->getRoot()->getRealPath();

        // only files
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path),
            \RecursiveIteratorIterator::SELF_FIRST,
            \RecursiveIteratorIterator::CATCH_GET_CHILD
        );

        $fileSets = [];
        foreach ($files as $name => $file) {
            /** @var \SplFileInfo $file */
            if ($this->isCorrectMovieFormat($file->getExtension()) && $this->isNotMultiPartMovie($name) && $this->isNotTrailer($name)) {
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

    /**
     * @param $name
     *
     * @return bool
     */
    public function isNotTrailer($name)
    {
        return 1 !== preg_match('/[tT]railer\.[\w\d]+$/', $name);
    }
}
