<?php declare(strict_types = 1);

namespace Mihaeu\MovieManager\IO;

class Downloader
{
    public function download(string $url, string $destination)
    {
        file_put_contents($destination, file_get_contents($url));
        return file_exists($destination);
    }
}
