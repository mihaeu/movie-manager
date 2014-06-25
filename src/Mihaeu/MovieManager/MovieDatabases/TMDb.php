<?php

namespace Mihaeu\MovieManager\MovieDatabases;

class TMDb
{

    private $tmdbWrapper;

    public function __construct($tmdbApiSecret)
    {
        $this->tmdbWrapper = \TMDb($tmdbApiSecret, 'en');
    }
}
