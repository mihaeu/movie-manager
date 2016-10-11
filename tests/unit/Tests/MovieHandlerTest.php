<?php declare(strict_types = 1);

namespace Mihaeu\MovieManager\Tests;

use Mihaeu\MovieManager\Console\PhantomJsWrapper;
use Mihaeu\MovieManager\Console\YoutubeDlWrapper;
use Mihaeu\MovieManager\IO\Downloader;
use Mihaeu\MovieManager\IO\Filesystem;
use Mihaeu\MovieManager\Movie;
use Mihaeu\MovieManager\MovieHandler;

class MovieHandlerTest extends BaseTestCase
{
    /** @var Filesystem|\PHPUnit_Framework_MockObject_MockObject */
    private $mockFileSystem;

    /** @var YoutubeDlWrapper|\PHPUnit_Framework_MockObject_MockObject */
    private $youtubeDlMock;

    /** @var PhantomJsWrapper|\PHPUnit_Framework_MockObject_MockObject */
    private $phantomJsMock;

    /** @var Downloader|\PHPUnit_Framework_MockObject_MockObject */
    private $downloader;

    /** @var MovieHandler */
    private $movieHandler;

    public function setUp()
    {
        $this->youtubeDlMock = $this->createMock(YoutubeDlWrapper::class);
        $this->phantomJsMock = $this->createMock(PhantomJsWrapper::class);
        $this->mockFileSystem = $this->createMock(Filesystem::class);
        $this->downloader = $this->createMock(Downloader::class);

        $this->movieHandler = new MovieHandler(
            $this->mockFileSystem,
            $this->youtubeDlMock,
            $this->phantomJsMock,
            $this->downloader
        );
    }

    public function tearDown()
    {
        $this->destroyTestStructure();
    }

    public function testGeneratesProperMovieName()
    {
        $movie = (new Movie())
            ->setTitle('Avatar')
            ->setYear(2009);

        $this->createTestStructure(['avatar.mkv']);
        $movieFile = new \SplFileInfo($this->testDirectory.'/avatar.mkv');

         /** @var Filesystem $mockFilesystem */
        $filename = $this->movieHandler->generateFileName($movie, $movieFile);
        $this->assertEquals($this->testDirectory.'/Avatar (2009)', $filename);

        $filename = $this->movieHandler->generateFileName($movie, $movieFile, '.mkv');
        $this->assertEquals($this->testDirectory.'/Avatar (2009).mkv', $filename);
    }

    public function testGeneratesIMDbLinkFromId()
    {
        $this->assertEquals('http://www.imdb.com/title/tt123456', $this->movieHandler->getIMDbLink('tt123456'));
    }

    public function testExtractsReleaseYearFromReleaseDate()
    {
        $this->assertEquals('2020', $this->movieHandler->convertMovieYear('10-10-2020'));
    }

    public function testRenameMovie()
    {
        $movie = (new Movie())
            ->setTitle('Avatar')
            ->setYear(2009);

        $this->createTestStructure(['avatar.mkv']);
        $movieFile = new \SplFileInfo($this->testDirectory.'/avatar.mkv');

        $this->movieHandler = new MovieHandler(
            new Filesystem(),
            $this->youtubeDlMock,
            $this->phantomJsMock,
            $this->downloader
        );
        $this->movieHandler->renameMovie($movie, $movieFile);

        $this->assertFileExists($this->testDirectory . '/Avatar (2009).mkv');
        $this->assertFalse($this->movieHandler->renameMovie($movie, $movieFile));
    }

    public function testRenameMovieDirectory()
    {
        $movie = (new Movie())
            ->setTitle('Avatar')
            ->setYear(2009);

        $this->createTestStructure(['avatar/avatar.mkv']);
        $movieFile = new \SplFileInfo($this->testDirectory.'/avatar/avatar.mkv');

        $this->movieHandler = new MovieHandler(
            new Filesystem(),
            $this->youtubeDlMock,
            $this->phantomJsMock,
            $this->downloader
        );
        $this->movieHandler->renameMovieFolder($movie, $movieFile);

        $this->assertFileExists($this->testDirectory . '/Avatar (2009)/avatar.mkv');
        $this->assertFalse($this->movieHandler->renameMovieFolder($movie, $movieFile));
    }

