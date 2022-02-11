<?php

namespace App\Thinkific;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Config;

class UrlBuilder
{
    const USERS_URI                     = "/users";
    const REFUND_URI                    = "/transactions/refund";
    const COURSES_URI                   = "/courses";
    const WEBHOOK_URI                   = "/webhooks";
    const ENROLLMENT_URI                = "/enrollments";
    const AUTH_TOKEN_URI                = "/oauth2/token";
    const WEBHOOK_BASE_URI              = "/api/v2";
    const EXTERNAL_ORDER_URI            = "/external_orders";
    const OAUTH_AUTHORIZE_URI           = "/oauth2/authorize";
    const THINKIFIC_ADMIN_BASE_URI      = "/api/public/v1";

    const APP_URL_KEY                   = "app.url";
    const BASE_URL_PATTERN_KEY          = "thinkific.base_url_pattern";

    const WEBHOOK_HANDLER_ROUTE_NAME            = "hooks.handler";
    const THINKIFIC_OAUTH_CALLBACK_ROUTE_NAME   = "thinkific.oauth.callback";

    /**
     * @param string $subdomain
     *
     * @return string
     */
    public static function getBearerAuthUrl(string $subdomain): string
    {
        $domain = self::getThinkificBaseUrl($subdomain);

        return $domain . self::AUTH_TOKEN_URI;
    }

    /**
     * @return string
     */
    private static function buildOauthFlowRedirectUrl(): string
    {
        return Config::get(self::APP_URL_KEY) . self::getUriByRouteName(self::THINKIFIC_OAUTH_CALLBACK_ROUTE_NAME);
    }

    /**
     * @param string $state
     * @param string $clientId
     * @param string $subdomain
     * @param string $responseMode
     * @param string $responseType
     *
     * @return string
     */
    public static function buildOauthFlowUrl(
        string $state,
        string $clientId,
        string $subdomain,
        string $responseMode,
        string $responseType
    ): string {
        $domain = self::getThinkificBaseUrl($subdomain);

        return $domain
            . self::OAUTH_AUTHORIZE_URI
            . "?"
            . "client_id=$clientId&"
            . "redirect_uri=". self::buildOauthFlowRedirectUrl() ."&"
            . "response_mode=$responseMode&"
            . "response_type=$responseType&"
            . "state=$state";
    }

    /**
     * @return string
     */
    public static function buildFetchCourseUrl(): string
    {
        return self::getAdminBaseURI() . self::COURSES_URI;
    }

    /**
     * @return string
     */
    public static function buildUsersUrl(): string
    {
        return self::getAdminBaseURI() . self::USERS_URI;
    }

    /**
     * @return string
     */
    public static function buildEnrollmentUrl(): string
    {
        return self::getAdminBaseURI() . self::ENROLLMENT_URI;
    }

    /**
     * @return string
     */
    public static function buildExternalOrderUrl(): string
    {
        return self::getAdminBaseURI() . self::EXTERNAL_ORDER_URI;
    }

    /**
     * @param string $subdomain
     *
     * @return string
     */
    public static function getThinkificBaseUrl(string $subdomain = "api"): string
    {
        return sprintf(Config::get(self::BASE_URL_PATTERN_KEY), $subdomain);
    }

    /**
     * @param string $uri
     *
     * @return string
     */
    public static function getAdminBaseURI(string $uri = self::THINKIFIC_ADMIN_BASE_URI): string
    {
        return self::getThinkificBaseUrl() . $uri;
    }

    /**
     * @param string $externalOrderId
     *
     * @return string
     */
    public static function buildExternalRefundUrl(string $externalOrderId): string
    {
        return self::buildExternalOrderUrl() . "/" . $externalOrderId . self::REFUND_URI;
    }

    /**
     * @return string
     */
    public static function buildWebhookUrl(): string
    {
        return self::getAdminBaseURI(self::WEBHOOK_BASE_URI) . self::WEBHOOK_URI;
    }

    /**
     * @return string
     */
    public static function getWebhookUrl(): string
    {
        return Config::get(self::APP_URL_KEY) . self::getUriByRouteName(self::WEBHOOK_HANDLER_ROUTE_NAME);
    }

    /**
     * @param string $id
     *
     * @return string
     */
    public static function buildWebhookDeleteUrl(string $id): string
    {
        return self::buildWebhookUrl() . "/" . $id;
    }

    /**
     * @param string $name
     *
     * @return string
     */
    private static function getUriByRouteName(string $name): string
    {
        $routeUrl = route($name);

        $split = explode("/", $routeUrl);

        unset($split[0]);
        unset($split[1]);
        unset($split[2]);

        return "/" . implode("/", $split);
    }
}
