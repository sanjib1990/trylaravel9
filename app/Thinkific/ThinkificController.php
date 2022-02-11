<?php

namespace App\Thinkific;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;

class ThinkificController extends Controller
{
    /**
     * @var \App\Thinkific\Service
     */
    private Service $service;

    /**
     * @param \App\Thinkific\Service $service
     */
    public function __construct(Service $service)
    {
        $this->service = $service;
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
     * @return \Illuminate\Contracts\View\View
     */
    public function install(Request $request): \Illuminate\Contracts\View\View
    {
        return View::make("thinkific.install", $request->all());
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function webhooks(Request $request): \Illuminate\Contracts\View\View
    {
        return View::make("thinkific.webhook", $request->all());
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function authourizedPage(Request $request): \Illuminate\Contracts\View\View
    {
        return View::make("thinkific.page", $request->all());
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function viewRegisterAppWebhook(Request $request): \Illuminate\Contracts\View\View
    {
        return View::make("thinkific.webhook-register");
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function courses(Request $request): \Illuminate\Contracts\View\View
    {
        $coursesList = $this->service->getCourseList($request->collect());

        return View::make("thinkific.courses", compact($request->all(), "coursesList"));
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function startOauthFlow(Request $request): \Illuminate\Http\RedirectResponse
    {
        $url = $this->service->getOauthFlowUrl($request->collect());

        Session::put(Constants::SUBDOMAIN, $request->get(Constants::SUBDOMAIN));

        logger()->debug("[START OAUTH] With URL: ". $url);

        return Redirect::to($url);
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function callback(Request $request): RedirectResponse
    {
        logger()->info("[OAUTH CALLBACK RECIEVED]", $request->all());

        if ($request->has("error")) {
            return Redirect::route("install")->withErrors($request->get("error"));
        }

        $this->service->handleOAuthCallback($request->collect());

        Session::put(Constants::SUBDOMAIN, $request->get(Constants::SUBDOMAIN));

        return Redirect::route("authourizedPage");
    }

    public function enroll(Request $request)
    {
        $this->service->enroll($request->collect());

        return Redirect::back()->withInput(["message" => "Enrolled"]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function listOrders(Request $request): \Illuminate\Contracts\View\View
    {
        $orders = $this->service->fetchOrders($request->collect());

        return View::make("thinkific.orders", compact($request->all(), 'orders'));
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function listRecievedWebhooks(Request $request): \Illuminate\Contracts\View\View
    {
        $hooks = $this->service->fetchRecievedWebhooks($request->collect());

        return View::make("thinkific.received_webhook", ["webhooks" => $hooks]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function listRegisteredWebhooks(Request $request): \Illuminate\Contracts\View\View
    {
        $response = $this->service->fetchRegisteredWebhooks($request->collect());

        return View::make("thinkific.webhook-list", compact("response"));
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function refund(Request $request): RedirectResponse
    {
        $entry = $this->service->refund($request->collect());

        return Redirect::back()->withInput(["message" => $entry["message"]]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function registerAppWebhook(Request $request): RedirectResponse
    {
        $this->service->registerWebhooks($request->collect());

        return Redirect::back();
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteWebhooks(Request $request): RedirectResponse
    {
        $this->service->deleteWebhook($request->collect());

        return Redirect::back()->withInput(["message" => "Deleted"]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function hooks(Request $request)
    {
        return $this->service->handleIncomingWebhooks($request);
    }
}
