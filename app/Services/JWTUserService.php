<?php

namespace App\Services;

use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class JWTUserService
{

    static public function getUserId($bearer_token)
    {

        $token = str_replace('Bearer ', '', $bearer_token);
        try {
            JWTAuth::setToken($token);
            $payload = JWTAuth::getPayload();
            return $payload['user']['id'];
        } catch (\Exception $e) {
            return false;
        }
    }
}
