<?php

namespace Mihaeu\MovieManager\Tests;

use Mihaeu\MovieManager\Console\PhantomJsWrapper;
use Mihaeu\MovieManager\Console\YoutubeDlWrapper;
use Mihaeu\MovieManager\IO\Filesystem;
use Mihaeu\MovieManager\Movie;
use Mihaeu\MovieManager\MovieHandler;

class MovieHandlerTest extends BaseTestCase
{
    private $youtubeDlMock;
    private $phantomJsMock;

    public function setUp()
    {
        $this->youtubeDlMock = $this
            ->getMockBuilder(YoutubeDlWrapper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->phantomJsMock = $this
            ->getMockBuilder(PhantomJsWrapper::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function tearDown()
    {
        $this->destroyTestStructure();
    }

    public function testGeneratesProperMovieName()
    {
        $movie = new Movie();
        $movie->setTitle('Avatar');
        $movie->setYear(2009);

        $this->createTestStructure(['avatar.mkv']);
        $movieFile = new \SplFileInfo($this->testDirectory.'/avatar.mkv');

        $mockFilesystem = \Mockery::mock('Mihaeu\MovieManager\IO\Filesystem'); /** @var Filesystem $mockFilesystem */
        $handler = new MovieHandler(
            $mockFilesystem,
            $this->youtubeDlMock,
            $this->phantomJsMock
        );
        $filename = $handler->generateFileName($movie, $movieFile);
        $this->assertEquals($this->testDirectory.'/Avatar (2009)', $filename);

        $filename = $handler->generateFileName($movie, $movieFile, '.mkv');
        $this->assertEquals($this->testDirectory.'/Avatar (2009).mkv', $filename);
    }

    public function testGeneratesIMDbLinkFromId()
    {
        $mockFilesystem = \Mockery::mock('Mihaeu\MovieManager\IO\Filesystem');  /** @var Filesystem $mockFilesystem */
        $handler = new MovieHandler(
            $mockFilesystem,
            $this->youtubeDlMock,
            $this->phantomJsMock
        );
        $this->assertEquals('http://www.imdb.com/title/tt123456', $handler->getIMDbLink('tt123456'));
    }

    public function testExtractsReleaseYearFromReleaseDate()
    {
        $handler = new MovieHandler(
            new Filesystem(),
            $this->youtubeDlMock,
            $this->phantomJsMock
        );
        $this->assertEquals('2020', $handler->convertMovieYear('10-10-2020'));
    }

    public function testRenameMovie()
    {
        $movie = new Movie();
        $movie->setTitle('Avatar');
        $movie->setYear(2009);

        $this->createTestStructure(['avatar.mkv']);
        $movieFile = new \SplFileInfo($this->testDirectory.'/avatar.mkv');

        $handler = new MovieHandler(
            new Filesystem(),
            $this->youtubeDlMock,
            $this->phantomJsMock
        );
        $handler->renameMovie($movie, $movieFile);

        $this->assertTrue(file_exists($this->testDirectory.'/Avatar (2009).mkv'));
        $this->assertFalse($handler->renameMovie($movie, $movieFile));
    }

    public function testRenameMovieDirectory()
    {
        $movie = new Movie();
        $movie->setTitle('Avatar');
        $movie->setYear(2009);

        $this->createTestStructure(['avatar/avatar.mkv']);
        $movieFile = new \SplFileInfo($this->testDirectory.'/avatar/avatar.mkv');

        $handler = new MovieHandler(
            new Filesystem(),
            $this->youtubeDlMock,
            $this->phantomJsMock
        );
        $handler->renameMovieFolder($movie, $movieFile);

        $this->assertTrue(file_exists($this->testDirectory.'/Avatar (2009)/avatar.mkv'));
        $this->assertFalse($handler->renameMovieFolder($movie, $movieFile));
    }

    public function testCreatesMovieInfo()
    {
        $this->createTestStructure(['avatar/avatar.mkv']);
        $movieFile = new \SplFileInfo($this->testDirectory.'/avatar/avatar.mkv');

        $movie = new Movie();
        $movie->setTitle('Avatar');
        $movie->setYear(2009);
        $movie->setImdbId('tt123456');
        $movie->setDirectors(['Michael Bay']);

        $handler = new MovieHandler(
            new Filesystem(),
            $this->youtubeDlMock,
            $this->phantomJsMock
        );
        $handler->createMovieInfo($movie, $movieFile);

        $movieInfo = $this->testDirectory.'/avatar/Avatar (2009) - IMDb.url';
        $this->assertTrue(file_exists($movieInfo));
        $movieInfoContent = file_get_contents($movieInfo);
        $this->assertRegExp('/year=2009.*title="Avatar".*\[directors\]/sm', $movieInfoContent);
    }

    public function testMovesCompleteMovieFolder()
    {
        $this->createTestStructure(['avatar/avatar.mkv']);
        $movieFile = new \SplFileInfo($this->testDirectory.'/avatar/avatar.mkv');

        $handler = new MovieHandler(
            new Filesystem(),
            $this->youtubeDlMock,
            $this->phantomJsMock
        );
        $handler->moveTo($movieFile, $this->testDirectory.'/target');

        $this->assertFileExists($this->testDirectory . '/target/avatar/avatar.mkv');
    }

    public function testDetectsIfMovieIsInSeparateDirectory()
    {
        $this->createTestStructure(['avatar/avatar.mkv', 'the godfather.avi']);
        $movieFileInSeparateFolder = new \SplFileInfo($this->testDirectory.'/avatar/avatar.mkv');
        $movieFileNotInSeparateFolder = new \SplFileInfo($this->testDirectory.'/the godfather.avi');

        $handler = new MovieHandler(
            new Filesystem(),
            $this->youtubeDlMock,
            $this->phantomJsMock
        );
        $root = new \SplFileInfo($this->testDirectory);
        $this->assertTrue($handler->movieIsNotInSeparateFolder($root, $movieFileNotInSeparateFolder));
        $this->assertFalse($handler->movieIsNotInSeparateFolder($root, $movieFileInSeparateFolder));
    }

    public function testMovesMovieToSeparateDirectory()
    {
        $this->createTestStructure(['the godfather.avi']);
        $movieFile = new \SplFileInfo($this->testDirectory.'/the godfather.avi');

        $handler = new MovieHandler(
            new Filesystem(),
            $this->youtubeDlMock,
            $this->phantomJsMock
        );
        $root = new \SplFileInfo($this->testDirectory);
        $newDestination = $handler->moveMovieToSeparateFolder($root, $movieFile);
        $this->assertFileExists($newDestination);
    }

    public function testDownloadsMoviePoster()
    {
        $this->markTestIncomplete('Method needs to be refactored to avoid expensive network access.');
    }

    public function testDownloadsScreenshotOfImdbPage()
    {
        $this->markTestIncomplete('Method needs to be refactored to avoid expensive network access.');
    }

    public function testDownloadTrailer()
    {
        $this->markTestIncomplete('Method needs to be refactored to avoid expensive network access.');
    }
}
