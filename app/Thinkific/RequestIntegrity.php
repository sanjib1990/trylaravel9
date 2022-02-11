<?php

namespace App\Thinkific;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class RequestIntegrity
{
    const ALGO = "sha256";
    const THINKIFIC_HMAC_HEADER = "X-Thinkific-Hmac-Sha256";

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    public static function validate(Request $request): void
    {
        $secret = TokenManager::getClientSecret();
        $jsonData = $request->getContent();
        $hmacKey = $request->headers->get(self::THINKIFIC_HMAC_HEADER);
        $currentHmac = hash_hmac(self::ALGO, $jsonData, $secret);

        if ($currentHmac !== $hmacKey) {
            throw new BadRequestException("Invalid data or corrupt data");
        }
    }
}
