<?php

namespace Mihaeu\MovieManager\Tests;

use Mihaeu\MovieManager\Config;

class ConfigTest extends BaseTestCase
{
    /**
     * @expectedException \Exception
     */
    public function testBadConfigFile()
    {
        new Config('/doesnotexist/config.yml');
    }

    public function testGetsCorrectEntry()
    {
        $config = new Config();
        $this->assertNotEmpty($config->tmdbApiKey());
    }
}
