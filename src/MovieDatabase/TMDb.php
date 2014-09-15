<?php

namespace Mihaeu\MovieManager\MovieDatabase;

use Symfony\Component\DomCrawler\Crawler;
use Tmdb\ApiToken;
use Tmdb\Client;
use Tmdb\Model\Collection\CreditsCollection;
use Tmdb\Model\Collection\Genres;
use Tmdb\Model\Collection\People\Cast;
use Tmdb\Model\Collection\People\Crew;
use Tmdb\Model\Common\GenericCollection;
use Tmdb\Model\Genre;
use Tmdb\Model\Movie;
use Tmdb\Model\Common\SpokenLanguage;
use Tmdb\Model\Company;
use Tmdb\Model\Person\CastMember;
use Tmdb\Model\Person\CrewMember;
use Tmdb\Model\Search\SearchQuery\MovieSearchQuery;
use Tmdb\Repository\MovieRepository;
use Tmdb\Repository\SearchRepository;
use Tmdb\Repository\ConfigurationRepository;
use Tmdb\Helper\ImageHelper;
use Tmdb\Model\Common\Country;

/**
 * TMDb Wrapper
 *
 * Wraps wtfzdotnet's TMDb library, especially since every library has different return types.
 *
 * @author Michael Haeuslmann (haeuslmann@gmail.com)
 */
class TMDb
{
    /**
     * @var Client
     */
    private $client;

    /**
     * Constructor.
     *
     * @param string $apiKey
     */
    public function __construct($apiKey)
    {
        $token  = new ApiToken($apiKey);
        $this->client = new Client($token);
    }

    /**
     * Searches TMDb for movies matching a query string.
     *
     * @param  string $query
     *
     * @return array
     */
    public function getMovieSuggestionsFromQuery($query)
    {
        $repository = new SearchRepository($this->client);
        $parameters = new MovieSearchQuery();
        $results = $repository->searchMovie($query, $parameters);

        $configRepository = new ConfigurationRepository($this->client);
        $config = $configRepository->load();
        $imageHelper = new ImageHelper($config);

        $suggestions = [];
        foreach ($results as $result) {
            /** @var Movie $result */

            // check filepath, because Image Helper returns url even if filepath is empty
            $url = '';
            $filepath = $result->getPosterImage()->getFilePath();
            if (!empty($filepath)) {
                $url = $imageHelper->getUrl($filepath, 'w342');
            }
            $suggestions[] = [
                'id'        => ((int) $result->getId()),
                'title'     => $result->getTitle(),
                'year'      => ((int) $result->getReleaseDate()->format('Y')),
                'poster'    => $url
            ];
        }
        return $suggestions;
    }

    /**
     * Get all movie information from TMDb.
     *
     * @param  int   $tmdbId
     * @return array
     */
    public function getMovieFromTmdbId($tmdbId)
    {
        $configRepository = new ConfigurationRepository($this->client);
        $config = $configRepository->load();
        $imageHelper = new ImageHelper($config);
        $movieRepository = new MovieRepository($this->client);

        /** @var Movie $movieResult */
        $movieResult = $movieRepository->load($tmdbId);
        /** @var CreditsCollection $credit */
        $credit = $movieRepository->getCredits($tmdbId);
        $movie = [
            'id'                    => $movieResult->getId(),
            'imdbId'                => $movieResult->getImdbId(),
            'adult'                 => $movieResult->getAdult(),
            'posterUrl'             => $imageHelper->getUrl($movieResult->getPosterImage()),
            'backdropUrl'           => $imageHelper->getUrl($movieResult->getBackdropImage()),
            'budget'                => $movieResult->getBudget(),
            'homepage'              => $movieResult->getHomepage(),
            'originalTitle'         => $movieResult->getOriginalTitle(),
            'overview'              => $movieResult->getOverview(),
            'popularity'            => $movieResult->getPopularity(),
            'releaseDate'           => $movieResult->getReleaseDate()->format('Y-m-d'),
            'year'                  => intval($movieResult->getReleaseDate()->format('Y')),
            'revenue'               => $movieResult->getRevenue(),
            'runtime'               => $movieResult->getRuntime(),
            'status'                => $movieResult->getStatus(),
            'tagline'               => $movieResult->getTagline(),
            'title'                 => $movieResult->getTitle(),
            'voteAverage'           => $movieResult->getVoteAverage(),
            'voteCount'             => $movieResult->getVoteCount(),
            'genres'                => $this->extractGenres($movieResult->getGenres()),
            'productionCompanies'   => $this->extractProductionCompanies($movieResult->getProductionCompanies()),
            'productionCountries'   => $this->extractProductionCountries($movieResult->getProductionCountries()),
            'spokenLanguages'       => $this->extractSpokenLanguages($movieResult->getSpokenLanguages()),
            'cast'                  => $this->extractCast($credit->getCast($credit)),
            'character'             => $this->extractCharacters($credit->getCast($credit)),
            'directors'             => $this->extractDirectors($credit->getCrew($credit))
        ];

        return $movie;
    }

