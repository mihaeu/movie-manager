<?php

namespace Mihaeu\MovieManager;

/**
 * IniWriter
 *
 * Writes data to a file in .ini format.
 *
 * @author Michael Haeuslmann <haeuslmann@gmail.com>
 */
class Ini
{
    /**
     * Read data from an .ini file into a PHP array.
     * 
     * @param  String $toFile
     * @return Array
     */
    public static function read($fromFile)
    {
        if (!is_readable($fromFile)) {
            throw new \Exception("$fromFile is not readable.".PHP_EOL, 1);
        }

        return parse_ini_file($fromFile, true);
    }

    /**
     * Writes an array to an .ini file.
     *
     * @param Array $data
     * @param String $toFile
     * 
     * @return null
     */
    public static function write($data, $toFile = null)
    {
        // check if $toFile is set, because hitting IO on
        // recursive calls would be madness
        if ($toFile !== null && !is_writable($toFile)) {
            throw new \Exception("$toFile is not writable.".PHP_EOL, 1);
        }

        $result = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if (!empty($value)) {
                    $result[] = "\r\n[$key]";
                }
                foreach ($value as $subkey => $subvalue) {
                    $result[] = $subkey.'='.self::sanitizeValue($subvalue);
                }
            } else {
                $result[] = $key.'='.self::sanitizeValue($value);
            }
        }

        file_put_contents($toFile, implode("\r\n", $result));
    }

    /**
     * Sanitizes .ini values.
     *
     * @param  String
     * 
     * @return String
     */
    public static function sanitizeValue($value)
    {
        if (is_numeric($value)) {
            return $value;
        }

        return '"'.str_replace('"', '\"', $value).'"';
    }
}
