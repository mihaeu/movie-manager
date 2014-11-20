<?php

namespace Mihaeu\MovieManager\Tests\Console;

use Mihaeu\MovieManager\Console\Application;
use Mihaeu\MovieManager\Console\ListCommand;
use Mihaeu\MovieManager\Tests\BaseTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ListCommandTest extends BaseTestCase
{
    public function testListsSingle()
    {
        $app = new Application();
        $app->add(new ListCommand());

        $command = $app->find('list');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['path' => __DIR__.'/../../../demo/movies']);

        $this->assertRegExp('/.*Avatar.*/', $commandTester->getDisplay());
    }

    public function testFailsGracefullyOnBadInput()
    {
        $app = new Application();
        $app->add(new ListCommand());

        $command = $app->find('list');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['path' => 'does-not-exist']);

        $this->assertRegExp('/.*is not readable\./', $commandTester->getDisplay());
    }

    public function testComputesFilesizeOfDirectory()
    {
        $testDir = $this->testDirectory.'/filesizeTest';
        mkdir($testDir, 0777, true);
        file_put_contents($testDir.'/movie.mp4', str_repeat('1', 2 * 1024 * 1024));

        $listCommand = new ListCommand();
        $this->assertEquals(2, $listCommand->getMovieSizeInMb($testDir));

        unlink($testDir.'/movie.mp4');
        rmdir($testDir);
    }

    public function testAllowsOnlyFilesWithTheRightSize()
    {
        $listCommand = \Mockery::mock('Mihaeu\MovieManager\Console\ListCommand[getMovieSizeInMb]');
        $listCommand
            ->shouldReceive('getMovieSizeInMb')
            ->twice()
            ->andReturn(500, 1500);
        $app = new Application();
        $app->add($listCommand);

        $command = $app->find('list');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'path' => __DIR__.'/../../../demo/movies',
            '--max-size-movie' => 1000
        ]);

        $this->assertContains('Avatar', $commandTester->getDisplay());          // 500 MB so valid
        $this->assertNotContains('Godfather', $commandTester->getDisplay());    // 1500 MB so invalid
    }

    public function testLimitsTotalFilesizeOfAllMovies()
    {
        $listCommand = \Mockery::mock('Mihaeu\MovieManager\Console\ListCommand[getMovieSizeInMb]');
        $listCommand
          ->shouldReceive('getMovieSizeInMb')
          ->twice()
          ->andReturn(500, 1500);
        $app = new Application();
        $app->add($listCommand);

        $command = $app->find('list');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'path' => __DIR__.'/../../../demo/movies',
            '--max-size' => 1700
          ]);

        $this->assertContains('Avatar', $commandTester->getDisplay());          // 500 MB so valid
        $this->assertNotContains('Godfather', $commandTester->getDisplay());    // 1500 MB so invalid
    }
}
