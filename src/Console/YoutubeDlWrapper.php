<?php declare(strict_types = 1);

namespace Mihaeu\MovieManager\Console;

class YoutubeDlWrapper
{
    public function __construct()
    {
        $this->ensureYoutubeDlIsInstalled();
    }

    public function download(string $movieFilenameWithoutExt, string $youtubeUrl)
    {
        $cmd = "youtube-dl "
            . "--format 22 '$youtubeUrl' "
            . "--output '$movieFilenameWithoutExt- Trailer.%(ext)s'";
        exec($cmd);

        return $this->trailerExistsInDestination($movieFilenameWithoutExt);
    }

    private function ensureYoutubeDlIsInstalled()
    {
        if (exec('youtube-dl --version') < 1) {
            throw new \InvalidArgumentException('YoutubeDl not installed, try running with the --no-trailer flag');
        }
    }

    private function trailerExistsInDestination($movieFilenameWithoutExt)
    {
        foreach (scandir(dirname($movieFilenameWithoutExt)) as $file) {
            if (strpos($file, basename($movieFilenameWithoutExt) . ' - Trailer.') !== false) {
                return true;
            }
        }
        return false;
    }
}
