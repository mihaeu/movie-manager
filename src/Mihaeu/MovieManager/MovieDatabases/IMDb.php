<?php

namespace Mihaeu\MovieManager;

class IMDb
{
    /**
     * Fetches IMDb rating by parsing the site using RedPandas library.
     * 
     * @param  int      $id IMDb ID without the tt prefix
     * @return float
     */
    public static function getRatingFromIMDb($id)
    {
        $movie = new IMDb\Movie($_GET['id']);
        return $movie->getRating();
    }

    public static function check()
    {
        if (!isset($argv[1]) || !is_dir($argv[1])) {
            exit('Usage: php '.basename(__FILE__).' [MOVIES_FOLDER]'.PHP_EOL);
        }
        $pathToMovies = realpath($argv[1]);

        $movieFolders = array_diff(scandir($pathToMovies), ['.', '..']);
        foreach ($movieFolders as $movieFolder) {
            $linkFile = "$pathToMovies/$movieFolder/$movieFolder - IMDb.url";
            if (!file_exists($linkFile)) {
                "Skipping $linkFile\n";
                continue;
            }

            $movieInfo = Ini::read($linkFile);
            if (isset($movieInfo['info']['imdb_id'])) {
                $imdb_id = str_replace('tt', '', $movieInfo['info']['imdb_id']);
                $json_response = file_get_contents(
                    'http://kimai.mike-dev.info/get-imdb-rating/index.php?id='.$imdb_id
                );
                $response = json_decode($json_response, true);
                if ($response['rating'] > 0) {
                    echo "$linkFile - ".$response['rating']."\n";
                    $movieInfo['info']['imdb_rating'] = $response['rating'];

                    Ini::write($movieInfo, $linkFile);
                } else {
                    var_dump($response['rating']);
                }
            } else {
                echo "$linkFile failed\n";
            }
        }
    }
}
