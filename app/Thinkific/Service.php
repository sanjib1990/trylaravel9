<?php

namespace App\Thinkific;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Response;
use App\Thinkific\Repositories\Repository;

class Service
{
    /**
     * @var \App\Thinkific\Repositories\Repository
     */
    private Repository $repository;

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param \Illuminate\Support\Collection $request
     *
     * @return string
     */
    public function getOauthFlowUrl(Collection $request): string
    {
        $state      = TokenManager::generateState();
        $clientId   = TokenManager::getClientId();
        $subdomain  = $request->get(Constants::SUBDOMAIN);

        return UrlBuilder::buildOauthFlowUrl(
            $state,
            $clientId,
            $subdomain,
            TokenManager::OAUTH_RESPONSE_MODE,
            TokenManager::OAUTH_RESPONSE_TYPE
        );
    }

    /**
     * @param \Illuminate\Support\Collection $request
     *
     * @return void
     */
    public function handleOAuthCallback(Collection $request): void
    {
        $manager = new TokenManager();

        $manager->setInput($request)->generateBearerToken();
    }

    /**
     * @param \Illuminate\Support\Collection $request
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCourseList(Collection $request): Collection
    {
        return $this->repository->fetchCourses($request);
    }

    /**
     * @param \Illuminate\Support\Collection $request
     *
     * @return void
     */
    public function enroll(Collection $request): void
    {
        $userDetail = $this->repository->getUserByEmail($request);

        $request->put(Constants::STUDENT_ID, $userDetail->get("id"));

        $this->repository->enrollStudent($request);

        // create order
        $this->repository->createExternalOrder($request);
    }

    /**
     * @param \Illuminate\Support\Collection $request
     *
     * @return \Illuminate\Support\Collection
     */
    public function refund(Collection $request): Collection
    {
        $res = collect();

        $res->put("message", "Refunded Successfully");

        $order = $this->repository->order()->findById($request->get("id"));

        if ($order === null) {
            return $res;
        }

        $this->repository->createThinkificRefundTransaction($order);

        $this->repository->order()->refund($order);

        return $res;
    }

    /**
     * @param \Illuminate\Support\Collection $request
     *
     * @return \Illuminate\Support\Collection
     */
    public function fetchRecievedWebhooks(Collection $request): Collection
    {
        return $this->repository->recievedWebhookRepository()->fetch($request);
    }

    /**
     * @param \Illuminate\Support\Collection $request
     *
     * @return \Illuminate\Support\Collection
     */
    public function fetchOrders(Collection $request): Collection
    {
        return $this->repository->order()->fetch($request);
    }

    /**
     * @param \Illuminate\Support\Collection $request
     *
     * @return \Illuminate\Support\Collection
     */
    public function fetchRegisteredWebhooks(Collection $request): Collection
    {
        return $this->repository->fetchRegisteredWebhooks($request);
    }

    /**
     * @param \Illuminate\Support\Collection $request
     *
     * @return void
     */
    public function registerWebhooks(Collection $request): void
    {
        $this->repository->registerWebhook($request);
    }

    /**
     * @param \Illuminate\Support\Collection $request
     *
     * @return void
     */
    public function deleteWebhook(Collection $request): void
    {
        $this->repository->deleteWebhook($request);
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleIncomingWebhooks(Request $request): \Illuminate\Http\JsonResponse
    {
        logger()->info("[Recieved Webhook]", [
            'headers' => $request->headers->all(),
            'webhook_data' => $request->toArray(),
        ]);
        try {
            RequestIntegrity::validate($request);
        } catch (\Throwable $e) {
            logger()->error("[WEBHOOK CANNOT VALIDATE] Invalid data or corrupt data");

            return Response::json();
        }

        $data = $request->collect();

        $data->put(Constants::SUBDOMAIN, $request->headers->get(RequestIntegrity::THINKIFIC_HMAC_HEADER));
        $data->put(Constants::HEADERS, json_encode($request->headers->all()));
        $data->put(Constants::WEBHOOK_DATA, $request->getContent());

        $this->repository->recievedWebhookRepository()->create($data);

        return Response::json($request->toArray());
    }
}
