<?php

namespace Mihaeu\MovieManager;

use Mihaeu\Movie\Finder;

class MovieFinder
{
    public function find($rootMovieFolder)
    {
        $finder = new Finder($rootMovieFolder);
        $movies = $finder->findMoviesInFolder();
        // var_dump($movies);
        
        $movieInfo = [];
        foreach ($movies as $movie) {
            $movieFile = $movie->getMovieFilename();
            $movieFolder = dirname($movieFile);
            $movieInfo[] = [
                'file'                      =>
                    $movieFile,
                
                // Armageddon (1994).mp4 should be in a folder called Armageddon (1994)
                'inProperMovieFolder'       =>
                    $this->movieInMovieFolder($movieFile),
                
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

    /**
     * Detects if the movie resides in the proper directory.
     * 
     * The folder should have the same filename as the movie, but without
     * the file extension. This of course is not a perfect test, because
     * my.awesome.movie.x264 would still be accepted if the filename is
     * my.awesome.movie.x264.mkv
     * 
     * @param  string $movieFilename Fully qualified filename e.g. '/movies/Avatar/avatar.mkv'
     * @return bool
     */
    public function movieInMovieFolder($movieFilename)
    {
        $file = new \SPLFileInfo($movieFilename);
        $dir = new \SPLFileInfo(dirname($movieFilename));
        return $dir->getBasename() === $file->getBasename('.'.$file->getExtension());
    }
}
