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

        $command = $app->find('print-list');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['path' => __DIR__.'/../../../demo/movies']);

        $this->assertRegExp('/.*Avatar.*/', $commandTester->getDisplay());
    }

    public function testFailsGracefullyOnBadInput()
    {
        $app = new Application();
        $app->add(new ListCommand());

        $command = $app->find('print-list');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['path' => 'does-not-exist']);

        $this->assertRegExp('/.*is not readable\./', $commandTester->getDisplay());
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

        $command = $app->find('print-list');
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

        $command = $app->find('print-list');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'path' => __DIR__.'/../../../demo/movies',
            '--max-size' => 1700
        ]);

        $this->assertContains('Avatar', $commandTester->getDisplay());          // 500 MB so valid
        $this->assertNotContains('Godfather', $commandTester->getDisplay());    // 1500 MB so invalid
    }

    public function testSortsMoviesByRating()
    {
        // Godfather has a higher rating than Avatar
        $this->assertRegExp('/Godfather.*Avatar/ms', $this->getSortOutput('imdb_rating'));
    }

    public function testSortsMoviesByYear()
    {
        // Godfather is older than Avatar
        $this->assertRegExp('/Godfather.*Avatar/ms', $this->getSortOutput('year'));
    }

    public function testSortsDescending()
    {
        // Avatar has a lower rating than Godfather
        $this->assertRegExp('/Avatar.*Godfather/ms', $this->getSortOutput('imdb_rating', ['--desc' => true]));
    }

    /**
     * Helper method to keep tests DRY
     *
     * @param string $sortBy
     * @param array $otherOptions
     *
     * @return string Command output
     */
    public function getSortOutput($sortBy, array $otherOptions = [])
    {
        $app = new Application();
        $app->add(new ListCommand());

        $command = $app->find('print-list');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
          'path' => __DIR__.'/../../../demo/movies',
          '--sort-by' => $sortBy,
        ] + $otherOptions);

        return $commandTester->getDisplay();
    }
}
