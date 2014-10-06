<?php

namespace Mihaeu\MovieManager\MovieDatabase;

use Guzzle\Http\ClientInterface;
use Tmdb\ApiToken;
use Tmdb\Client;
use Tmdb\Model\Collection\CreditsCollection;
use Tmdb\Model\Collection\Genres;
use Tmdb\Model\Collection\People\Cast;
use Tmdb\Model\Collection\People\Crew;
use Tmdb\Model\Collection\Videos;
use Tmdb\Model\Common\GenericCollection;
use Tmdb\Model\Common\Video;
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
     * @param string          $apiKey
     * @param ClientInterface $httpClient
     */
    public function __construct($apiKey, ClientInterface $httpClient = null)
    {
        $token  = new ApiToken($apiKey);
        $this->client = new Client($token, $httpClient);
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
        $result = $repository->searchMovie($query, new MovieSearchQuery());

        $suggestions = [];
        foreach ($result as $movieSuggestion) {
            /** @var Movie $movieSuggestion */
            $suggestions[] = [
                'id'        => (int) $movieSuggestion->getId(),
                'title'     => $movieSuggestion->getTitle(),
                'year'      => (int) $movieSuggestion->getReleaseDate()->format('Y'),
                'poster'    => $this->getPoster($movieSuggestion)
            ];
        }
        return $suggestions;
    }

    /**
     * @param Movie $movie
     *
     * @return string
     */
    public function getPoster(Movie $movie)
    {
        $configRepository = new ConfigurationRepository($this->client);
        $config = $configRepository->load();
        $imageHelper = new ImageHelper($config);

        $filepath = $movie->getPosterImage()->getFilePath();
        $posterUrl = '';
        if (!empty($filepath)) {
            $posterUrl = $imageHelper->getUrl($filepath, 'w342');
        }

        return $posterUrl;
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
            'directors'             => $this->extractDirectors($credit->getCrew($credit)),
            'trailer'               => $this->extractTrailer($movieRepository->getVideos($tmdbId))
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
     * @param Videos $videos
     *
     * @return null|string
     */
    public function extractTrailer(Videos $videos)
    {
        foreach ($videos as $video) {
            /** @var Video $video */
            if ('Trailer' === $video->getType() && 'YouTube' === $video->getSite()) {
                return 'https://www.youtube.com/watch?v='.$video->getKey();
            }
        }
        return false;
    }
}
