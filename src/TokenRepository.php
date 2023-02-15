<?php

namespace YangJiSen\QuickPassport;

use Illuminate\Support\Facades\Cache;
use Laravel\Passport\TokenRepository as PassportTokenRepository;

class TokenRepository extends PassportTokenRepository
{
    protected string $cacheKey = 'Passport:TokenRepository';

    /**
     * Get a token by the given ID.
     *
     * @param  string  $id
     * @return \Laravel\Passport\Token
     */
    public function find($id): \Laravel\Passport\Token
    {
        return Cache::remember(
            "{$this->cacheKey}:{$id}",
            config('passport.cache_time', 86400),
            function () use ($id) {
                return parent::find($id);
            }
        );
    }

    /**
     * Revoke an access token.
     *
     * @param  string  $id
     * @return mixed
     */
    public function revokeAccessToken($id): mixed
    {
        Cache::forget("{$this->cacheKey}:{$id}");
        return parent::revokeAccessToken($id);
    }
}
