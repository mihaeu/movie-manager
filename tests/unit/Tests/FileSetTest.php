<?php

namespace Mihaeu\MovieManager\Tests;

use Mihaeu\MovieManager\Factory\FileSetFactory;
use Mihaeu\MovieManager\FileSet;
use org\bovigo\vfs\vfsStream;

class FileSetTest extends BaseTestCase
{
    public function setUp()
    {
        vfsStream::setup('root', null, [
            'Amour (2012)' => [
                'Amour (2012).avi'          => '1111111111',
                'Amour (2012).srt'          => '',
                'Amour (2012) - CD2.avi'    => '',
                'Amour (2012) - IMDb.png'   => '',
                'Amour (2012) - IMDb.url'   => '',
                'Amour (2012) - Poster.jpg' => ''
            ],
            'Amour' => [
                'Amour (2012).avi'          => '',
            ]
        ]);
    }

    public function testComputesFileSize()
    {
        $this->createTestStructure(['folder' => ['movie.mp4']]);
        $movieFilename = $this->testDirectory.DIRECTORY_SEPARATOR.'folder'.DIRECTORY_SEPARATOR.'movie.mp4';
        file_put_contents($movieFilename, str_repeat('1', 1024*1024));
        $factory = new FileSetFactory($this->testDirectory);
        $fileSet = $factory->create($movieFilename);
        $this->assertEquals(1, $fileSet->getFilesize());
        $this->destroyTestStructure();
    }

    public function testChecksParentFolder()
    {
        $factory = new FileSetFactory(vfsStream::url('root'));
        $fileSet = $factory->create(vfsStream::url('root').'/Amour (2012)/Amour (2012).avi');
        $this->assertTrue($fileSet->hasCorrectParentFolder());

        $fileSet = $factory->create(vfsStream::url('root').'/Amour/Amour (2012).avi');
        $this->assertFalse($fileSet->hasCorrectParentFolder());

        $emptyFileSet = new FileSet();
        $this->assertFalse($emptyFileSet->hasCorrectParentFolder());
    }

    public function testDetectsIfTheFileHasTheRightName()
    {
        $factory = new FileSetFactory(vfsStream::url('root'));
        $fileSet = $factory->create(vfsStream::url('root').'/Amour (2012)/Amour (2012).avi');
        $this->assertTrue($fileSet->hasCorrectName());
        $this->assertTrue($fileSet->hasCorrectName('Amour', 2012));
        $emptyFileSet = new FileSet();
        $this->assertFalse($emptyFileSet->hasCorrectName());
    }

    public function testDetectsPoster()
    {
        $factory = new FileSetFactory(vfsStream::url('root'));
        $fileSet = $factory->create(vfsStream::url('root').'/Amour (2012)/Amour (2012).avi');
        $this->assertNotNull($fileSet->getPosterFile());

        $fileSet = $factory->create(vfsStream::url('root').'/Amour/Amour (2012).avi');
        $this->assertNull($fileSet->getPosterFile());
    }

    public function testDetectsMovieInfo()
    {
        $factory = new FileSetFactory(vfsStream::url('root'));
        $fileSet = $factory->create(vfsStream::url('root').'/Amour (2012)/Amour (2012).avi');
        $this->assertNotNull($fileSet->getInfoFile());

        $fileSet = $factory->create(vfsStream::url('root').'/Amour/Amour (2012).avi');
        $this->assertNull($fileSet->getInfoFile());
    }

    public function testDetectsScreenshot()
    {
        $factory = new FileSetFactory(vfsStream::url('root'));
        $fileSet = $factory->create(vfsStream::url('root').'/Amour (2012)/Amour (2012).avi');
        $this->assertNotNull($fileSet->getImdbScreenshotFile());

        $fileSet = $factory->create(vfsStream::url('root').'/Amour/Amour (2012).avi');
        $this->assertNull($fileSet->getImdbScreenshotFile());
    }

    public function testDetectsSubtitles()
    {
        $factory = new FileSetFactory(vfsStream::url('root'));
        $fileSet = $factory->create(vfsStream::url('root').'/Amour (2012)/Amour (2012).avi');
        $this->assertNotNull($fileSet->getSubtitleFiles());

        $fileSet = $factory->create(vfsStream::url('root').'/Amour/Amour (2012).avi');
        $this->assertEmpty($fileSet->getSubtitleFiles());
    }

    public function testDetectsMultiPartMovies()
    {
        $factory = new FileSetFactory(vfsStream::url('root'));
        $fileSet = $factory->create(vfsStream::url('root').'/Amour (2012)/Amour (2012).avi');
        $this->assertCount(2, $fileSet->getMoviePartFiles());

        $fileSet = $factory->create(vfsStream::url('root').'/Amour/Amour (2012).avi');
        $this->assertCount(1, $fileSet->getMoviePartFiles());
    }
}
