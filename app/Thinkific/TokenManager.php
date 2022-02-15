<?php

namespace App\Thinkific;

use Carbon\Carbon;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use App\Thinkific\Models\Thinkific;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class TokenManager
{
    const CODE                      = "code";
    const GRAN_TYPE                 = "grant_type";
    const REFRESH_TOKEN             = "refresh_token";
    const CODE_VERIFIER             = "code_verifier";
    const AUTHORIZATION_CODE        = "authorization_code";
    const OAUTH_RESPONSE_TYPE       = "code";
    const OAUTH_RESPONSE_MODE       = "form_post";
    const CLIENT_ID_CONFIG_KEY      = "thinkific.client_id";
    const CLIENT_SECRET_CONFIG_KEY  = "thinkific.client_secret";

    const CODE_CHALLENGE_METHOD_VALUE   = "S256";

    const SUPPORTED_ALGOS = [
        self::CODE_CHALLENGE_METHOD_VALUE => "SHA256"
    ];
    /**
     * @var string
     */
    private string $grantType;
    /**
     * @var bool
     */
    private bool $isRefreshingToken;
    /**
     * @var \Illuminate\Support\Collection
     */
    private Collection $input;

    /**
     * @var \Illuminate\Support\Collection
     */
    private Collection $existingToken;
    /**
     * @var bool
     */
    private bool $debug;

    /**
     * @param array $input
     */
    public function __construct(array $input = [])
    {
        $this->existingToken = collect();
        $this->debug = Arr::get($input, Constants::DEBUG, Config::get(Constants::DEBUG_KEY));
        $this->setIsRefreshingToken(false);
    }

    /**
     * @return string
     */
    public static function generateState(): string
    {
        return Uuid::uuid4()->toString();
    }

    /**
     * @throws \Exception
     */
    public static function generateCodeVerifier(): string
    {
        return bin2hex(random_bytes(128));
    }

    /**
     * @param string $codeVerifier
     * @param string $codechallengeMethod
     *
     * @return string
     */
    public static function getCodeChallenge(string $codeVerifier, string $codechallengeMethod): string
    {
        $hash = hash(self::SUPPORTED_ALGOS[$codechallengeMethod], $codeVerifier);

        return base64_encode($hash);
    }

    /**
     * @return mixed
     */
    public static function getClientSecret(): string
    {
        return Config::get(self::CLIENT_SECRET_CONFIG_KEY);
    }

    public static function getClientId(): string
    {
        return Config::get(self::CLIENT_ID_CONFIG_KEY);
    }

    /**
     * @return string
     */
    public function getBasicAuthToken(): string
    {
        $secret = self::getClientSecret();
        $key    = self::getClientId();

        return base64_encode($key . ":" . $secret);
    }

    /**
     * @param \Illuminate\Support\Collection $input
     *
     * @return $this
     */
    public function setInput(Collection $input): static
    {
        $this->input = $input;

        return $this;
    }

    public function ensureToken()
    {
        if ($this->hasTokenExpired()) {
            $this->refreshToken();
        }
    }

    /**
     * @return mixed
     */
    public function getBearerToken(): string
    {
        $entry = $this->getExistingToken();

        if ($entry->isEmpty()) {
            throw new BadRequestException("Authenticate please", 400);
        }

        return $entry->get(Constants::TOKEN);
    }

    public function hasTokenExpired(): bool
    {
        $entry = $this->getExistingToken();

        if ($entry->isEmpty()) {
            return true;
        }

        $diff = Carbon::now()->diffInSeconds($entry[Constants::UPDATED_AT]);

        return $diff >= $entry[Constants::EXPIRES_IN];
    }

    public function refreshToken(): array
    {
        $existingToken = $this->getExistingToken();

        if ($existingToken->isEmpty()) {
            throw new BadRequestException("Cannot Refresh Token", 400);
        }

        $this->input->put(self::REFRESH_TOKEN, $existingToken->get(self::REFRESH_TOKEN));

        return $this->setIsRefreshingToken(true)->generateBearerToken();
    }

    /**
     * @return array
     */
    public function retrieveToken(): array
    {
        $entry = Thinkific::query()
            ->where(Constants::SUBDOMAIN, $this->input->get(Constants::SUBDOMAIN))
            ->first();

        return empty($entry) === false ? $entry->toArray() : [];
    }

    /**
     * @param bool $isRefreshingToken
     *
     * @return $this
     */
    public function setIsRefreshingToken(bool $isRefreshingToken): static
    {
        $this->isRefreshingToken = $isRefreshingToken;
        $this->setGrantType();

        return $this;
    }

    /**
     * @return array
     */
    public function generateBearerToken(): array
    {
        $url = UrlBuilder::getBearerAuthUrl($this->input->get(Constants::SUBDOMAIN));
        $client = new HttpClient();
        $response = $client
            ->setUrl($url)
            ->setBasicAuth($this->getBasicAuthToken())
            ->setJson($this->getTokenRequestBody())
            ->post()
            ->collect();

        if ($response->isEmpty())
        {
            throw new BadRequestException("Could generate token", 400);
        }

        $thinkific = [
            Constants::SUBDOMAIN    => $this->input->get(Constants::SUBDOMAIN),
            Constants::TOKEN        => $response->get(Constants::ACCESS_TOKEN),
            self::REFRESH_TOKEN     => $response->get(self::REFRESH_TOKEN),
            Constants::GID          => $response->get(Constants::GID),
            Constants::EXPIRES_IN   => $response->get(Constants::EXPIRES_IN),
        ];

        $this->storeToken($thinkific);

        return $thinkific;
    }

    public function generateBearerTokenWithPkce(): array
    {
        // Doesnt work, always get a repsonse "401 Unauthorized" even though the appropriate request is being passed
        $url = UrlBuilder::getBearerAuthUrl($this->input->get(Constants::SUBDOMAIN));
        $client = new HttpClient();
        $response = $client
            ->setUrl($url)
            ->setBasicAuth($this->getBasicAuthToken())
            ->setJson($this->getTokenRequestBody())
            ->post()
            ->collect();

        $thinkific = [
            Constants::SUBDOMAIN    => $this->input->get(Constants::SUBDOMAIN),
            Constants::TOKEN        => $response->get(Constants::ACCESS_TOKEN),
            self::REFRESH_TOKEN     => $response->get(self::REFRESH_TOKEN),
            Constants::GID          => $response->get(Constants::GID),
            Constants::EXPIRES_IN   => $response->get(Constants::EXPIRES_IN),
        ];

        $this->storeToken($thinkific);

        return $thinkific;
    }

    /**
     * @return string[]
     */
    private function getTokenRequestBody(): array
    {
        $body = [
            self::GRAN_TYPE => $this->grantType,
        ];
        if ($this->isRefreshingToken) {
            $body[self::REFRESH_TOKEN] = $this->input->get(self::REFRESH_TOKEN);
        } else {
            $body[self::CODE] = $this->input->get(self::CODE);
            $body[self::CODE_VERIFIER] = Cache::get(self::CODE_VERIFIER);
        }

        return $body;
    }

    /**
     * @return void
     */
    private function setGrantType(): void
    {
        $this->grantType = $this->isRefreshingToken ? self::REFRESH_TOKEN : self::AUTHORIZATION_CODE;
    }

    /**
     * @param array $thinkific
     *
     * @return void
     */
    private function storeToken(array $thinkific): void
    {
        Thinkific::upsert($thinkific, Constants::SUBDOMAIN);
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    private function getExistingToken(): Collection
    {
        if ($this->existingToken->isEmpty()) {
            $this->existingToken = collect($this->retrieveToken());
        }

        return $this->existingToken;
    }
}
