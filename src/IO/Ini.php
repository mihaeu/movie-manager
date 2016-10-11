<?php declare(strict_types = 1);

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
     * @var array
     */
    private $illegalKeyReplacements = [
        'no='       => 'no_=',
        'on='       => 'on_=',
        'yes='      => 'yes_=',
        'off='      => 'off_=',
        'true='     => 'true_=',
        'null='     => 'null_=',
        'none='     => 'none_=',
        'false='    => 'false_='
    ];

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

        $sanitizedIniContent = $this->sanitizeIniContent($iniContent);
        return parse_ini_string($sanitizedIniContent, $sections);
    }

    /**
     * Sanitizes keys in an .ini formated string.
     *
     * There are many varying implementations of the .ini format, in php it is
     * not possible to read arrays with illegal keys. This method changes the
     * keys.
     *
     * @param string $content
     *
     * @return string
     */
    public function sanitizeIniContent($content)
    {
        $illegalKeys = array_keys($this->illegalKeyReplacements);
        $legalReplacements = array_values($this->illegalKeyReplacements);
        return str_ireplace($illegalKeys, $legalReplacements, $content);
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
