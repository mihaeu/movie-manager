<?php declare(strict_types = 1);

namespace Mihaeu\MovieManager\Console;

class PhantomJsWrapper
{
    public function __construct()
    {
        $this->ensurePhantomJsIsInstalled();
    }

    public function downloadScreenshot(string $url, string $destination) : bool
    {
        $script = __DIR__.'/../../rasterize.js';
        $cmd = "phantomjs $script \"$url\" \"$destination\"";
        $returnVal = 1;
        $output = [];
        exec($cmd, $output, $returnVal);

        return 0 === $returnVal;
    }

    private function ensurePhantomJsIsInstalled()
    {
        if (exec('phantomjs --version') < 1) {
            throw new \Exception("PhantomJS is not installed.");
        }
    }
}
