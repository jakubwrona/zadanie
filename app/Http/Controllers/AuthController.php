<?php

namespace App\Http\Controllers;

use App\Services\ExternalAuthFacade;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use ReallySimpleJWT\Token;
use Symfony\Component\HttpFoundation\JsonResponse;

class AuthController extends Controller
{
    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        /*Validator::make($request->only(['login', 'password']),
            [
                'login' => ['required', 'string', 'regex:/^(FOO|BAR|BAZ)_\w{5,25}$/'],
                'password' => 'required|string|min:8'
            ]
        )->validate();*/

        $externalAuthFacade = app()->make(ExternalAuthFacade::class);

        if ($externalAuthFacade->auth($request->get('login'), $request->get('password'))) {
            return response()->json([
                'status' => 'success',
                'token' => JWT::encode([
                    'login' => $request->get('login'),
                    'system' => $externalAuthFacade->getSystemFromLogin($request->get('login'))
                ], 'JWTSECRET123!_',
                    'HS256'
                )
            ]);
        }

        return response()->json([
            'status' => 'failure',
        ]);
    }
}
