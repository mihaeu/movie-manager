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
     * @throws \Exception
     */
    public function __construct()
    {
        $configFile = __DIR__ . '/../config.json';
        if (!file_exists($configFile)) {
            throw new \Exception($configFile . ' does not exist, please create it or rename config.sample.json.' . PHP_EOL);
        }

        $this->config = json_decode(file_get_contents($configFile), true);
    }

    /**
     * Get a value from the configuration.
     *
     * @param string  $key
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function get($key)
    {
        if (!isset($this->config[$key])) {
            throw new \Exception("Key $key is not in your configuration file.".PHP_EOL);
        }

        return $this->config[$key];
    }
} 