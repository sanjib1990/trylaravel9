<?php

namespace App\Thinkific\Repositories;

use App\Thinkific\Constants;
use Illuminate\Support\Collection;
use App\Thinkific\Models\RecievedWebhook;

class RecievedWebhookRespository
{
    /**
     * @param \Illuminate\Support\Collection $request
     *
     * @return \Illuminate\Support\Collection
     */
    public function fetch(Collection $request): Collection
    {
        return RecievedWebhook::query()->orderByDesc(Constants::CREATED_AT)->get();
    }

    /**
     * @param \Illuminate\Support\Collection $request
     *
     * @return \App\Thinkific\Models\RecievedWebhook
     */
    public function create(Collection $request): RecievedWebhook
    {
        $data = $this->getBuildData($request);
        $recieved = new RecievedWebhook($data);
        $recieved->save();

        return $recieved;
    }

    /**
     * @param \Illuminate\Support\Collection $request
     *
     * @return array
     */
    private function getBuildData(Collection $request): array
    {
        return [
            Constants::SUBDOMAIN => $request->get(Constants::SUBDOMAIN),
            Constants::HOOK_ID => $request->get("id"),
            Constants::RESOURCE => $request->get(Constants::RESOURCE),
            Constants::ACTION => $request->get(Constants::ACTION),
            Constants::HEADERS => $request->get(Constants::HEADERS),
            Constants::WEBHOOK_DATA => $request->get(Constants::WEBHOOK_DATA),
        ];
    }
}
