<?php

namespace Mihaeu\MovieManager\Console;

use Symfony\Component\Console\Application as BaseApplication;

class Application extends BaseApplication
{
    /**
     * {@inheritdoc}
     */
    protected function getDefaultCommands()
    {
        $commands = parent::getDefaultCommands();
        $commands[] = new CopyCommand();
        $commands[] = new PrintListCommand();
        $commands[] = new ListCommand();
        $commands[] = new BuildCommand();
        $commands[] = new ManageCommand();

        return $commands;
    }
}
