<?php
namespace App\Services;

use App\Services\Exceptions\SystemNotInitializedException;
use App\Services\Exceptions\UnsupportedSystemException;
use External\Baz\Auth\Authenticator;
use External\Bar\Auth\LoginService;
use External\Baz\Auth\Responses\Success;
use External\Foo\Auth\AuthWS;
use External\Foo\Exceptions\AuthenticationFailedException;
use Firebase\JWT\Key;

class ExternalAuthFacade
{
    public const SERVICE_FOO = 'FOO';
    public const SERVICE_BAR = 'BAR';
    public const SERVICE_BAZ = 'BAZ';

    private const PREFIX_LENGTH = 3;

    public $systems = [self::SERVICE_BAR, self::SERVICE_FOO, self::SERVICE_BAZ];

    private $authenticator;
    private $loginService;
    private $authWS;

    public function __construct(Authenticator $authenticator, AuthWS $authWS, LoginService $loginService)
    {
        $this->authenticator = $authenticator;
        $this->loginService = $loginService;
        $this->authWS = $authWS;
    }

    /**
     * @param $login
     * @param $password
     * @return bool
     * @throws UnsupportedSystemException
     */
    public function auth($login, $password): bool
    {
        $system = $this->getSystemFromLogin($login);

        switch ($system) {
            case static::SERVICE_BAR: {
                return $this->loginService->login($login, $password);
            }
            case static::SERVICE_BAZ: {
               return $this->authenticator->auth($login, $password) instanceof Success;
            }
            case static::SERVICE_FOO: {
                try {
                    $this->authWS->authenticate($login, $password);
                    return true;
                } catch (AuthenticationFailedException $e) {
                    return false;
                }
            }
            default: {
                throw new UnsupportedSystemException($system);
            }
        }
    }

    public function getSystemFromLogin(string $login): string
    {
        return mb_substr($login, 0, self::PREFIX_LENGTH);
    }

    public function getSystemFromToken(string $token)
    {
        $decoded = JWT::decode($token, new Key('JWTSECRET123!_', 'HS256'));
        dump($decoded);
    }
}
