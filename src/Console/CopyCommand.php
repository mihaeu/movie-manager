<?php

namespace Mihaeu\MovieManager\Console;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Lists all the (correctly formatted) movies in a directory.
 *
 * @package Mihaeu\MovieManager\Console
 *
 * @author Michael Haeuslmann (haeuslmann@gmail.com)
 */
class CopyCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        parent::configure();

        $this
          ->setName('copy')
          ->setDescription('Copies all the (correctly formatted) movies in a directory to another destination.')
          ->addArgument(
            'pathTo',
            InputArgument::REQUIRED,
            'Path to where the movies are supposed to be copied.'
          )
        ;
    }

    /**
     * Copies movies from a directory which have been previously parsed by
     * movie manager.
     *
     * @param InputInterface   $input
     * @param OutputInterface $output
     *
     * @return int|null
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $path = realpath($input->getArgument('path'));
        $pathTo = realpath($input->getArgument('pathTo'));
        if (!$path) {
            $output->writeln('<error>Read-Directory doesn\'t exist or is not readable.</error>');
            return self::RETURN_CODE_BAD_DIRECTORY;
        }
        if (!$pathTo || !is_writable($pathTo)) {
            $output->writeln('<error>Write-Directory doesn\'t exist or is not writable.</error>');
            return self::RETURN_CODE_BAD_DIRECTORY;
        }

        $movies = $this->getFilteredMovies($path, $input->getOptions());

        if (empty($movies)) {
            $output->writeln('<error>No movies found or no movies matched the filters.</error>');
            return self::RETURN_CODE_NO_MATCHES;
        }

        $output->writeln('<info>Copying '.count($movies).' movies to '.$pathTo.':</info>');
        $progressBar = new ProgressBar($output, count($movies));
        $progressBar->start();
        foreach ($movies as $movieDirectory => $movie) {
            $this->copy($movieDirectory, $pathTo);
            $progressBar->advance();
        }
        $progressBar->finish();
        $output->writeln('');

        return self::RETURN_CODE_OK;
    }

    /**
     * @param string $movieDirectory
     * @param string $pathTo
     */
    public function copy($movieDirectory, $pathTo)
    {
        $destination = $pathTo.'/'.basename($movieDirectory);
        if (!file_exists($destination)) {
            mkdir($destination);
        }
        $this->xcopy($movieDirectory, $destination);
    }

    /**
     * Copy a file, or recursively copy a folder and its contents
     *
     * @param       string   $source    Source path
     * @param       string   $dest      Destination path
     * @param       int      $permissions New folder creation permissions
     *
     * @return      bool     Returns true on success, false on failure
     */
    function xcopy($source, $dest, $permissions = 0755)
    {
        // Check for symlinks
        if (is_link($source)) {
            return symlink(readlink($source), $dest);
        }

        // Simple copy for a file
        if (is_file($source)) {
            return copy($source, $dest);
        }

        // Make destination directory
        if (!is_dir($dest)) {
            mkdir($dest, $permissions);
        }

        // Loop through the folder
        $dir = dir($source);
        while (false !== $entry = $dir->read()) {
            // Skip pointers
            if ($entry == '.' || $entry == '..') {
                continue;
            }

            // Deep copy directories
            $this->xcopy("$source/$entry", "$dest/$entry");
        }

        // Clean up
        $dir->close();
        return true;
    }
}
