<?php

namespace YangJiSen\QuickPassport\Http\Controllers;

use Illuminate\Http\Request;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use Nyholm\Psr7\Response as Psr7Response;
use Nyholm\Psr7\ServerRequest as Psr7ServerRequest;

class PassportController
{
    /**
     * @param AuthorizationServer $server
     */
    public function __construct(protected AuthorizationServer $server){}

    /**
     * @param $username
     * @param $password
     * @param array $scopes
     * @return Psr7ServerRequest
     */
    protected function createPasswordRequest($username, $password, array $scopes = [])
    {
        return (new Psr7ServerRequest('POST', 'not-important'))->withParsedBody([
            'grant_type' => 'password',
            'client_id' => config('passport.password_grant_client.id', 2),
            'client_secret' => config('passport.password_grant_client.secret', 'secret'),
            'scope' => implode(' ', $scopes),
            'username' => "{$username}",
            'password' => "{$password}",
        ]);
    }

    /**
     * @param Request $request
     *
     * @return array
     * @see https://github.com/laravel/passport/issues/1163
     */
    public function issueToken(Request $request)
    {
        $username = $request->input('username');
        $password = $request->input('password');
        $psr7ServerRequest = $this->createPasswordRequest($username, $password);

        try {
            $response = $this->server->respondToAccessTokenRequest($psr7ServerRequest, new Psr7Response);
            return [
                'status' => $response->getStatusCode(),
                'message' => $response->getReasonPhrase(),
                'data' => json_decode($response->getBody(), true),
            ];
        } catch (OAuthServerException $e) {
            return [
                'status' => $e->getHttpStatusCode(),
                'message' => $e->getMessage(),
                'data' => $e->getPayload(),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 500,
                'message' => $e->getMessage(),
                'data' => [],
            ];
        }
    }

}
