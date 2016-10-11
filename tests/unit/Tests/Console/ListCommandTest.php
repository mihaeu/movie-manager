<?php declare(strict_types = 1);

namespace Mihaeu\MovieManager\Tests\Console;

use Mihaeu\MovieManager\Console\Command;
use Mihaeu\MovieManager\Console\PrintListCommand;
use Mihaeu\MovieManager\Tests\BaseTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class ListCommandTest extends BaseTestCase
{
    /** @var Application */
    private $application;

    /** @var Command */
    private $command;

    /** @var CommandTester */
    private $commandTester;

    public function setUp()
    {
        $this->application = new Application();
        $this->application->add(new PrintListCommand());
        $this->command = $this->application->find('print-list');
        $this->commandTester = new CommandTester($this->command);
    }

    public function testListsSingle()
    {
        $this->commandTester->execute(['path' => __DIR__.'/../../../demo/movies']);
        $this->assertRegExp('/.*Avatar.*/', $this->commandTester->getDisplay());
    }

    public function testFailsGracefullyOnBadInput()
    {
        $this->commandTester->execute(['path' => 'does-not-exist']);
        $this->assertRegExp('/.*is not readable\./', $this->commandTester->getDisplay());
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

    public function testSortsMoviesByRating()
    {
        // Avatar has a lower rating than Godfather (ascending by default)
        $this->assertRegExp('/Avatar.*Godfather/ms', $this->getSortOutput('imdb_rating'));
    }

    public function testSortsMoviesByYear()
    {
        // Godfather is older than Avatar
        $this->assertRegExp('/Godfather.*Avatar/ms', $this->getSortOutput('year', ['--desc' => true]));
    }

    public function testSortsDescending()
    {
        // Avatar has a lower rating than Godfather
        $this->assertRegExp('/Godfather.*Avatar/ms', $this->getSortOutput('imdb_rating', ['--desc' => true]));
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
        $command = $this->application->find('print-list');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
          'path' => __DIR__.'/../../../demo/movies',
          '--sort-by' => $sortBy,
        ] + $otherOptions);

        return $commandTester->getDisplay();
    }
}
