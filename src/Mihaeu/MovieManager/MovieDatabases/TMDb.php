<?php

namespace Mihaeu\MovieManager;

class TMDb
{

    private $tmdbWrapper;

    public function __construct($tmdbApiSecret)
    {
        $this->tmdbWrapper = \TMDb($tmdbApiSecret, 'en');
    }
}
