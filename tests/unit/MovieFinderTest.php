<?php

//use org\bovigo\vfs\vfsStream;
//use org\bovigo\vfs\vfsStreamDirectory;

class MovieFinderTest extends PHPUnit_Framework_TestCase
{
    public function testPHPUnitWorks()
    {
        $this->markTestSkipped("Class unused.");
        $movieFinder = new Mihaeu\MovieManager\MovieFinder();
        $this->assertEmpty($movieFinder->find(sys_get_temp_dir()));
        // $this->assertEmpty($movieFinder->find('/home/mike/Desktop'));
    }
}
