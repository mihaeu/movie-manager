<?php

use Mihaeu\MovieManager\MovieFinder;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

class MovieFinderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var MovieFinder
     */
    private $movieFinder;

    public function setUp()
    {
        $this->movieFinder = new MovieFinder();

        // mock the filesystem
        $testFiles = array(
            'movies' => array(
                'Armageddon (2010)' => array(
                    'Armageddon (2010).avi' => '',
                    'Armageddon (2010).srt' => '',
                ),
                'Die Hard.mkv' => '',
                'subFolderA' => array(
                    'subFolderB' => array(
                        'subFolderC' => array(
                            'Avatar.mp4' => '',
                            'Avatar.srt' => ''
                        )
                    )
                )
            )
        );
        $this->root = vfsStream::setup('testDir', null, $testFiles);
    }

    public function testFindsMovieInFolder()
    {
        $flatMoviePath = vfsStream::url('testDir').'/movies/Armageddon (2010)';
        $this->assertEquals(1, count($this->movieFinder->find($flatMoviePath)));
    }

    public function testFindsMoviesInNestedFolder()
    {
        $nestedMoviePath = vfsStream::url('testDir');
        $this->assertEquals(3, count($this->movieFinder->find($nestedMoviePath)));
    }

    public function testDetectsIfAMovieIsInTheProperFolder()
    {
        $flatMoviePath = vfsStream::url('testDir').'/movies/Armageddon (2010)/Armageddon (2010).avi';
        $this->assertTrue($this->movieFinder->movieInMovieFolder($flatMoviePath));
        
        $badMoviePath = vfsStream::url('testDir').'/movies/subFolderA/subFolderB/subFolderC/Avatar.mp4';
        $this->assertFalse($this->movieFinder->movieInMovieFolder($badMoviePath));
    }
}
