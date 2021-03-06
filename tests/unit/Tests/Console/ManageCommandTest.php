<?php declare(strict_types = 1);

namespace Mihaeu\MovieManager\Tests\Console;

use Mihaeu\MovieManager\Console\ManageCommand;
use Mihaeu\MovieManager\Console\PhantomJsWrapper;
use Mihaeu\MovieManager\Console\YoutubeDlWrapper;
use Mihaeu\MovieManager\IO\Downloader;
use Mihaeu\MovieManager\Tests\BaseTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class ManageCommandTest extends BaseTestCase
{
    public function testShowsEvenProcessedOnes()
    {
        $app = new Application();
        $app->add(new ManageCommand(
            $this->createMock(YoutubeDlWrapper::class),
            $this->createMock(PhantomJsWrapper::class),
            $this->createMock(Downloader::class)
        ));

        $command = $app->find('manage');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'path'          => __DIR__.'/../../../demo/movies',
            '--show-all'    => true,
        ]);

        $this->assertRegExp('/.*Avatar \(2009\).mkv.*/m', $commandTester->getDisplay());
    }
}
