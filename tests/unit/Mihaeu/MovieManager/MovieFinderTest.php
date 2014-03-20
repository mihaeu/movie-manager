<?php

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

/**
 * Class FinderTest
 *
 * The Finder relies heavily on the filesystem, which is being mocked by vfs:://stream for testing purposes.
 *
 * @author Michael Haeuslmann <haeuslmann@gmail.com>
 */
class MovieFinderTest extends PHPUnit_Framework_TestCase
{
    public function testPHPUnitWorks()
    {
        $movieFinder = new Mihaeu\MovieManager\MovieFinder();
        $this->assertNotNull($movieFinder);
    }
}
