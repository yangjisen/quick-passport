<?php

namespace YangJiSen\QuickPassport\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use Nyholm\Psr7\Response as Psr7Response;
use Nyholm\Psr7\ServerRequest as Psr7ServerRequest;
use Overtrue\LaravelWeChat\EasyWeChat;

class PassportController
{
    /**
     * @param AuthorizationServer $server
     */
    public function __construct(protected AuthorizationServer $server){}

    /**
     * 设置配置文件的名称
     * @return string
     */
    public function clientName()
    {
        return 'password_grant_client';
    }

    /**
     * 表单验证失败返回
     * @param $validator
     * @return array
     */
    protected function requestValidatorFail($validator)
    {
        return [
            'status' => 422,
            'data' => $message = $validator->messages(),
            'message' => $message->first()
        ];
    }

    /**
     * 密码令牌创建请求登录
     * @param $username
     * @param $password
     * @param array $scopes
     * @return array
     */
    protected function createPasswordRequest($username, $password, array $scopes = [])
    {
        $psr7ServerRequest = (new Psr7ServerRequest('POST', 'not-important'))->withParsedBody([
            'grant_type' => 'password',
            'client_id' => config("passport.{$this->clientName()}.id", 2),
            'client_secret' => config("passport.{$this->clientName()}.secret", 'secret'),
            'scope' => implode(' ', $scopes),
            'username' => "{$username}",
            'password' => "{$password}",
        ]);

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

    /**
     * 账号密码或手机验证码登录
     * @param Request $request
     *
     * @return array
     * @see https://github.com/laravel/passport/issues/1163
     */
    public function issueToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'bail|required',
            'password' => 'bail|required',
        ], [
            'username.required' => '必填项不能为空',
            'password.required' => '必填项不能为空',
        ]);

        if($validator->fails())
            return $this->requestValidatorFail($validator);

        $username = $request->input('username');
        $password = $request->input('password');
        return $this->createPasswordRequest($username, $password);
    }

    /**
     * 微信小程序一键登录
     * @param Request $request
     * @return array
     */
    public function programToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'js_code' => 'bail|required',
            'code' => 'bail|required',
            'encryptedData' => 'bail|required',
            'iv' => 'bail|required',
        ], [
            'js_code.required' => '用户登录凭证不正确',
            'code.required' => '用户登录凭证不正确',
            'encryptedData.required' => '用户数据不正确',
            'iv.required' => '加密算法不正确',
        ]);

        if($validator->fails())
            return $this->requestValidatorFail($validator);

        try {
            $program = EasyWeChat::miniApp();
            $utils = $program->getUtils();

            /* 通过Code获取OpenId */
            $session = $utils->codeToSession($request->input('js_code'));

            /* 解密手机号码 */
            $decrypt = $utils->decryptSession(
                Arr::get($session, 'session_key'),
                $request->input('iv'),
                $request->input('encryptedData')
            );

            $phone = Arr::get($decrypt, 'purePhoneNumber');
            $openid = Arr::get($session, 'openid');

            if(!$phone || !$openid)
                return ['status' => 422, 'message' => '一键登录失败'];

            if(config('passport.program_auto_register')) {
                /** @uses \YangJiSen\QuickPassport\User::autoRegister 自动注册用户 */
                (config('passport.auto_register_model'))::autoRegister($phone, $openid);
            }

            /* 通过手机号码与openId进行登录 */
            return $this->createPasswordRequest($phone, $openid);
        } catch (\Throwable $e) {
            return [
                'status' => 500,
                'message' => $e->getMessage(),
                'data' => [],
            ];
        }
    }

}
