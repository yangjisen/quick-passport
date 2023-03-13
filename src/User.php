<?php

namespace YangJiSen\QuickPassport;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\HasApiTokens;

/**
 * @mixin  \Illuminate\Database\Eloquent\Builder
 *
 * @property string username 用户名
 * @property string password 密码
 * @property string program_id 小程序的OPENID
 */
abstract class User extends Authenticatable
{
    use HasApiTokens;

    protected function forgetPassportVerify($prefix)
    {
        Cache::forget("$prefix:code");
        Cache::forget("$prefix:retry");
    }

    /**
     * @param $username
     *
     * @return mixed|null
     * @see \Laravel\Passport\Bridge\UserRepository::getUserEntityByUserCredentials
     */
    public function findForPassport($username): mixed
    {
        return $this->where('username', $username)->firstOrFail();
    }

    /**
     * @param $password
     * @return bool
     */
    public function validateForPassportPasswordGrant($password): bool
    {
        /* 使用验证码登录 */
        $cachePrefix = "passport:verification:{$this->username}";
        if($verification = Cache::get("{$cachePrefix}:code")) {
            if(($very = $verification === $password) || Cache::increment("{$cachePrefix}:retry") > 3)
                $this->forgetPassportVerify($cachePrefix);

            return $very;
        }

        /* 注意programId泄漏的情况 */
        return $this->program_id === $password || Hash::check($password, $this->password);
    }

    /**
     * 微信自动注册用户
     * @param string $phone
     * @param string $openid
     * @return static
     */
    public static function autoRegister(string $phone, string $openid): static
    {
        return static::firstOrCreate([
            'username' => $phone
        ], [
            'phone' => $phone,
            'nickname' => $phone,
            'program_id' => $phone,
            'password' => bcrypt($openid)
        ]);
    }

}
