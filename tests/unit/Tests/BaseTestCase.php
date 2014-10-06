<?php

namespace Mihaeu\MovieManager\Tests;

use Symfony\Component\Filesystem\Filesystem;

class BaseTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $testFolder;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    public function __construct()
    {
        $this->testFolder = sys_get_temp_dir().DIRECTORY_SEPARATOR.microtime(true);
    }

    /**
     * Creates a real test structure on the file system. All the files are empty and have a current timestamp (touch).
     *
     * @param array  $files  Nodes are either files (string) or subdirectories (array).
     * @param string $parent By default a random directory will be created in the system's temp directory.
     */
    protected function createTestStructure($files, $parent = null)
    {
        if (null === $parent) {
            $parent = $this->testFolder;
        }

        foreach ($files as $key => $file) {
            if (is_array($file)) {
                $this->createTestStructure($file, $parent.DIRECTORY_SEPARATOR.$key);
            } else {
                $testFile = $parent.DIRECTORY_SEPARATOR.$file;
                $this->getFilesystem()->mkdir(dirname($testFile));
                $this->getFilesystem()->touch($testFile);
            }
        }
    }

    /**
     * @param string $path
     */
    protected function destroyTestStructure($path = null)
    {
        if (null === $path) {
            $path = $this->testFolder;
        }
        $this->getFilesystem()->remove($path);
    }

    /**
     * @return Filesystem
     */
    protected function getFilesystem()
    {
        if (null === $this->filesystem) {
            $this->filesystem = new Filesystem();
        }
        return $this->filesystem;
    }
}
