<?php declare(strict_types = 1);

namespace Mihaeu\MovieManager\IO;

interface FilesystemInterface
{
    /**
     * @param string $filename
     *
     * @return string
     */

    public function read($filename);
    /**
     * @param string $filename
     * @param mixed  $data
     *
     * @return mixed
     */
    public function write($filename, $data);

    /**
     * @param string $filename
     *
     * @return bool
     */
    public function exists($filename);

    /**
     * @param string $filenameOld
     * @param string $filenameNew
     * @param bool   $overwrite
     */
    public function rename($filenameOld, $filenameNew, $overwrite = false);

    /**
     * @param string $filename
     */
    public function delete($filename);

    /**
     * @param string $filename
     */
    public function createFile($filename);

    /**
     * @param string $filename
     */
    public function createDirectory($filename);
}
