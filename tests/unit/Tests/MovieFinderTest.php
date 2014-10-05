<?php

namespace Mihaeu\MovieManager\Tests;

use Mihaeu\MovieManager\Factory\FileSetFactory;
use Mihaeu\MovieManager\MovieFinder;
use Symfony\Component\Filesystem\Filesystem;

class MovieFinderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MovieFinder
     */
    private $finder;

    /**
     * @var string
     */
    private $testFolder;

    /**
     * @param array  $files
     * @param string $parent
     */
    public function createTestStructure($files, $parent)
    {
        $fs = new Filesystem();
        foreach ($files as $key => $file) {
            if (is_array($file)) {
                $this->createTestStructure($file, $parent.DIRECTORY_SEPARATOR.$key);
            } else {
                $testFile = $parent.DIRECTORY_SEPARATOR.$file.PHP_EOL;
                $fs->mkdir(dirname($testFile));
                $fs->touch($testFile);
            }
        }
    }

    public function setUp()
    {
        $files = [
            'Amour (2012)' => [
                'Amour (2012).avi',
                'Amour (2012).srt',
                'Amour (2012) - CD2.avi',
                'Amour (2012) - IMDb.png',
                'Amour (2012) - IMDb.url',
                'Amour (2012) - Poster.jpg',
            ],
            'Amour' => [
                'Amour (2012).avi',
            ],
            'Avatar' => [
                'Amour (2009).mkv',
            ],
            'awesome-movie.mp4',
            'illegal-format.mpeg',
            'a' => [
                'b' => [
                    'c' => 'deeply nested.avi'
                ]
            ]
        ];
        $this->testFolder = sys_get_temp_dir().DIRECTORY_SEPARATOR.microtime(true);
        $this->createTestStructure($files, $this->testFolder);
        $this->finder = $finder = new MovieFinder(new FileSetFactory($this->testFolder), ['mkv', 'avi', 'mp4']);
    }

    public function tearDown()
    {
        $fs = new Filesystem();
        $fs->remove($this->testFolder);
    }

    public function testFindsAllMovieFiles()
    {
        $fileSets = $this->finder->findMoviesInDir($this->testFolder);
        $this->assertCount(5, $fileSets);
    }
}
