<?php

namespace Mihaeu\MovieManager;

class MovieFinder
{
    /**
     * Looks recursively for movie files in a directory.
     *
     * @param  string $path             Path which contains the movies.
     * @param  array  $allowedFormats   Movie formats (extensions) which are allowed.
     *
     * @return array          matched movies
     */
    public function findMoviesInDir($path = '', array $allowedFormats)
    {
        if (!is_dir($path)) {
            return [];
        }

        $path = realpath($path);

        $filenameChunks = [];
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        $allowedExtensionsRegex = '/(' . implode('|', $allowedFormats) . ')/i';
        foreach ($files as $name => $file) {
            if (preg_match($allowedExtensionsRegex, $file->getExtension())
                && !preg_match('/.*CD2\.\w+$/', $name)
            ) {
                $filename = $file->getBasename();
                $matches = [];
                preg_match('/^(.*)\.[a-z0-9]{2,4}$/i', $filename, $matches);
                $filenameWithoutExt = $matches[1];

                $chunks = preg_replace('/[\:\-\._\(\)\[\]]/', ' ', $filenameWithoutExt);
                $chunks = preg_replace('/  +/', ' ', $chunks);

                $folder = $link = $screenshot = $poster = false;
                $formatOk = preg_match('/.+ \(\d{4}\)\.[a-z0-9]{2,4}/i', $filename);
                if ($formatOk) {
                    $folder = is_dir(realpath($file->getPath() . '/../' . $filenameWithoutExt));

                    $linkFile = $file->getPath() . '/' . $filenameWithoutExt . ' - IMDb.url';
                    $link = file_exists($linkFile);

                    $screenshotFile = $file->getPath() . '/' . $filenameWithoutExt . ' - IMDb.png';
                    $screenshot = file_exists($screenshotFile);

                    $posterFile = $file->getPath() . '/' . $filenameWithoutExt . ' - Poster.jpg';
                    $poster = file_exists($posterFile);
                }

                $filenameChunks[$file->getBasename()] = [
                    'name'          => $filename,
                    'fullname'      => $name,
                    'path'          => $file->getPath(),
                    'chunks'        => explode(' ', trim($chunks)),
                    'format'        => (bool)$formatOk,
                    'folder'        => $folder,
                    'link'          => $link,
                    'screenshot'    => $screenshot,
                    'poster'        => $poster
                ];
            }
        }

        ksort($filenameChunks);
        return $filenameChunks;
    }
}