    /**
     * Extract genres from the API respone to a simple array.
     *
     * @param  Genres $genres
     *
     * @return array
     */
    private function extractGenres(Genres $genres)
    {
        $plainGenres = [];
        foreach ($genres->getGenres() as $genre) {
            /** @var Genre $genre */
            $plainGenres[$genre->getId()] = $genre->getName();
        }
        return $plainGenres;
    }

    /**
     * Extract production companies from the API response into a simple array.
     *
     * @param  GenericCollection $companies
     *
     * @return array
     */
    private function extractProductionCompanies(GenericCollection $companies)
    {
        $plainCompanies = [];
        foreach ($companies->toArray() as $company) {
            /** @var Company $company */
            $plainCompanies[$company->getId()] = $company->getName();
        }
        return $plainCompanies;
    }

    /**
     * Extract production countries from the API response into a simple array.
     *
     * @param  GenericCollection $countries
     *
     * @return array
     */
    private function extractProductionCountries(GenericCollection $countries)
    {
        $plainCountries = [];
        foreach ($countries->toArray() as $country) {
            /** @var Country $country */
            $plainCountries[] = $country->getName();
        }
        return $plainCountries;
    }

    /**
     * Extract spoken languages from the API response into a simple array.
     *
     * @param  GenericCollection $spokenLanguages
     *
     * @return array
     */
    private function extractSpokenLanguages(GenericCollection $spokenLanguages)
    {
        $plainLanguages = [];
        foreach ($spokenLanguages->toArray() as $spokenLanguage) {
            /** @var SpokenLanguage $spokenLanguage*/
            $plainLanguages[] = $spokenLanguage->getName();
        }
        return $plainLanguages;
    }

    /**
     * @param Cast $cast
     *
     * @return array
     */
    public function extractCast(Cast $cast)
    {
        $castNames = [];
        foreach ($cast->getCast() as $castMember) {
            /** @var CastMember $castMember */
            $castNames[$castMember->getId()] = $castMember->getName();
        }
        return $castNames;
    }

    /**
     * @param Cast $cast
     *
     * @return array
     */
    public function extractCharacters(Cast $cast)
    {
        $characters = [];
        foreach ($cast->getCast() as $castMember) {
            /** @var CastMember $castMember */
            $characters[$castMember->getId()] = $castMember->getCharacter();
        }
        return $characters;
    }

    /**
     * @param Crew $crew
     *
     * @return array
     */
    public function extractDirectors(Crew $crew)
    {
        $directors = [];
        foreach ($crew->getCrew() as $crewMember) {
            /** @var CrewMember $crewMember */
            if ('Director' === $crewMember->getJob()) {
                $directors[$crewMember->getId()] = $crewMember->getName();
            }
        }
        return $directors;
    }

    /**
     * Retrieves the IMDb ID by crawling TMDb's site.
     *
     * This is a **HACK** and was only intended for one time use.
     * (Isn't it always?)
     *
     * TMDb search retrieves only a single result when searching
     * for an IMDb ID, so crawling the result is simple.
     *
     * @param  string $imdbId E.g. tt0068646
     *
     * @throws \Exception
     *
     * @return string
     */
    public static function getTmdbIdFromImdbId($imdbId)
    {
        $url = 'https://www.themoviedb.org/search?query=' . $imdbId;
        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $data = curl_exec($ch);
        curl_close($ch);

        $crawler = new Crawler($data);

        // this is highly volatile and was only used for a quick hack
        $xpath = '//*[@id="container"]/div[5]/div[1]/ul/li/div[2]/h3/a';

        $tmdbUrl = $crawler->filterXpath($xpath)->attr('href');
        $tmdbId = preg_replace('/^\/movie\/(\d+).*$/', '$1', $tmdbUrl);

        if (!is_numeric($tmdbId)) {
            throw new \Exception("TMDb ID \"$tmdbId\" extracted from \"$tmdbUrl\" is not valid" . PHP_EOL, 1);
        }

        return $tmdbId;
    }
}
