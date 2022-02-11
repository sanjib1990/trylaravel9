<?php

namespace App\Thinkific\Repositories;

use Carbon\Carbon;
use App\Thinkific\Constants;
use App\Thinkific\HttpClient;
use App\Thinkific\UrlBuilder;
use App\Thinkific\Models\Order;
use App\Thinkific\TokenManager;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

class Repository
{
    /**
     * @var \App\Thinkific\Repositories\OrderRespository
     */
    private OrderRespository $order;

    /**
     * @var \App\Thinkific\Repositories\RecievedWebhookRespository
     */
    private RecievedWebhookRespository $recievedWebhookRespository;

    /**
     * @param \App\Thinkific\Repositories\OrderRespository           $orderRespository
     * @param \App\Thinkific\Repositories\RecievedWebhookRespository $recievedWebhookRespository
     */
    public function __construct(OrderRespository $orderRespository, RecievedWebhookRespository $recievedWebhookRespository)
    {
        $this->order = $orderRespository;
        $this->recievedWebhookRespository = $recievedWebhookRespository;
    }

    /**
     * @return \App\Thinkific\Repositories\OrderRespository
     */
    public function order(): OrderRespository
    {
        return $this->order;
    }

    /**
     * @return \App\Thinkific\Repositories\RecievedWebhookRespository
     */
    public function recievedWebhookRepository(): RecievedWebhookRespository
    {
        return $this->recievedWebhookRespository;
    }

    /**
     * @param \Illuminate\Support\Collection $request
     *
     * @return \Illuminate\Support\Collection
     */
    public function fetchCourses(Collection $request): Collection
    {
        $client = new HttpClient();
        $url = UrlBuilder::buildFetchCourseUrl();

        return $client
            ->setUrl($url)
            ->setBearerAuth($this->getBearerToken())
            ->setQuery($request->toArray())
            ->get()
            ->collect();
    }

    /**
     * @param \Illuminate\Support\Collection $request
     *
     * @return \Illuminate\Support\Collection
     */
    public function getUserByEmail(Collection $request): Collection
    {
        $client = new HttpClient();
        $url = UrlBuilder::buildUsersUrl();

        $query = [
            "query[email]" => $request->get(Constants::EMAIL),
        ];

        $userDetails = $client
            ->setUrl($url)
            ->setBearerAuth($this->getBearerToken())
            ->setQuery($query)
            ->get()
            ->collect();

        if (empty($userDetails->get('items', []))) {
            return collect();
        }

        return collect($userDetails->get("items")[0]);
    }


    /**
     * @param \Illuminate\Support\Collection $request
     *
     * @return \Illuminate\Support\Collection
     */
    public function registerStudent(Collection $request): Collection
    {
        $data = [
            "email"         => $request->get("email"),
            "last_name"     => $request->get("last_name"),
            "first_name"    => $request->get("first_name"),
        ];

        logger()->debug("[Registering User]", $data);

        $client = new HttpClient();
        $url = UrlBuilder::buildUsersUrl();

        return $client
            ->setUrl($url)
            ->setBearerAuth($this->getBearerToken())
            ->setJson($data)
            ->post()
            ->collect();
    }

    /**
     * @param \Illuminate\Support\Collection $request
     *
     * @return \Illuminate\Support\Collection
     */
    public function enrollStudent(Collection $request): Collection
    {
        logger()->debug("[Enroll]", $request->all());

        $client = new HttpClient();
        $url = UrlBuilder::buildEnrollmentUrl();

        return $client
            ->setUrl($url)
            ->setBearerAuth($this->getBearerToken())
            ->setJson($this->getEnrollData($request))
            ->post()
            ->collect();
    }

    /**
     * @param \Illuminate\Support\Collection $request
     *
     * @return \App\Thinkific\Models\Order
     */
    public function createExternalOrder(Collection $request): Order
    {
        logger()->debug("[ORDER]", $request->all());

        $this->preExternalOrderProcessor($request);

        $orderArr = $this->getOrderData($request);

        logger()->info("[CREATING ORDER]", $orderArr);

        $order = new Order($orderArr);

        $order->save();

        $data = $this->getExternalOrderData($order);

        logger()->debug("[Placing Thinkific Order]", $data);

        $client = new HttpClient();
        $url = UrlBuilder::buildExternalOrderUrl();

        $response = $client
            ->setUrl($url)
            ->setBearerAuth($this->getBearerToken())
            ->setJson($data)
            ->post()
            ->toArray();

        $order->setAttribute(Constants::EXTERNAL_ORDER_ID, $response['id']);
        $order->save();

        return $order;
    }

    /**
     * @param \App\Thinkific\Models\Order $order
     *
     * @return \Illuminate\Support\Collection
     */
    public function createThinkificRefundTransaction(Order $order): Collection
    {
        $data = $this->getRefundData($order);

        logger()->debug("[Refunding Order]", $data);

        $client = new HttpClient();
        $url = UrlBuilder::buildExternalRefundUrl($order->getAttribute(Constants::EXTERNAL_ORDER_ID));

        return $client
            ->setUrl($url)
            ->setBearerAuth($this->getBearerToken())
            ->setJson($data)
            ->post()
            ->collect();
    }

