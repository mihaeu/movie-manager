<?php

namespace Mihaeu\MovieManager;

/**
 * IniWriter
 *
 * Writes data to a file in .ini format.
 *
 * @author Michael Haeuslmann <haeuslmann@gmail.com>
 */
class IniWriter
{
    /**
     * Writes an array to an .ini file.
     *
     * @param Array $data
     * @param String $toFile
     * 
     * @return null
     */
    public static function write(Array $data, $toFile)
    {
        if (!is_writable($toFile)) {
            throw new Exception("$toFile is not writable.".PHP_EOL, 1);
        }

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

        file_put_contents($toFile, $content);
    }
}