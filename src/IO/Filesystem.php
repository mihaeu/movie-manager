<?php

namespace Mihaeu\MovieManager\IO;

use League\Flysystem\Adapter\Local;
use Symfony\Component\Filesystem\Filesystem as SymfonfyFilesystem;

/**
 * Adapter for Symfony's Filesystem component
 *
 * @author  Michael Haeuslmann (haeuslmann@gmail.com)
 */
class Filesystem implements FilesystemInterface
{
    /**
     * @var SymfonfyFilesystem
     */
    private $filesystem;

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        $this->filesystem = new SymfonfyFilesystem();
    }

    /**
     * {@inheritdoc}
     */
    public function read($filename)
    {
        $adapter = new Local(dirname($filename));
        $differentFilesystem = new \League\Flysystem\Filesystem($adapter);
        $fileContent = $differentFilesystem->read(basename($filename));
        if (!is_string($fileContent)) {
            $fileContent = '';
        }
        return $fileContent;
    }

    /**
     * {@inheritdoc}
     */
    public function write($filename, $data)
    {
        $adapter = new Local(dirname($filename));
        $differentFilesystem = new \League\Flysystem\Filesystem($adapter);
        if (file_exists($filename)) {
            return $differentFilesystem->update(basename($filename), $data);
        }
        return $differentFilesystem->write(basename($filename), $data);
    }

    /**
     * {@inheritdoc}
     */
    public function exists($filename)
    {
        $this->filesystem->exists($filename);
    }

    /**
     * {@inheritdoc}
     */
    public function rename($filenameOld, $filenameNew, $overwrite = false)
    {
        $this->filesystem->rename($filenameOld, $filenameNew, $overwrite);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($filename)
    {
        $this->filesystem->remove($filename);
    }

    /**
     * {@inheritdoc}
     */
    public function createFile($filename)
    {
        $this->filesystem->touch($filename);
    }

    /**
     * {@inheritdoc}
     */
    public function createDirectory($filename)
    {
        $this->filesystem->mkdir($filename);
    }
}
