<?php

namespace App\Helpers;

use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\Guard;

class JWT extends Guard
{
    public static function decode($token)
    {
        // split the token
        $tokenParts = explode('.', $token);
        $payload = base64_decode($tokenParts[1]);

        return json_decode($payload, true);
    }

    public static function expiry($token): Carbon
    {
        $payload = self::decode($token);

        return Carbon::createFromTimestamp($payload['exp']);
    }

    public static function verify($token)
    {
        try {
            $secret = config('services.sidooh.jwt_key');

            if (! isset($secret)) {
                exit('Invalid JWT key!');
            }

            // split the token
            $tokenParts = explode('.', $token);
            $header = base64_decode($tokenParts[0]);
            $payload = base64_decode($tokenParts[1]);
            $signatureProvided = $tokenParts[2];

            // check the expiration time - note this will cause an error if there is no 'exp' claim in the token
            $expiration = Carbon::createFromTimestamp(json_decode($payload)->exp);
            $tokenExpired = (Carbon::now()->diffInSeconds($expiration, false) < 0);

            // build a signature based on the header and payload using the secret
            $base64UrlHeader = base_64_url_encode($header);
            $base64UrlPayload = base_64_url_encode($payload);
            $signature = hash_hmac('sha256', $base64UrlHeader.'.'.$base64UrlPayload, $secret, true);
            $base64UrlSignature = base_64_url_encode($signature);

            // verify it matches the signature provided in the token

            if ($tokenExpired) {
                Log::debug('Token has expired.');
            }
            if ($base64UrlSignature !== $signatureProvided) {
                Log::debug('Token is invalid.');
            }

            return ! $tokenExpired && $base64UrlSignature === $signatureProvided;
        } catch (Exception $err) {
            Log::error('--- --- --- --- ---   ...[JWT]: Unable to verify auth token...   --- --- --- --- ---', [$err]);

            return false;
        }
    }
}
