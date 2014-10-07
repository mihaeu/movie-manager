<?php

namespace Mihaeu\MovieManager\Tests;

use Mihaeu\MovieManager\Movie;
use Mihaeu\MovieManager\MovieHandler;

class MovieHandlerTest extends BaseTestCase
{
    public function testGeneratesProperMovieName()
    {
        $movie = new Movie();
        $movie->setTitle('Avatar');
        $movie->setYear(2009);

        $this->createTestStructure(['avatar.mkv']);
        $movieFile = new \SplFileInfo($this->testDirectory.'/avatar.mkv');

        $mockFilesystem = \Mockery::mock('Symfony\Component\Filesystem\Filesystem');
        $handler = new MovieHandler($mockFilesystem);
        $filename = $handler->generateFileName($movie, $movieFile);
        $this->assertEquals($this->testDirectory.'/Avatar (2009)', $filename);

        $filename = $handler->generateFileName($movie, $movieFile, '.mkv');
        $this->assertEquals($this->testDirectory.'/Avatar (2009).mkv', $filename);
    }

    public function testGeneratesIMDbLinkFromId()
    {
        $mockFilesystem = \Mockery::mock('Symfony\Component\Filesystem\Filesystem');
        $handler = new MovieHandler($mockFilesystem);
        $this->assertEquals('http://www.imdb.com/title/tt123456', $handler->getIMDbLink('tt123456'));
    }
}
