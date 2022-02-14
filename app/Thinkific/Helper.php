<?php

namespace App\Thinkific;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

class Helper
{
    /**
     * @param string $url
     * @param array  $query
     *
     * @return \Illuminate\Support\Collection
     */
    public static function makeGetCall(string $url, array $query = []): Collection
    {
        return Helper::createClientAndMakeAdminCalls($url)
            ->setQuery($query)
            ->get()
            ->collect();
    }

    private static function createClientAndMakeAdminCalls($url): HttpClient
    {
        $client = new HttpClient();

        return $client
            ->setUrl($url)
            ->setBearerAuth(Helper::getBearerToken());
    }

    /**
     * @param string $url
     * @param array  $data
     *
     * @return \Illuminate\Support\Collection
     */
    public static function makeDeleteCall(string $url, array $data = []): Collection
    {
        $client = Helper::createClientAndMakeAdminCalls($url);
        if (!empty($data)) {
            $client->setJson($data);
        }

        return $client->delete()->collect();
    }

    /**
     * @return string
     */
    private static function getBearerToken(): string
    {
        $manager = new TokenManager();
        $input = collect([Constants::SUBDOMAIN => Session::get(Constants::SUBDOMAIN)]);
        $manager->setInput($input);

        return $manager->getBearerToken();
    }

    /**
     * @param string $url
     * @param array  $data
     *
     * @return \Illuminate\Support\Collection
     */
    public static function makePostCall(string $url, array $data): Collection
    {
        return Helper::createClientAndMakeAdminCalls($url)
            ->setJson($data)
            ->post()
            ->collect();
    }
}
