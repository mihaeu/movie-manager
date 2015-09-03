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
        $ini->write($this->testDirectory.'/test.ini', [
            'info' => [
                'test' => 'test'
            ],
            'number' => 3
        ]);
        $expected = "[info]\r\ntest=\"test\"\r\n\r\nnumber=3\r\n";
        $this->assertEquals($expected, file_get_contents($this->testDirectory.'/test.ini'));
    }

    public function testWritesPlainString()
    {
        $ini = new Ini(new Filesystem());
        $ini->write($this->testDirectory.'/test.ini', ['test']);
        $expected = "0=\"test\"\r\n";
        $this->assertEquals($expected, file_get_contents($this->testDirectory.'/test.ini'));
    }

    public function testNoCrashWhenFileNotExisting()
    {
        $ini = new Ini(new Filesystem());
        $this->assertEmpty($ini->read('non-existing-file'));
    }

    public function testNoCrashWhenFileEmpty()
    {
        $ini = new Ini(new Filesystem());
        mkdir($this->testDirectory);
        touch($this->testDirectory.'/empty-test.ini');
        $this->assertEmpty($ini->read($this->testDirectory.'/empty-test.ini'));
    }
}
