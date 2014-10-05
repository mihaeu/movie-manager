<?php

namespace Mihaeu\MovieManager\Tests;

use Mihaeu\MovieManager\Factory\FileSetFactory;
use org\bovigo\vfs\vfsStream;

class FileSetTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        vfsStream::setup('root', null, [
            'Amour (2012)' => [
                'Amour (2012).avi'          => '',
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

    public function testChecksParentFolder()
    {
        $factory = new FileSetFactory(vfsStream::url('root'));
        $fileSet = $factory->create(vfsStream::url('root').'/Amour (2012)/Amour (2012).avi');
        $this->assertTrue($fileSet->hasCorrectParentFolder());

        $fileSet = $factory->create(vfsStream::url('root').'/Amour/Amour (2012).avi');
        $this->assertFalse($fileSet->hasCorrectParentFolder());
    }

    public function testDetectsIfTheFileHasTheRightName()
    {
        $factory = new FileSetFactory(vfsStream::url('root'));
        $fileSet = $factory->create(vfsStream::url('root').'/Amour (2012)/Amour (2012).avi');
        $this->assertTrue($fileSet->hasCorrectName());
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
}
