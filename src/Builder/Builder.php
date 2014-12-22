<?php

namespace Mihaeu\MovieManager\Builder;

use Mihaeu\MovieManager\Movie;

interface Builder
{
    /**
     * @param array|Movie[] $movies
     *
     * @return string
     */
    public function build(array $movies);
}
