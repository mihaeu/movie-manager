<?php

namespace Mihaeu\MovieManager\Tests;

use Mihaeu\MovieManager\Console\Command;

class TestCommand extends BaseTestCase
{
    public function testComputesFilesizeOfDirectory()
    {
        $testDir = $this->testDirectory.'/filesizeTest';
        mkdir($testDir, 0777, true);
        file_put_contents($testDir.'/movie.mp4', str_repeat('1', 2 * 1024 * 1024));

        $command = new Command('meh');
        $this->assertEquals(2, $command->getMovieSizeInMb($testDir));

        unlink($testDir.'/movie.mp4');
        rmdir($testDir);
    }
}
