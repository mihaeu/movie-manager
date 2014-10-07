<?php

namespace Mihaeu\MovieManager\Tests;

use Mihaeu\MovieManager\Factory\FileSetFactory;
use Mihaeu\MovieManager\MovieFinder;

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
        $fileSets = $this->finder->findMoviesInDir($this->testDirectory);
        $this->assertCount(5, $fileSets);
    }
}
