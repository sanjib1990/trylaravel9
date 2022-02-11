<?php
return [
    "client_id" => env("THINKIFIC_CLIENT_ID"),
    "client_secret" => env("THINKIFIC_CLIENT_SECRET"),
    "redirect_uri" => env("THINKIFIC_REDIRECT_URI"),
    "base_url" => env("THINKIFIC_BASE_URL"),
    "webhook_uri" => env("THINKIFIC_WEBHOOK_URI", "/v2"),
    "admin_uri" => env("THINKIFIC_WEBHOOK_ADMIN_URI", "/public/v1"),
];
