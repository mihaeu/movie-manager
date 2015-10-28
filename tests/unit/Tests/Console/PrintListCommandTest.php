<?php

namespace Mihaeu\MovieManager\Tests\Console;

use Mihaeu\MovieManager\Console\Application;
use Mihaeu\MovieManager\Console\PrintListCommand;
use Mihaeu\MovieManager\Tests\BaseTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class PrintListCommandTest extends BaseTestCase
{
    public function testListsSingle()
    {
        $app = new Application();

        $command = $app->find('print-list');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['path' => __DIR__.'/../../../demo/movies']);

        $this->assertRegExp('/.*Avatar.*/', $commandTester->getDisplay());
    }

    public function testFailsGracefullyOnBadInput()
    {
        $app = new Application();

        $command = $app->find('print-list');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['path' => 'does-not-exist']);

        $this->assertRegExp('/.*is not readable\./', $commandTester->getDisplay());
    }

    public function testAllowsOnlyFilesWithTheRightSize()
    {
        $listCommand = \Mockery::mock('Mihaeu\MovieManager\Console\PrintListCommand[getMovieSizeInMb]');
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
        $listCommand = \Mockery::mock('Mihaeu\MovieManager\Console\PrintListCommand[getMovieSizeInMb]');
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

    public function testSortsMoviesByRatingAscending()
    {
        // Godfather has a higher rating than Avatar
        // ASC sort: Avatar, Godfather
        $this->assertRegExp('/Avatar.*Godfather/ms', $this->getSortOutput('imdb_rating'));
    }

    public function testSortsMoviesByRatingDescending()
    {
        // Avatar has a lower rating than Godfather
        // DESC sort: Godfather, Avatar
        $this->assertRegExp('/Godfather.*Avatar/ms', $this->getSortOutput('imdb_rating', ['--desc' => true]));
    }

    public function testSortsMoviesByYearAscending()
    {
        // Godfather is older (year is lower) than Avatar
        // ASC sort: Godfather, Avatar
        $this->assertRegExp('/Godfather.*Avatar/ms', $this->getSortOutput('year'));
    }

    public function testSortingByNonExistingAttributeDoesNothing()
    {
        $this->assertRegExp('/Avatar.*Godfather/ms', $this->getSortOutput('fdfsfsdfsd'));
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
        $app->add(new PrintListCommand());

        $command = $app->find('print-list');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
          'path' => __DIR__.'/../../../demo/movies',
          '--sort-by' => $sortBy,
        ] + $otherOptions);

        return $commandTester->getDisplay();
    }
}
