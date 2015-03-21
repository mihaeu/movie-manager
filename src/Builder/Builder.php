<?php

namespace Mihaeu\MovieManager\Builder;

use Mihaeu\MovieManager\Movie;

interface Builder
{
    /**
     * @param array|Movie[] $movies
     * @param string        $path
     *
     * @return string
     */
    public function build(array $movies, $path);
}
