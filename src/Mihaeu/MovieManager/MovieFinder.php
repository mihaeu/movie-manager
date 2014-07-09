<?php

namespace Mihaeu\MovieManager;

use Mihaeu\Movie\Finder;

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
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));
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

    public function find($rootMovieFolder)
    {
        $finder = new Finder($rootMovieFolder);
        $movies = $finder->findMoviesInFolder();

        $movieInfo = [];
        foreach ($movies as $movie) {
            $movieFile = $movie->getMovieFilename();
            $movieFolder = dirname($movieFile);
            $movieInfo[] = [
                'file'                      =>
                    $movieFile,
                
                // Armageddon (1994).mp4 should be in a folder called Armageddon (1994)
                'inProperMovieFolder'       =>
                    $movieFolder === basename($movieFile, $movie->getMovieFileExtension()),
                
                // is the movie in a separate folder or a plain file under the root directory (because we want to have all the movies in a separate folder) (*)
                'inSeparateFolderUnderRoot' =>
                    dirname($movieFolder) === $rootMovieFolder,
                
                // does the movie have one or more subtitles
                'hasSubtitle'               =>
                    $movie->hasSubtitle(),
                
                // does the movie have a release info file (.nfo)
                'hasNfoFile'                =>
                    file_exists($movieFolder.DIRECTORY_SEPARATOR.$movie->getName().'.nfo'),
                
                // does the movie have a .url file and is it in our format with all the information (*)
                
                // does the movie have a poster (*)
                
                // what resolution is the movie in
                
                // what other files are in the movie folder

            ];
        }

        return $movieInfo;
    }

    public function movieInMovieFolder($movieFilename, $rootMovieFolder)
    {
        return false;
    }
}
