<?php

namespace Mihaeu\MovieManager;

use Mihaeu\Movie\Finder;

class MovieFinder
{
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
