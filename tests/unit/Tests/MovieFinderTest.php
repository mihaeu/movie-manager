<?php declare(strict_types = 1);

namespace Mihaeu\MovieManager\Tests;

use Mihaeu\MovieManager\Factory\FileSetFactory;
use Mihaeu\MovieManager\MovieFinder;

/**
 * @covers Mihaeu\MovieManager\MovieFinder
 *
 * @covers Mihaeu\MovieManager\Factory\FileSetFactory
 * @covers Mihaeu\MovieManager\FileSet
 */
class MovieFinderTest extends BaseTestCase
{
    /**
     * @var MovieFinder
     */
    private $finder;

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
            'Benghazi (2016)- Trailer.mp4',
            'awesome-movie.mp4',
            'illegal-format.mpeg',
            'a' => [
                'b' => [
                    'c' => 'deeply nested.avi'
                ]
            ]
        ];

        $this->createTestStructure($files);
        $this->finder = $finder = new MovieFinder(new FileSetFactory($this->testDirectory), ['mkv', 'avi', 'mp4']);
    }

    public function tearDown()
    {
        $this->destroyTestStructure();
    }

    public function testFindsAllMovieFiles()
    {
        $fileSets = $this->finder->findMoviesInDir();
        $this->assertCount(5, $fileSets);
    }

    public function testIgnoresUnreadableDirectories()
    {
        chmod($this->testDirectory.'/a', 0222);
        $fileSets = $this->finder->findMoviesInDir();

        // usually it should be 5, but one is not readable = 4
        $this->assertCount(4, $fileSets);
        chmod($this->testDirectory.'/a', 0777);
    }
}
