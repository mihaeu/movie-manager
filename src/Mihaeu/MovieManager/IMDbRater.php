<?php

namespace Mihaeu\MovieManager;

/**
 * Class IMDbRater
 *
 * @package Mihaeu\MovieManager
 *
 * @author Michael Haeuslmann (haeuslmann@gmail.com)
 */
class IMDbRater
{
    /**
     * @TODO Copied in from a cmd line script and still needs major rework.
     */
    public static function check()
    {
        if (!isset($argv[1]) || !is_dir($argv[1])) {
            exit('Usage: php '.basename(__FILE__).' [MOVIES_FOLDER]'.PHP_EOL);
        }
        $pathToMovies = realpath($argv[1]);

        $movieFolders = array_diff(scandir($pathToMovies), ['.', '..']);
        foreach ($movieFolders as $movieFolder)
        {
            $linkFile = "$pathToMovies/$movieFolder/$movieFolder - IMDb.url";
            if ( ! file_exists($linkFile))
            {
                echo  "Skipping $linkFile\n";
                continue;
            }

            $movieInfo = parse_ini_file($linkFile, true);
            if (isset($movieInfo['info']['imdb_id']))
            {
                $imdb_id = str_replace('tt', '', $movieInfo['info']['imdb_id']);
                $json_response = file_get_contents(
                    'http://kimai.mike-dev.info/get-imdb-rating/index.php?id='.$imdb_id
                );
                $response = json_decode($json_response, true);
                if ($response['rating'] > 0)
                {
                    echo "$linkFile - ".$response['rating']."\n";
                    $movieInfo['info']['imdb_rating'] = $response['rating'];
                    self::writeIniFile($movieInfo, $linkFile);
                }
                else
                {
                    var_dump($response['rating']);
                }
            }
            else
            {
                echo "$linkFile failed\n";
            }
        }
    }

    /**
     * Parses a PHP array to INI format and writes the result to a file.
     *
     * @param  Array $data
     * @param  String $path
     *
     * @return mixed
     */
    public static function writeIniFile($data, $path)
    {
        $content = '';
        if (is_array($data))
        {
            foreach ($data as $key => $value)
            {
                if (is_array($value))
                {
                    if ( ! empty($value))
                    {
                        $content .= "[$key]\r\n";
                    }
                    foreach ($value as $subkey => $subvalue)
                    {
                        if (is_array($subvalue))
                        {
                            if ( ! empty($value))
                            {
                                $content .= "[$key\\$subkey]\r\n";
                            }
                            foreach ($subvalue as $subsubkey => $subsubvalue)
                            {
                                if (is_numeric($subsubvalue))
                                {
                                    $content .= "$subsubkey=$subsubvalue\r\n";
                                }
                                else
                                {
                                    $subsubvalue = str_replace('"', "'", $subsubvalue);
                                    $content .= "$subsubkey=\"$subsubvalue\"\r\n";
                                }
                            }
                            $content .= "\r\n";
                        }
                        else
                        {
                            if (is_numeric($subvalue))
                            {
                                $content .= "$subkey=$subvalue\r\n";
                            }
                            else
                            {
                                $subvalue = str_replace('"', "'", $subvalue);
                                $content .= "$subkey=\"$subvalue\"\r\n";
                            }
                        }
                    }
                    $content .= "\r\n";
                }
                else
                {
                    if (is_numeric($value))
                    {
                        $content .= "$key=$value\r\n";
                    }
                    else
                    {
                        $value = str_replace('"', "'", $value);
                        $content .= "$key=\"$value\"\r\n";
                    }
                }
            }
        }
        else
        {
            return false;
        }

        file_put_contents($path, $content);
    }
}