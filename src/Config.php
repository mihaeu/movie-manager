<?php

namespace Mihaeu\MovieManager;

/**
 * Configuration based on a json file.
 *
 * @author Michael Haeuslmann (haeuslmann@gmail.com)
 */
class Config
{
    /**
     * @var array
     */
    private $config;

    /**
     * @param  string|null $configFile
     *
     * @throws \Exception
     */
    public function __construct($configFile = null)
    {
        if (null === $configFile) {
            $configFile = __DIR__ . '/../config.json';
        }
        $this->ensureFileIsReadable($configFile);

        $this->config = json_decode(file_get_contents($configFile), true);
    }

    public function tmdbApiKey() : string
    {
        return $this->config['tmdb-api-key'];
    }

    /**
     * @return string[]
     */
    public function allowedMovieFormats() : array
    {
        return $this->config['allowed-movie-formats'];
    }

    /**
     * @param $configFile
     * @throws \Exception
     */
    private function ensureFileIsReadable($configFile)
    {
        if (!is_readable($configFile)) {
            throw new \Exception($configFile . ' does not exist, please create it or rename config.sample.json.' . PHP_EOL);
        }
    }
}
