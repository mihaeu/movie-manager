<?php

namespace Mihaeu\MovieManager;

class MovieInfoChecker
{
    public static function check()
    {
        $tmdb = new \TMDb('20f832e20d298376fcd4bc6a3b262108', 'en');

        if (!isset($argv[1]) || !is_dir($argv[1])) {
            exit('Usage: php '.basename(__FILE__).' [MOVIES_FOLDER]'.PHP_EOL);
        }
        $movieRootFolder = $argv[1];

        // $movieRootFolder = '/media/media/videos/movies';
        $movieFolders = array_diff(scandir($movieRootFolder), ['.', '..']);
        $movies = [];
        foreach ($movieFolders as $movieFolder)
        {

            $linkFile = "$movieRootFolder/$movieFolder/$movieFolder - IMDb.url";
            if ( ! file_exists($linkFile))
            {
                printf("$movieFolder does not have a link file.\n\n");
                continue;
            }

            $movieInfo = parse_ini_file($linkFile, true);
            if ( ! isset($movieInfo['info']['imdb_id']))
            {
                printf("$movieFolder has no imdb_id.\n");
            }

            if ( ! isset($movieInfo['info']['overview']))
            {
                printf("$movieFolder has no overview.\n");
            }

            if ( ! isset($movieInfo['info']['vote_average']))
            {
                printf("$movieFolder has no vote_average.\n");
            }

            if ( ! isset($movieInfo['info']['release_date']))
            {
                printf("$movieFolder has no release_date.\n");
            }

            if ( ! isset($movieInfo['info']['title']))
            {
                printf("$movieFolder has no title.\n");
            }

            if ( ! isset($movieInfo['info']['vote_count']))
            {
                printf("$movieFolder has no vote_count.\n");
            }

            if ( ! isset($movieInfo['info']['runtime']))
            {
                printf("$movieFolder has no runtime.\n");
            }

            if ( ! isset($movieInfo['genres']))
            {
                printf("$movieFolder has no genres.\n");
            }
            
            if ( ! isset($movieInfo['spoken_languages']))
            {
                printf("$movieFolder has no spoken_languages.\n");
            }
            
            if ( ! isset($movieInfo['production_countries']))
            {
                printf("$movieFolder has no production_countries.\n");
            }

            if ( ! isset($movieInfo['cast']) && ! isset($movieInfo['directors']))
            {
                printf("$movieFolder has no cast or crew information.\n");
                $credit = $tmdb->getMovieCast($movieInfo['info']['id']);

                if (isset($credit['cast']))
                {
                    foreach ($credit['cast'] as $cast)
                    {
                        $movieInfo['cast'][$cast['id']] = $cast['name'];
                        $movieInfo['character'][$cast['id']] = $cast['character'];
                    }
                }

                if (isset($credit['crew']))
                {
                    foreach ($credit['crew'] as $crew)
                    {
                        if ($crew['job'] === 'Director')
                        {
                            $movieInfo['directors'][$crew['id']] = $crew['name'];
                        }
                    }
                }

                IMDbRater::writeIniFile($movieInfo, $linkFile);
            }   
        }
    }
}