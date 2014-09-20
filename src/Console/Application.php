<?php

namespace Mihaeu\MovieManager\Console;

use Symfony\Component\Console\Application as BaseApplication;

class Application extends BaseApplication
{
    /**
     * Initializes all the composer commands
     */
    protected function getDefaultCommands()
    {
        $commands = parent::getDefaultCommands();
        $commands[] = new ListCommand();
        $commands[] = new BuildCommand();
        $commands[] = new ManageCommand();

        return $commands;
    }
}