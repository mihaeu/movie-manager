<?php

namespace Mihaeu\MovieManager\Tests;

use Mihaeu\MovieManager\Factory\FileSetFactory;
use org\bovigo\vfs\vfsStream;

class FileSetTest extends \PHPUnit_Framework_TestCase
{
    public function testChecksParentFolder()
    {
        vfsStream::setup('root', null, [
                'Amour (2012)' => [
                    'Amour (2012).avi'          => '',
                    'Amour (2012) - CD2.avi'    => '',
                    'Amour (2012) - IMDb.png'   => '',
                    'Amour (2012) - IMDb.url'   => '',
                    'Amour (2012) - Poster.jpg' => ''
                ],
                'Amour' => [
                    'Amour (2012).avi'          => '',
                    'Amour (2012) - CD2.avi'    => '',
                    'Amour (2012) - IMDb.png'   => '',
                    'Amour (2012) - IMDb.url'   => '',
                    'Amour (2012) - Poster.jpg' => ''
                ]
            ]);

        $factory = new FileSetFactory(vfsStream::url('root'));
        $fileSet = $factory->create(vfsStream::url('root').'/Amour (2012)/Amour (2012).avi');
        $this->assertTrue($fileSet->hasCorrectParentFolder());

        $fileSet = $factory->create(vfsStream::url('root').'/Amour/Amour (2012).avi');
        $this->assertFalse($fileSet->hasCorrectParentFolder());
    }

    public function testDetectsIfTheFileHasTheRightName()
    {
        vfsStream::setup('root', null, [
                'Amour (2012)' => [
                    'Amour (2012).avi'          => '',
                    'Amour (2012) - CD2.avi'    => '',
                    'Amour (2012) - IMDb.png'   => '',
                    'Amour (2012) - IMDb.url'   => '',
                    'Amour (2012) - Poster.jpg' => ''
                ]
            ]);

        $factory = new FileSetFactory(vfsStream::url('root'));
        $fileSet = $factory->create(vfsStream::url('root').'/Amour (2012)/Amour (2012).avi');
        $this->assertTrue($fileSet->hasCorrectName());
    }
}