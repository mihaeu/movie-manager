<?php

use Mihaeu\MovieManager\IniWriter;

class IniWriterTest extends PHPUnit_Framework_TestCase
{
    private $testFile;

    public function setUp()
    {
        $this->testFile = sys_get_temp_dir().DIRECTORY_SEPARATOR.'test.ini';
        touch($this->testFile);
    }

    public function tearDown()
    {
        if (file_exists($this->testFile)) {
            unlink($this->testFile);
        }
    }

    public function testWritesSimpleArray()
    {
        Mihaeu\MovieManager\IniWriter::write([1], $this->testFile);
        $this->assertEquals("0=1", file_get_contents($this->testFile));
    }

    public function testWritesNestedArray()
    {
        Mihaeu\MovieManager\IniWriter::write([
                '1st' => 1, '2nd' => [
                    2, 3, 4
                ]
            ], $this->testFile);
        $this->assertEquals("1st=1\r\n[2nd]\r\n0=2\r\n1=3\r\n2=4", file_get_contents($this->testFile));
    }
}
