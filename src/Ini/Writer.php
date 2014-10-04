<?php

namespace Mihaeu\MovieManager\Ini;

/**
 * Class Writer
 *
 * @author Michael Haeuslmann (haeuslmann@gmail.com)
 */
class Writer
{
    /**
     * Convert and write a php array into an .ini file.
     *
     * NOTE: Maximum depth is 1!
     *
     * @param  string $file
     * @param  array  $data
     *
     * @return bool
     */
    public static function write($file, $data)
    {
        $content = '';
        if (is_array($data))
        {
            foreach ($data as $key => $value)
            {
                if (is_array($value))
                {
                    if (!empty($value))
                    {
                        $key = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $key));
                        $content .= "[$key]\r\n";
                    }
                    foreach ($value as $subkey => $subvalue)
                    {
                        // ignore deep nesting
                        if (!is_array($subvalue) && null !== $subvalue)
                        {
                            $subkey = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $subkey));
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

        return false !== @file_put_contents($file, $content);
    }
}
