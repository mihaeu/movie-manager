<?php

namespace Mihaeu\MovieManager\Tests\IO;

use Mihaeu\MovieManager\IO\Filesystem;
use Mihaeu\MovieManager\IO\Ini;
use Mihaeu\MovieManager\Tests\BaseTestCase;

class IniTest extends BaseTestCase
{
    public function tearDown()
    {
        $this->destroyTestStructure();
    }

    public function testWritesSimpleArray()
    {
        $ini = new Ini(new Filesystem());
        $ini->write($this->testDirectory.'/test.ini', ['info' => ['test' => 'test']]);
        $expected = "[info]\r\ntest=\"test\"\r\n\r\n";
        $this->assertEquals($expected, file_get_contents($this->testDirectory.'/test.ini'));
    }

    public function testWritesPlainString()
    {
        $ini = new Ini(new Filesystem());
        $ini->write($this->testDirectory.'/test.ini', ['test']);
        $expected = "0=\"test\"\r\n";
        $this->assertEquals($expected, file_get_contents($this->testDirectory.'/test.ini'));
    }
}
