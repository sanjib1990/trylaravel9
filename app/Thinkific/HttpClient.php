<?php

namespace App\Thinkific;

use GuzzleHttp\Client;
use Illuminate\Support\Arr;
use GuzzleHttp\Psr7\Response;
use JetBrains\PhpStorm\ArrayShape;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\GuzzleException;

class HttpClient
{
    const JSON              = "json";
    const QUERY             = "query";
    const BASIC             = "Basic";
    const BEARER            = "Bearer";
    const HEADERS           = "headers";
    const CONTENT_TYPE      = "Content-Type";
    const AUTHORIZATION     = "Authorization";
    const APPLICATION_JSON  = "application/json";
    /**
     * @var \GuzzleHttp\Client
     */
    private Client $client;
    /**
     * @var array
     */
    private array $json = [];
    /**
     * @var array
     */
    private array $query = [];
    /**
     * @var \Psr\Http\Message\ResponseInterface
     */
    private ResponseInterface $response;
    /**
     * @var array
     */
    private array $headers = [];
    /**
     * @var string
     */
    private string $url;

    /**
     * @param array $input
     */
    public function __construct(array $input = [])
    {
        $this->client = new Client($input);
    }

    /**
     * @param string $url
     *
     * @return $this
     */
    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return $this
     */
    public function setBearerAuth(string $token): static
    {
        $this->headers[self::AUTHORIZATION] = self::BEARER . " " . $token;

        return $this;
    }

    /**
     * @return $this
     */
    public function setBasicAuth(string $token): static
    {
        $this->headers[self::AUTHORIZATION] = self::BASIC . " " . $token;

        return $this;
    }

    /**
     * @return $this
     */
    public function post(): static
    {
        $this->makeCall("post");

        return $this;
    }

    /**
     * @return $this
     */
    public function get(): static
    {
        $this->makeCall("get");

        return $this;
    }

    /**
     * @return $this
     */
    public function put(): static
    {
        $this->makeCall("put");

        return $this;
    }

    public function delete(): static
    {
        $this->makeCall("delete");

        return $this;
    }

    /**
     * @return $this
     */
    public function jsonContentType(): static
    {
        $this->headers[self::CONTENT_TYPE] = self::APPLICATION_JSON;

        return $this;
    }

    /**
     * @param array $json
     *
     * @return $this
     */
    public function setJson(array $json): static
    {
        $this->json = $json;

        return $this;
    }

    /**
     * @param array $query
     *
     * @return $this
     */
    public function setQuery(array $query): static
    {
        $this->query = $query;

        return $this;
    }

    /**
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collect(): Collection
    {
        return collect($this->toArray());
    }

    /**
     * @return array|null
     */
    public function toArray(): array | null
    {
        if ($this->response->getStatusCode() >= 300) {
            return null;
        }

        $resString = $this->response->getBody()->getContents();

        return json_decode($resString, true);
    }

    /**
     * @return array
     */
    private function getRequestOptions(): array
    {
        return [
            self::HEADERS   => $this->headers,
            self::JSON      => $this->json,
            self::QUERY     => $this->query,
        ];
    }

    /**
     * @param string $method
     *
     * @return void
     */
    private function makeCall(string $method): void
    {
        $this->jsonContentType();

        try {
            $data = $this->getRequestOptions();
            logger()->debug("[MAKING CALL] $method", [$this->url, $data]);
            $this->response = $this->client->$method($this->url, $data);
        } catch (GuzzleException $e) {
            $this->response = new Response(500);
            logger()->debug("[Thinkific Client  Error]: " . $e->getMessage());
        }
    }
}