    /**
     * @param \Illuminate\Support\Collection $request
     *
     * @return \Illuminate\Support\Collection
     */
    public function fetchRegisteredWebhooks(Collection $request): Collection
    {
        $client = new HttpClient();
        $url = UrlBuilder::buildWebhookUrl();

        return $client
            ->setUrl($url)
            ->setBearerAuth($this->getBearerToken())
            ->get()
            ->collect();
    }

    /**
     * @param \Illuminate\Support\Collection $request
     *
     * @return \Illuminate\Support\Collection
     */
    public function registerWebhook(Collection $request): Collection
    {
        $url = UrlBuilder::buildWebhookUrl();
        $client = new HttpClient();

        return $client
            ->setUrl($url)
            ->setBearerAuth($this->getBearerToken())
            ->setJson($this->getRegisterWebhookData($request))
            ->post()
            ->collect();
    }

    /**
     * @param \Illuminate\Support\Collection $request
     *
     * @return \Illuminate\Support\Collection
     */
    public function deleteWebhook(Collection $request): Collection
    {
        $url = UrlBuilder::buildWebhookDeleteUrl($request->get("id"));
        $client = new HttpClient();

        return $client
            ->setUrl($url)
            ->setBearerAuth($this->getBearerToken())
            ->delete()
            ->collect();
    }

    /**
     * @return string
     */
    private function getBearerToken(): string
    {
        $manager = new TokenManager();
        $input = collect([Constants::SUBDOMAIN => Session::get(Constants::SUBDOMAIN)]);
        $manager->setInput($input);

        return $manager->getBearerToken();
    }

    private function preExternalOrderProcessor(Collection $request)
    {
        $request->put(Constants::COURSE_ID, $request->get("id"));
        $request->put(Constants::STUDENT_ID, $request->get(Constants::STUDENT_ID));
        $request->put(Constants::STUDENT_EMAIL, $request->get(Constants::EMAIL));
        $request->put(Constants::CURRENCY, Constants::CURRENCY_USD);
    }

    /**
     * @param \Illuminate\Support\Collection $request
     *
     * @return array
     */
    private function getEnrollData(Collection $request): array
    {
        return [
            "user_id"       => $request->get(Constants::STUDENT_ID),
            "course_id"     => $request->get("id"),
            "activated_at"  => Carbon::now()->toIso8601ZuluString(),
        ];
    }

    /**
     * @param \App\Thinkific\Models\Order $order
     *
     * @return array
     */
    private function getExternalOrderData(Order $order): array
    {
        return [
            Constants::USER_ID          => $order->getAttribute(Constants::STUDENT_ID),
            Constants::PRODUCT_ID       => $order->getAttribute(Constants::PRODUCT_ID),
            Constants::ORDER_TYPE       => $order->getAttribute(Constants::ORDER_TYPE),
            Constants::PAYMENT_PROVIDER => $order->getAttribute(Constants::PROVIDER),
            Constants::TRANSACTION  => [
                Constants::ACTION       => $order->getAttribute(Constants::ACTION),
                Constants::AMOUNT       => $order->getAttribute(Constants::AMOUNT),
                Constants::CURRENCY     => $order->getAttribute(Constants::CURRENCY),
                Constants::REFERENCE    => $order->getAttribute("id"),
            ],
        ];
    }

    /**
     * @param \Illuminate\Support\Collection $request
     *
     * @return array
     */
    private function getOrderData(Collection $request): array
    {
        return [
            Constants::STATUS           => Constants::STATUS_DEFAULT,
            Constants::ACTION           => Constants::ACTION_DEFAULT,
            Constants::AMOUNT           => $request->get(Constants::AMOUNT),
            Constants::PROVIDER         => Constants::PROVIDERD_DEFAULT,
            Constants::CURRENCY         => Constants::CURRENCY_USD,
            Constants::COURSE_ID        => $request->get(Constants::COURSE_ID),
            Constants::STUDENT_ID       => $request->get(Constants::STUDENT_ID),
            Constants::ORDER_TYPE       => Constants::ORDER_TYPE_DEFAULT,
            Constants::PRODUCT_ID       => $request->get(Constants::PRODUCT_ID),
            Constants::COURSE_NAME      => $request->get(Constants::COURSE_NAME),
            Constants::STUDENT_EMAIL    => $request->get(Constants::STUDENT_EMAIL),
        ];
    }

    /**
     * @param \App\Thinkific\Models\Order $order
     *
     * @return array
     */
    private function getRefundData(Order $order): array
    {
        return [
            Constants::AMOUNT => $order->getAttribute(Constants::AMOUNT),
            Constants::ACTION => $order->getAttribute(Constants::ACTION),
            Constants::CURRENCY =>$order->getAttribute(Constants::CURRENCY),
            Constants::REFERENCE => $order->id,
        ];
    }

    /**
     * @param \Illuminate\Support\Collection $request
     *
     * @return array
     */
    private function getRegisterWebhookData(Collection $request): array
    {
        return [
            "topic" => $request->get("event"),
            "target_url" => UrlBuilder::getWebhookUrl(),
        ];
    }
}
