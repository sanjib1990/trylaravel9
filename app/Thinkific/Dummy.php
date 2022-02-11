<?php

namespace App\Thinkific;

use Carbon\Carbon;
use Ramsey\Uuid\Uuid;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Thinkific\Models\Order;
use Illuminate\Support\Collection;
use App\Thinkific\Models\Thinkific;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use GuzzleHttp\Exception\ClientException;
use App\Thinkific\Models\RecievedWebhook;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class Dummy extends Controller {
    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function install(Request $request): \Illuminate\Contracts\View\View
    {
        return View::make("thinkific.install", $request->all());
    }

    public function webhooks(Request $request)
    {
        return View::make("thinkific.webhook", $request->all());
    }

    public function authourizedPage(Request $request)
    {
        return View::make("thinkific.page", $request->all());
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return array|\Illuminate\Http\RedirectResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function callback(Request $request)
    {
        if ($request->has("error")) {
            return Redirect::route("install")->withErrors($request->get("error"));
        }
        $data = $request->collect();
        $this->requestForToken($data);
        Session::put("subdomain", $request->get("subdomain"));

        return Redirect::route("authourizedPage");
    }

    /**
     * @return array
     */
    private function getTokenData(): array
    {
        $subdomain = Session::get("subdomain");

        return Thinkific::query()->where("subdomain", $subdomain)->first()->toArray();
    }

    /**
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function refreshToken(): array
    {
        $thinkific = Thinkific::query()->where("subdomain", Session::get("subdomain"))->first();
        $data = collect($thinkific->toArray());
        $response = $this->requestForToken($data, "refresh_token");
        Session::put("token", $response);

        return $response;
    }

    public function hasTokenExpired(): bool
    {
        $subdomain = Session::get("subdomain");
        $entry = Thinkific::query()->where("subdomain", $subdomain)->first()->toArray();
        if (empty($entry)) {
            return true;
        }
        $diff = Carbon::now()->diffInSeconds($entry['updated_at']);

        return $diff >= $entry['expires_in'];
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function support(Request $request): \Illuminate\Contracts\View\View
    {
        return View::make('thinkific.support', $request->toArray());
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function startOauthFlow(Request $request): \Illuminate\Http\RedirectResponse
    {
        $state = $this->getState();
        $subdomain = $request->get("subdomain");
        $clientId = $this->getClientId();
        $redirectUrl = $this->getRedirectUrl();
        $requestMode = $this->getRequestMode();
        if (empty($state) || empty($subdomain) || empty($clientId) || empty($redirectUrl) || empty($requestMode)) {
            return Redirect::back()->withErrors("One or more input were not provided");
        }
        Session::put("subdomain", $subdomain);
        $url = "https://$subdomain.thinkific.com/oauth2/authorize?"
            . "client_id=$clientId&"
            . "redirect_uri=$redirectUrl&"
            . "response_mode=$requestMode&"
            . "response_type=code&"
            . "state=$state";

        return Redirect::to($url);
    }

    /**
     * @return string
     */
    private function getState(): string
    {
        return Uuid::uuid4()->toString();
    }

    /**
     * @return string
     */
    private function getClientId(): string
    {
        return Config::get("thinkific.client_id");
    }

    /**
     * @return string
     */
    private function getRedirectUrl(): string
    {
        $appUrl = Config::get("app.url");

        return $appUrl . Config::get("thinkific.redirect_uri");
    }

    /**
     * @return string
     */
    private function getRequestMode(): string
    {
        return "query";
    }

    /**
     * @return string
     */
    private function getClientSecret(): string
    {
        return Config::get("thinkific.client_secret");
    }

    /**
     * @param \Illuminate\Support\Collection $request
     * @param                                $grantType
     *
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function requestForToken(Collection $request, $grantType = "authorization_code"): array
    {
        $body = ["grant_type" => $grantType];
        if ($grantType === "authorization_code") {
            $body['code'] = $request->get('code');
        }
        if ($grantType === "refresh_token") {
            $body['refresh_token'] = $request->get('refresh_token');
        }
        $client = new Client();
        $response = $client->post("https://{$request->get('subdomain')}.thinkific.com/oauth2/token", [
            "headers" => ["Authorization" => "Basic " . base64_encode($this->getClientId() . ":" . $this->getClientSecret())],
            "json" => $body,
        ]);
        $resString = $response->getBody()->getContents();
        $response = json_decode($resString, true);
        $thinkific = [
            "subdomain" => $request->get("subdomain"),
            "token" => $response['access_token'],
            "refresh_token" => $response['refresh_token'],
            "gid" => $response['gid'],
            "expires_in" => $response["expires_in"],
        ];
        Thinkific::upsert($thinkific, "subdomain");

        return $thinkific;
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Contracts\View\View
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function courses(Request $request)
    {
        $coursesList = collect($this->callThinkific("get", "/courses", []));

        return View::make("thinkific.courses", compact($request->all(), "coursesList"));
    }

    public function viewRegisterAppWebhook(Request $request)
    {
        return View::make("thinkific.webhook-register");
    }

    public function registerUser(Request $request)
    {
        $data = [
            "email" => $request->get("email"),
            "first_name" => $request->get("first_name"),
            "last_name" => $request->get("last_name"),
        ];
        logger()->debug("[Registering User]", $data);

        return $this->callThinkific("post", "/users", ["json" => $data]);
    }

    public function enroll(Request $request)
    {
        $email = $request->get('email');
        $userDetails = $this->callThinkific("get", "/users", [
            "query" => [
                "query[email]" => $email,
            ],
        ]);
        $userDetails = collect($userDetails);
        if (empty($userDetails->get('items', []))) {
            $userDetail = $this->registerUser($request);
        } else {
            $userDetail = $userDetails->get("items")[0];
            logger()->debug("[User Exists]", $userDetail);
        }
        $data = [
            "course_id" => $request->get("id"),
            "user_id" => $userDetail['id'],
            "activated_at" => Carbon::now()->toIso8601ZuluString(),
        ];
        logger()->debug("[Enroll]", $data);
        $this->callThinkific("post", "/enrollments", [
            "json" => $data,
        ]);
        // create order
        $request->request->set("course_id", $request->get("id"));
        $request->request->set("student_id", $userDetail['id']);
        $request->request->set("student_email", $email);
        $request->request->set("currency", "USD");
        $this->createExternalOrder($request);

        return Redirect::back()->withInput(["message" => "Enrolled"]);
    }

    public function getEnorlments(Request $request)
    {
        $queries = [
            "query[course_id]" => $request->get("course_id"),
        ];
        if ($request->has("email")) {
            $queries["query[email]"] = $request->get("email");
        }

        return $this->callThinkific("get", "/enrollments", [
            "query" => $queries,
        ]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function listOrders(Request $request): \Illuminate\Contracts\View\View
    {
        $orders = Order::query()->orderByDesc("created_at")->get();

        return View::make("thinkific.orders", compact($request->all(), 'orders'));
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function createExternalOrder(Request $request): array
    {
        $orderArr = [
            "student_email" => $request->get("student_email"),
            "student_id" => $request->get("student_id"),
            "course_id" => $request->get("course_id"),
            "course_name" => $request->get("course_name"),
            "product_id" => $request->get("product_id"),
            "amount" => $request->get("amount"),
            "currency" => "USD",
            "provider" => "ExternalProviderSanjib",
            "order_type" => "one-time",
            "action" => "purchase",
            "status" => "purchased",
        ];
        logger()->debug("[Creating Order]", $orderArr);
        $order = new Order($orderArr);
        $order->save();
        $collectOrder = collect($order->toArray());
        $data = [
            "payment_provider" => $collectOrder->get("provider"),
            "user_id" => $collectOrder->get("student_id"),
            "product_id" => $collectOrder->get("product_id"),
            "order_type" => $collectOrder->get("order_type"),
            "transaction" => [
                "amount" => $collectOrder->get("amount"),
                "currency" => $collectOrder->get("currency"),
                "reference" => $collectOrder->get("id"),
                "action" => $collectOrder->get("action"),
            ],
        ];
        logger()->debug("[Placing Order]", $data);
        $response = $this->callThinkific("post", "/external_orders", [
            "json" => $data,
        ]);
        $order->setAttribute("external_order_id", $response['id']);
        $order->save();

        return $order->toArray();
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function refund(Request $request)
    {
        $message = "Refunded Successfully";
        try {
            $this->createRefundTransaction($request);
        } catch (ClientException $e) {
            $message = $e->getMessage();
        }

        return Redirect::back()->withInput(["message" => $message]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function createRefundTransaction(Request $request): array
    {
        $order = Order::query()->find($request->get("id"));
        if (empty($order)) {
            return [];
        }
        $order->setAttribute("status", "refunded");
        $order->save();
        $collectOrder = collect($order->toArray());
        $data = [
            "amount" => $collectOrder->get("amount"),
            "currency" => $collectOrder->get("currency"),
            "reference" => $collectOrder->get("id"),
            "action" => $collectOrder->get("action"),
        ];
        logger()->debug("[Refunding Order]", $data);
        $this->callThinkific("post",
            "/external_orders/" . $order->external_order_id . "/transactions/refund",
            [
                "json" => $data,
            ]
        );

        return $order->toArray();
    }

    public function deactivateEnrollment(Request $request)
    {
        $email = $request->get('email');
        $userDetails = $this->callThinkific("get", "/users", [
            "query" => [
                "query[email]" => $email,
            ],
        ]);
        $userDetails = collect($userDetails);
        if (empty($userDetails->get('items', []))) {
            return Redirect::back()->withInput(["message" => "No student found with email " . $email]);
        }
        $userDetail = $userDetails->get("items")[0];
        logger()->debug("[User Exists]", $userDetail);
        $enrolments = $this->getEnorlments($request);
        if (empty($enrolments) || empty($enrolments['items'])) {
            return Redirect::back()->withInput(["message" => "No enrolment for student " . $email]);
        }
        $data = [
            "course_id" => $request->get("id"),
            "user_id" => $userDetail['id'],
            "expiry_date" => Carbon::now()->addMinutes(-5)->toIso8601ZuluString(),
        ];
        logger()->debug("[Enroll]", [$data, $enrolments]);
        $this->callThinkific("put", "/enrollments/" . $enrolments['items'][0]['id'], [
            "json" => $data,
        ]);

        return Redirect::back()->withInput(["message" => "Enrolment deactivated Successfully"]);
    }

    public function registerAppWebhook(Request $request)
    {
        $event = $request->get("event");
        $hook = Config::get("app.url") . "/api/events/hooks";
        try {
            $this->callThinkific("post", "/webhooks", [
                "json" => [
                    "topic" => $event,
                    "target_url" => $hook,
                ],
            ], true);
        } catch (ClientException $e) {
            logger()->error("Register error ==> " . $e->getMessage());
        }

        return Redirect::back();
    }

    public function listRecievedWebhooks(Request $request)
    {
        $hooks = RecievedWebhook::query()->orderByDesc("created_at")->get();

        return View::make("thinkific.received_webhook", ["webhooks" => $hooks]);
    }

    public function listRegisteredWebhooks(Request $request)
    {
        [$response, $status, $error] = $this->getListOfWebhooks();

        return View::make("thinkific.webhook-list", compact("response"))->with(["message" => $status, "error" => $error]);
    }

    /**
     * @return array
     */
    private function getListOfWebhooks(): array
    {
        $response = [];
        $status = "Success";
        $error = "";
        try {
            $response = $this->callThinkific("get", "/webhooks", [], true);
        } catch (ClientException $e) {
            $status = "Failed";
            $error = $e->getMessage();
        }
        $response = collect($response);

        return [$response, $status, $error];
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteWebhooks(Request $request)
    {
        try {
            $this->callThinkific("delete", "/webhooks/" . $request->get("id"), [], true);
        } catch (ClientException $e) {
            logger()->error("Delete error: " . $e->getMessage());
        }

        return Redirect::back()->withInput(["message" => "Deleted"]);
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array  $input
     * @param bool   $isWebhook
     *
     * @return array|void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function callThinkific(string $method, string $uri = "", array $input = [], bool $isWebhook = false)
    {
        $baseUrl = Config::get('thinkific.base_url');
        $baseUri = Config::get('thinkific.admin_uri');
        $this->assureToken();
        $tokenData = $this->getTokenData();
        if ($isWebhook) {
            $baseUri = Config::get('thinkific.webhook_uri');
        }
        $url = $baseUrl . $baseUri . $uri;
        $data = [
                "headers" => [
                    "Authorization" => "Bearer " . $tokenData['token'],
                    "Content-Type" => "application/json",
                ],
            ] + $input;
        $client = new Client();
        /**
         * @var \Psr\Http\Message\ResponseInterface
         */
        $response = null;
        switch ($method) {
            case "post":
                $response = $client->post($url, $data);
                break;
            case "get":
                $response = $client->get($url, $data);
                break;
            case "put":
                $response = $client->put($url, $data);
                break;
            case "delete":
                $response = $client->delete($url, $data);
                break;
            case "default":
                return [];
        }
        $resString = $response->getBody()->getContents();

        return json_decode($resString, true);
    }

    /**
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function assureToken()
    {
        if ($this->hasTokenExpired()) {
            $this->refreshToken();
        }
    }

    public function hooks(Request $request): array
    {
        logger()->info("[Recieved Webhook]", [
            'headers' => $request->headers->all(),
            'webhook_data' => $request->toArray(),
        ]);
        try {
            $this->validateRequest($request);
        } catch (\Throwable $e) {
            logger()->error("[WEBHOOK CANNOT VALIDATE] Invalid data or corrupt data");

            return [];
        }
        $data = [
            'subdomain' => $request->headers->get("x-thinkific-subdomain"),
            'hook_id' => $request->get("id"),
            'resource' => $request->get("resource"),
            'action' => $request->get("action"),
            'headers' => collect($request->headers->all())->toJson(),
            'webhook_data' => $request->getContent(),
        ];
        $recieved = new RecievedWebhook($data);
        $recieved->save();

        return [
            'webhook_url' => "",
            'headers' => collect($request->headers->all())->toJson(),
            'webhook_data' => $request->collect()->toJson(),
        ];
    }

    private function validateRequest(Request $request)
    {
        $secret = Config::get("thinkific.client_secret");
        $jsonData = $request->getContent();
        $hmacKey = $request->headers->get("X-Thinkific-Hmac-Sha256");
        $currentHmac256 = hash_hmac("sha256", $jsonData, $secret);
        if ($currentHmac256 !== $hmacKey) {
            throw new BadRequestException("Invalid data or corrupt data");
        }
    }
}
