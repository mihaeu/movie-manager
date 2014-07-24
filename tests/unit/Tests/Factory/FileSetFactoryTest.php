<?php

namespace Mihaeu\MovieManager\Tests\Factory;

use Mihaeu\MovieManager\Factory\FileSetFactory;
use org\bovigo\vfs\vfsStream;

class FileSetFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreatesACompleteMovieFile()
    {
        vfsStream::setup('root', null, [
            'Amour (2012)' => [
                'Amour (2012).avi'          => '',
                'Amour (2012) - IMDb.png'   => '',
                'Amour (2012) - IMDb.url'   => '',
                'Amour (2012) - Poster.jpg' => ''
            ]
        ]);

        $factory = new FileSetFactory(vfsStream::url('root'));
        $fileSet = $factory->create(vfsStream::url('root').'/Amour (2012)/Amour (2012).avi');
        $this->assertEquals('Amour (2012)', $fileSet->getParentFolder()->getBasename());
    }
}
