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
}
