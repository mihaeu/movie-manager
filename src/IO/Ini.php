<?php

namespace Mihaeu\MovieManager\IO;

/**
 * Class Ini
 *
 * @author Michael Haeuslmann (haeuslmann@gmail.com)
 */
class Ini
{
    /**
     * @var FilesystemInterface
     */
    private $filesystem;

    /**
     * @param FilesystemInterface $filesystem
     */
    public function __construct(FilesystemInterface $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * Read a .ini file into an associative array.
     *
     * @param  string $file
     * @param  bool   $sections
     *
     * @return array|false
     */
    public function read($file, $sections = true)
    {
        if (!file_exists($file)) {
            return false;
        }

        $iniContent = $this->filesystem->read($file);
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

        return parse_ini_string($saveIniContent, $sections);
    }

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
    public function write($file, $data)
    {
        $content = '';
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    if (!empty($value)) {
                        $key = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $key));
                        $content .= "[$key]\r\n";
                    }
                    foreach ($value as $subkey => $subvalue) {
                        // ignore deep nesting
                        if (!is_array($subvalue) && null !== $subvalue) {
                            $subkey = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $subkey));
                            if (is_numeric($subvalue)) {
                                $content .= "$subkey=$subvalue\r\n";
                            } else {
                                $subvalue = str_replace('"', "'", $subvalue);
                                $content .= "$subkey=\"$subvalue\"\r\n";
                            }
                        }
                    }
                    $content .= "\r\n";
                } else {
                    if (is_numeric($value)) {
                        $content .= "$key=$value\r\n";
                    } else {
                        $value = str_replace('"', "'", $value);
                        $content .= "$key=\"$value\"\r\n";
                    }
                }
            }
        } else {
            return false;
        }

        return false !== $this->filesystem->write($file, $content);
    }
}
