<?php

namespace Mihaeu\MovieManager\Ini;

/**
 * Class Reader
 *
 * @author Michael Haeuslmann (haeuslmann@gmail.com)
 */
class Reader
{
    /**
     * Read a .ini file into an associative array.
     *
     * @param  String $file
     * @param  bool   $sections
     *
     * @return array
     */
    public static function read($file, $sections = true)
    {
        $iniContent = @file_get_contents($file);
        if (false === $iniContent) {
            return false;
        }

        // problem: certain keys are not allowed in .ini files
        // find and fix them
        $replacements = [
            'no='       => 'no_=',
            'on='       => 'on_=',
            'yes='      => 'yes_=',
            'off='      => 'off_=',
            'true='     => 'true_=',
            'null='     => 'null_=',
            'none='     => 'none_=',
            'false='    => 'false_='
        ];
        $saveIniContent = str_replace(array_keys($replacements), array_values($replacements), $iniContent);

        return @parse_ini_string($saveIniContent, $sections);
    }
} 