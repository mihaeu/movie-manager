<?php

use Mihaeu\MovieManager\IMDb;

class IMDbTest extends PHPUnit_Framework_TestCase
{
    public function testCorrectRequest()
    {
        $this->markTestIncomplete('RedPanda library problems.');
        // Avatar (2009) --> 7.9
        $this->assertEquals(IMDb::getRatingFromIMDb(0499549), 7.9);
    }
}
