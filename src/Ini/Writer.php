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
     * @param  string $file
     * @param  array  $data
     *
     * @return bool
     */
    public static function write($file, $data)
    {
        $content = '';
        if (!is_array($data))
        {
            return false;
        }

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if ( ! empty($value)) {
                    $content .= "[$key]\r\n";
                }
                foreach ($value as $subKey => $subValue) {
                    if (is_array($subValue)) {
                        if ( ! empty($value)) {
                            $content .= "[$key\\$subKey]\r\n";
                        }
                        foreach ($subValue as $subSubKey => $subSubValue) {
                            self::formatIniKeyValue($subSubKey, $subSubValue);
                        }
                        $content .= "\r\n";
                    } else {
                        self::formatIniKeyValue($subKey, $subValue);
                    }
                }
                $content .= "\r\n";
            } else {
                $content .= self::formatIniKeyValue($key, $value);
            }
        }

        return false !== @file_put_contents($file, $content);
    }

    /**
     * Escapes/quotes values if necessary.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return string
     */
    public static function formatIniKeyValue($key, $value)
    {
        return $key.'='.is_numeric($value) ? $value : '"'.str_replace('"', "'", $value).'"';
    }
}