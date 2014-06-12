<?php

namespace Mihaeu\MovieManager;

class MovieHandler
{
	public static $ALLOWED_FORMATS = [
		'mkv',
		'mv4',
		'm4v',
		'mp4',
		'mpeg',
		'mpg',
		'avi',
		'rmvb',
		'm2ts'
	];

	/**
	 * TMDb API Wrapper
	 * 
	 * @var Object
	 */
	private $tmdb;

	/**
	 * Constructor instantiates TMDb.
	 */
	public function __construct()
	{
		$this->tmdb = new \TMDb('20f832e20d298376fcd4bc6a3b262108', 'en');
	}

	/**
	 * Looks recursively for movie files in a directory.
	 *
	 * @param  string $path   Path which contains the movies.
	 * @return array          matched movies
	 */
	public function findMoviesInDir($path = '')
	{
		if ( ! is_dir($path))
		{
			return [];
		}

		$path = realpath($path);

		$filenameChunks = [];
		$files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));
		$allowedExtensionsRegex = '/('.implode('|', $this::$ALLOWED_FORMATS).')/i';
		$filesArray = iterator_to_array($files);
		foreach($files as $name => $file)
		{
			if (preg_match($allowedExtensionsRegex, $file->getExtension())
				&& ! preg_match('/.*CD2\.\w+$/', $name))
			{
				$matches = [];
				$filename = $file->getBasename();
				preg_match('/^(.*)\.[a-z0-9]{2,4}$/i', $filename, $matches);
				$filenameWithoutExt = $matches[1];
				$chunks = preg_replace('/[\:\-\._\(\)\[\]]/', ' ', $filenameWithoutExt);
				$chunks = preg_replace('/  +/', ' ', $chunks);

				$formatOk = $folder = $link = $screenshot = $poster = false;
				$formatOk = preg_match('/[a-z0-9 \-\.]+ \(\d{4}\)\.[a-z0-9]{2,4}/i', $filename);
				if ($formatOk)
				{
					$folder = is_dir(realpath($file->getPath().'/../'.$filenameWithoutExt));

					$linkFile = $file->getPath().'/'.$filenameWithoutExt.' - IMDb.url';
					$link = file_exists($linkFile);

					$screenshotFile = $file->getPath().'/'.$filenameWithoutExt.' - IMDb.png';
					$screenshot = file_exists($screenshotFile);

					$posterFile = $file->getPath().'/'.$filenameWithoutExt.' - Poster.jpg';
					$poster = file_exists($posterFile);
				}

				$filenameChunks[$file->getBasename()] = [
					'name'			=> $filename,
					'fullname'		=> $name,
					'path'			=> $file->getPath(),
					'chunks'		=> explode(' ', trim($chunks)),
					'format'		=> (bool) $formatOk,
					'folder'		=> $folder,
					'link'			=> $link,
					'screenshot'	=> $screenshot,
					'poster'		=> $poster
				];
			}
		}

		ksort($filenameChunks);
		return $filenameChunks;
	}

	/**
	 * Search for movie matches on "The Movie Database tmdb.org"
	 *
	 * @param  string $query    movie query
	 * @return array            matches movies
	 */
	public function searchMoviesOnTMDb($query)
	{
		$query = $this->tmdb->searchMovie($query, 1, true, null, 'en');

		$movies = [];
		foreach ($query['results'] as $movie)
		{
			$movies[$movie['id']] = $this->getMovieFromTMDbResult($movie);
		}

		return $movies;
	}

	/**
	 * Handles movie related tasts like renaming, downloading the poster etc.
	 *
	 * @param  string   $file   Movie file
	 * @param  int      $imdbId IMDb ID which is also used by TMDb.org
	 * @return boolean          success flag
	 */
	public function handleMovie($file, $tmdbId)
	{
		$movie = $this->tmdb->getMovie($tmdbId);
		$imdbId = str_replace('tt', '', $movie['imdb_id']);

		$movieTitle = $this->convertMovieTitle($movie['title']);
		$movieYear = $this->convertMovieYear($movie['release_date']);

		// if the file is located in the base folder, put it in a separate folder first
		// if (realpath(dirname($file)) === realpath('/mnt/usb-passport/videos/neda-movies/flattened'))
		// {
		// 	$tmpFolder = dirname($file).'/'.md5($file);
		// 	mkdir($tmpFolder);

		// 	$newFile = $tmpFolder.'/'.basename($file);
		// 	rename(realpath($file), $newFile);

		// 	// change file location
		// 	$file = $newFile;
		// }

		$fileInfo = new \SplFileInfo($file);
		$filePath = $fileInfo->getPath();
		$fileExt = $fileInfo->getExtension();

		$movieFolder = realpath($filePath.'/..');

		// make sure the crucial parts are in order
		if (empty($movieTitle) || empty($imdbId) || empty($movieYear))
		{
			return false;
		}

		$this->renameFile($movieTitle, $movieYear, $file, $filePath, $fileExt);
		$this->createIMDbLink($movieTitle, $movieYear, $filePath, $imdbId, $movie);
		$this->downloadMoviePoster($movieTitle, $movieYear, $filePath, $movie);
		$this->renameMovieFolder($movieTitle, $movieYear, $filePath, $movieFolder);

		return true;
	}

	private function getMovieFromTMDbResult(Array $movie)
	{
		return [
			'id'                 => $movie['id'],
			'title'              => $movie['title'],
			'year'               => (int) date('Y', strtotime($movie['release_date'])),
			'link'               => 'http://imdb.com/title/tt'.$movie['id'],
			'posterThumbnailSrc' => $this->tmdb->getImageUrl($movie['poster_path'],
									\TMDb::IMAGE_PROFILE, 'w185')
	    ];
	}

	public function convertMovieTitle($originalTitle)
	{
		// : is not allowed in most OS, replace with - and add spaces
		$movieTitle = str_replace(':', ' - ', $originalTitle);

		// replace other illegal characters with spaces
		$movieTitle = preg_replace('/[\/\:\*\?"\\<>\|]/', ' ', $movieTitle);

		// trim spaces to one space max
		$movieTitle = preg_replace('/  +/', ' ', $movieTitle);

		return $movieTitle;
	}

	public function convertMovieYear($originalReleaseDate)
	{
		return date('Y', strtotime($originalReleaseDate));
	}

	private function renameFile($movieTitle, $movieYear,$file, $filePath, $fileExt)
	{
		rename($file, "$filePath/$movieTitle ($movieYear).$fileExt");
	}

	private function renameMovieFolder($movieTitle, $movieYear, $filePath, $movieFolder)
	{
		rename($filePath, "$movieFolder/$movieTitle ($movieYear)");
	}

	private function createIMDbLink($movieTitle, $movieYear, $filePath, $imdbId, Array $movie)
	{
		$movie['info'] = [];
		// we don't want loose values without sections
		// and we don't want empty section
		// because we're going to render it into the INI format
		foreach ($movie as $key => $value)
		{
			if ( ! is_array($value))
			{
				$movie['info'][$key] = $value;
				unset($movie[$key]);
			}
			else
			{
				switch ($key)
				{
					case 'genres':
						foreach ($movie['genres'] as $key => $genre)
						{
							$movie['genres'][$genre['id']] = $genre['name'];
							unset($movie['genres'][$key]);
						}
					break;
					case 'production_companies':
						foreach ($movie['production_companies'] as $key => $company)
						{
							$movie['production_companies'][$company['id']] = $company['name'];
							unset($movie['production_companies'][$key]);
						}
					break;
					case 'production_countries':
						foreach ($movie['production_countries'] as $key => $country)
						{
							$movie['production_countries'][$country['iso_3166_1']] = $country['name'];
							unset($movie['production_countries'][$key]);
						}
					break;
					case 'spoken_languages':
						foreach ($movie['spoken_languages'] as $key => $language)
						{
							$movie['spoken_languages'][$language['iso_639_1']] = $language['name'];
							unset($movie['spoken_languages'][$key]);
						}
					break;
				}
			}
		}

		$url = $this->getIMDbLink($imdbId);
		$internetShortcut = "[InternetShortcut]\rURL=$url\r";
		$iniArray = [
			'InternetShortcut' => [
				'URL' => $url
			]
		] + $movie;

		$this->writeIniFile($iniArray, "$filePath/$movieTitle ($movieYear) - IMDb.url");
	}

	private function getIMDbLink($imdbId)
	{
		if (strpos($imdbId, 'tt') === false)
		{
			return 'http://www.imdb.com/title/tt'.$imdbId;
		}
		else
		{
			return 'http://www.imdb.com/title/'.$imdbId;
		}
	}

	private function downloadMoviePoster($movieTitle, $movieYear, $filePath, Array $movie)
	{
		$posterSrc = $this->tmdb->getImageUrl(
			$movie['poster_path'],
			\TMDb::IMAGE_PROFILE,
			'original'
		);
		$srcExtension = substr(strrchr($posterSrc, '.'), 1);
		
		file_put_contents(
			"$filePath/$movieTitle ($movieYear) - Poster.$srcExtension",
			file_get_contents($posterSrc));

		// take a screenshot with PhantomJS and save it
		$output = '';
		$url = $this->getIMDbLink($movie['imdb_id']);
		$script = __DIR__.'/../../../../assets/js/rasterize.js';
		$target = "$filePath/$movieTitle ($movieYear) - IMDb.png";
		$cmd = "phantomjs $script \"$url\" \"$target\"";
		system($cmd, $output);

		return 0 === $output;
	}

	/**
	 * Parses a PHP array to INI format and writes the result to a file.
	 *
	 * @param array $data
	 * @param string $path
	 */
	private function writeIniFile($data, $path)
	{
		$content = '';
		if (is_array($data))
		{
			foreach ($data as $key => $value)
			{
				if (is_array($value))
				{
					if ( ! empty($value))
					{
						$content .= "[$key]\r\n";
					}
					foreach ($value as $subkey => $subvalue)
					{
						if (is_array($subvalue))
						{
							if ( ! empty($value))
							{
								$content .= "[$key\\$subkey]\r\n";
							}
							foreach ($subvalue as $subsubkey => $subsubvalue)
							{
								if (is_numeric($subsubvalue))
								{
									$content .= "$subsubkey=$subsubvalue\r\n";
								}
								else
								{
									$subsubvalue = str_replace('"', "'", $subsubvalue);
									$content .= "$subsubkey=\"$subsubvalue\"\r\n";
								}
							}
							$content .= "\r\n";
						}
						else
						{
							if (is_numeric($subvalue))
							{
								$content .= "$subkey=$subvalue\r\n";
							}
							else
							{
								$subvalue = str_replace('"', "'", $subvalue);
								$content .= "$subkey=\"$subvalue\"\r\n";
							}
						}
					}
					$content .= "\r\n";
				}
				else
				{
					if (is_numeric($value))
					{
						$content .= "$key=$value\r\n";
					}
					else
					{
						$value = str_replace('"', "'", $value);
						$content .= "$key=\"$value\"\r\n";
					}
				}
			}
		}
		else
		{
			return false;
		}

		file_put_contents($path, $content);
	}
}
