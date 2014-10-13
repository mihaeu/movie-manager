<?php

namespace Mihaeu\MovieManager\Tests\Console;

use Mihaeu\MovieManager\Console\Application;
use Mihaeu\MovieManager\Console\ManageCommand;
use Mihaeu\MovieManager\Tests\BaseTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ManageCommandTest extends BaseTestCase
{
    public function testShowsEvenProcessedOnes()
    {
        $app = new Application();
        $app->add(new ManageCommand());

        $command = $app->find('manage');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['path' => __DIR__.'/../../../demo/movies', '--show-all' => true]);

        $this->assertRegExp('/.*Avatar \(2009\).mkv.*/m', $commandTester->getDisplay());
    }
}