    public function testCreatesMovieInfo()
    {
        $this->createTestStructure(['avatar/avatar.mkv']);
        $movieFile = new \SplFileInfo($this->testDirectory.'/avatar/avatar.mkv');

        $movie = (new Movie())
            ->setTitle('Avatar')
            ->setYear(2009)
            ->setImdbId('tt123456')
            ->setDirectors(['Michael Bay']);

        $this->movieHandler = new MovieHandler(
            new Filesystem(),
            $this->youtubeDlMock,
            $this->phantomJsMock,
            $this->downloader
        );
        $this->movieHandler->createMovieInfo($movie, $movieFile);

        $movieInfo = $this->testDirectory.'/avatar/Avatar (2009) - IMDb.url';
        $this->assertFileExists($movieInfo);
        $movieInfoContent = file_get_contents($movieInfo);
        $this->assertRegExp('/year=2009.*title="Avatar".*\[directors\]/sm', $movieInfoContent);
    }

    public function testMovesCompleteMovieFolder()
    {
        $this->createTestStructure(['avatar/avatar.mkv']);
        $movieFile = new \SplFileInfo($this->testDirectory.'/avatar/avatar.mkv');

        $this->movieHandler = new MovieHandler(
            new Filesystem(),
            $this->youtubeDlMock,
            $this->phantomJsMock,
            $this->downloader
        );
        $this->movieHandler->moveTo($movieFile, $this->testDirectory.'/target');

        $this->assertFileExists($this->testDirectory . '/target/avatar/avatar.mkv');
    }

    public function testDetectsIfMovieIsInSeparateDirectory()
    {
        $this->createTestStructure(['avatar/avatar.mkv', 'the godfather.avi']);
        $movieFileInSeparateFolder = new \SplFileInfo($this->testDirectory.'/avatar/avatar.mkv');
        $movieFileNotInSeparateFolder = new \SplFileInfo($this->testDirectory.'/the godfather.avi');

        $root = new \SplFileInfo($this->testDirectory);
        $this->assertTrue($this->movieHandler->movieIsNotInSeparateFolder($root, $movieFileNotInSeparateFolder));
        $this->assertFalse($this->movieHandler->movieIsNotInSeparateFolder($root, $movieFileInSeparateFolder));
    }

    public function testMovesMovieToSeparateDirectory()
    {
        $this->createTestStructure(['the godfather.avi']);
        $movieFile = new \SplFileInfo($this->testDirectory.'/the godfather.avi');

        $root = new \SplFileInfo($this->testDirectory);
        $newDestination = $this->movieHandler->moveMovieToSeparateFolder($root, $movieFile);
        $this->assertFileExists($newDestination);
    }

    public function testDownloadsMoviePoster()
    {
        $movie = (new Movie())
            ->setTitle('Avatar')
            ->setYear(2009)
            ->setImdbId('tt123456')
            ->setDirectors(['Michael Bay'])
            ->setPosterUrl('http://example.com');

        $this->createTestStructure(['the godfather.avi']);
        $movieFile = new \SplFileInfo($this->testDirectory.'/the godfather.avi');

        $this->downloader->expects($this->once())
            ->method('download')
            ->with('http://example.com', $this->stringContains('Avatar'))
            ->willReturn(true);
        $this->movieHandler->downloadMoviePoster($movie, $movieFile);
    }

    public function testDownloadsScreenshotOfImdbPage()
    {
        $movie = (new Movie())
            ->setTitle('Avatar')
            ->setYear(2009)
            ->setImdbId('tt123456')
            ->setDirectors(['Michael Bay'])
            ->setPosterUrl('http://example.com');

        $this->createTestStructure(['the godfather.avi']);
        $movieFile = new \SplFileInfo($this->testDirectory.'/the godfather.avi');

        $this->phantomJsMock->expects($this->once())
            ->method('downloadScreenshot')
            ->with('http://www.imdb.com/title/tt123456', $this->stringContains('Avatar'))
            ->willReturn(true);
        $this->movieHandler->downloadIMDbScreenshot($movie, $movieFile);
    }

    public function testDownloadTrailer()
    {
        $movie = (new Movie())
            ->setTitle('Avatar')
            ->setYear(2009)
            ->setImdbId('tt123456')
            ->setDirectors(['Michael Bay'])
            ->setPosterUrl('http://example.com')
            ->setTrailer('Trailer');

        $this->createTestStructure(['the godfather.avi']);
        $movieFile = new \SplFileInfo($this->testDirectory.'/the godfather.avi');

        $this->youtubeDlMock->expects($this->once())
            ->method('download')
            ->with('Trailer', $this->stringContains('Avatar'))
            ->willReturn(true);
        $this->movieHandler->downloadTrailer($movie, $movieFile);
    }
}
