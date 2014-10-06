<?php

namespace Mihaeu\MovieManager\Tests;

use PHPUnit\Runner\CleverAndSmart\Storage\Sqlite3Storage;

class Sqlite3TestStorage extends Sqlite3Storage
{
    public function __construct($fileName = '.phpunit-cas.db')
    {
        $fileName = realpath(__DIR__.'/../../../'.$fileName);
        parent::__construct($fileName);
    }
}
